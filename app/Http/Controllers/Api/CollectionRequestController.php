<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class CollectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CollectionRequest::with(['collector:id,name,phone,avatar,is_available', 'zone:id,name,commune']);

        if ($user->isCitizen()) {
            $query->where('citizen_id', $user->id);
        } elseif ($user->isCollector()) {
            $query->where('collector_id', $user->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'waste_type' => 'required|in:household,organic,recyclable,hazardous,construction,other',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'scheduled_at' => 'nullable|date|after:now',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('collections', 'public');
            }
        }

        $collectionRequest = CollectionRequest::create([
            'citizen_id' => $request->user()->id,
            'waste_type' => $data['waste_type'],
            'priority' => $data['priority'] ?? 'normal',
            'address' => $data['address'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'notes' => $data['notes'] ?? null,
            'photos' => $photos ?: null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'zone_id' => $data['zone_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de ramassage créée avec succès',
            'data' => $collectionRequest->load(['citizen', 'zone']),
        ], 201);
    }

    public function show(Request $request, CollectionRequest $collectionRequest)
    {
        $user = $request->user();

        if ($user->isCitizen() && $collectionRequest->citizen_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if ($user->isCollector() && $collectionRequest->collector_id !== $user->id
            && $collectionRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $collectionRequest->load(['citizen:id,name,phone,avatar', 'collector:id,name,phone,avatar', 'zone']),
        ]);
    }

    public function cancel(Request $request, CollectionRequest $collectionRequest)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        if ($collectionRequest->citizen_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if (!in_array($collectionRequest->status, ['pending', 'assigned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne peut plus être annulée.',
            ], 422);
        }

        $collectionRequest->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande annulée',
            'data' => $collectionRequest,
        ]);
    }

    public function rate(Request $request, CollectionRequest $collectionRequest)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        if ($collectionRequest->citizen_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if ($collectionRequest->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez noter qu\'une demande terminée.'], 422);
        }

        $collectionRequest->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note envoyée. Merci !',
        ]);
    }

    // Collector actions
    public function availableForCollector(Request $request)
    {
        $user = $request->user();

        if (!$user->isCollector()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $requests = CollectionRequest::with(['citizen:id,name,phone', 'zone:id,name,commune'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function accept(Request $request, CollectionRequest $collectionRequest)
    {
        $user = $request->user();

        if (!$user->isCollector()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if ($collectionRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cette demande n\'est plus disponible.'], 422);
        }

        $collectionRequest->update([
            'collector_id' => $user->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande acceptée',
            'data' => $collectionRequest->load(['citizen', 'zone']),
        ]);
    }

    public function updateStatus(Request $request, CollectionRequest $collectionRequest)
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed',
        ]);

        $user = $request->user();

        if ($collectionRequest->collector_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $updateData = ['status' => $request->status];
        if ($request->status === 'in_progress') {
            $updateData['started_at'] = now();
        } elseif ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }

        $collectionRequest->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'data' => $collectionRequest,
        ]);
    }
}
