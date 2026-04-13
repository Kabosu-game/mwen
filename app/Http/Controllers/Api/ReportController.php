<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Report::with(['reporter:id,name', 'zone:id,name,commune']);

        if ($user->isCitizen()) {
            $query->where('reporter_id', $user->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $reports = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:illegal_dump,blocked_canal,risk_zone,flooding,public_health,other',
            'severity' => 'nullable|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('reports', 'public');
            }
        }

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'type' => $data['type'],
            'severity' => $data['severity'] ?? 'medium',
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'photos' => $photos ?: null,
            'zone_id' => $data['zone_id'] ?? null,
        ]);

        // Add civic points to the reporter
        $request->user()->increment('points', 10);

        return response()->json([
            'success' => true,
            'message' => 'Signalement envoyé. Merci pour votre action citoyenne ! (+10 points)',
            'data' => $report->load(['reporter', 'zone']),
        ], 201);
    }

    public function show(Request $request, Report $report)
    {
        $user = $request->user();

        if ($user->isCitizen() && $report->reporter_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $report->load(['reporter:id,name', 'assignedAgent:id,name', 'zone']),
        ]);
    }

    // Map view - all public reports
    public function mapData(Request $request)
    {
        $reports = Report::select('id', 'type', 'severity', 'status', 'title', 'latitude', 'longitude', 'address')
            ->whereNotIn('status', ['rejected', 'closed'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }
}
