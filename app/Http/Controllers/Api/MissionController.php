<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionApplication;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Mission::with(['zone:id,name,commune', 'creator:id,name']);

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'open');
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $missions = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $missions,
        ]);
    }

    public function show(Mission $mission)
    {
        return response()->json([
            'success' => true,
            'data' => $mission->load(['zone', 'creator:id,name', 'applications.user:id,name,avatar']),
        ]);
    }

    public function apply(Request $request, Mission $mission)
    {
        $request->validate([
            'motivation' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        if (!$mission->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette mission n\'est plus disponible pour candidature.',
            ], 422);
        }

        $existing = MissionApplication::where('mission_id', $mission->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà postulé à cette mission.',
            ], 422);
        }

        $application = MissionApplication::create([
            'mission_id' => $mission->id,
            'user_id' => $user->id,
            'motivation' => $request->motivation,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidature envoyée avec succès !',
            'data' => $application,
        ], 201);
    }

    public function myApplications(Request $request)
    {
        $applications = MissionApplication::with(['mission.zone'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    public function cancelApplication(Request $request, MissionApplication $application)
    {
        if ($application->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if (!in_array($application->status, ['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature ne peut plus être annulée.',
            ], 422);
        }

        $application->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Candidature annulée',
        ]);
    }
}
