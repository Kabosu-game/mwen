<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::active()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }
}
