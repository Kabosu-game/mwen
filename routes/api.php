<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectionRequestController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MWEN PWÒP — API Routes
|--------------------------------------------------------------------------
| v1 — Mobile application endpoints
*/

Route::prefix('v1')->group(function () {

    // ── Public routes ────────────────────────────────────────────────────
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Zones (public list for registration/form)
    Route::get('zones', [ZoneController::class, 'index']);

    // Map data (public environmental reports)
    Route::get('reports/map', [ReportController::class, 'mapData']);

    // ── Authenticated routes ─────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Profile
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'updatePassword']);
        Route::post('auth/avatar', [AuthController::class, 'uploadAvatar']);

        // ── Collection Requests ──────────────────────────────────────────
        Route::prefix('collections')->group(function () {
            Route::get('/', [CollectionRequestController::class, 'index']);
            Route::post('/', [CollectionRequestController::class, 'store']);
            Route::get('{collectionRequest}', [CollectionRequestController::class, 'show']);
            Route::post('{collectionRequest}/cancel', [CollectionRequestController::class, 'cancel']);
            Route::post('{collectionRequest}/rate', [CollectionRequestController::class, 'rate']);

            // Collector actions
            Route::get('available/near-me', [CollectionRequestController::class, 'availableForCollector']);
            Route::post('{collectionRequest}/accept', [CollectionRequestController::class, 'accept']);
            Route::put('{collectionRequest}/status', [CollectionRequestController::class, 'updateStatus']);
        });

        // ── Environmental Reports ────────────────────────────────────────
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::post('/', [ReportController::class, 'store']);
            Route::get('{report}', [ReportController::class, 'show']);
        });

        // ── Travay Vèt (Eco-jobs / Missions) ────────────────────────────
        Route::prefix('missions')->group(function () {
            Route::get('/', [MissionController::class, 'index']);
            Route::get('{mission}', [MissionController::class, 'show']);
            Route::post('{mission}/apply', [MissionController::class, 'apply']);
            Route::get('my/applications', [MissionController::class, 'myApplications']);
            Route::delete('applications/{application}', [MissionController::class, 'cancelApplication']);
        });

        // ── Notifications ────────────────────────────────────────────────
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::put('mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{notification}', [NotificationController::class, 'destroy']);
        });
    });
});
