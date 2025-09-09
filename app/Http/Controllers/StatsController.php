<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $perStatus = Ticket::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total','status');

        $perCategory = Ticket::select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')->pluck('total','category');

        return response()->json([
            'per_status' => $perStatus,
            'per_category' => $perCategory,
            'total' => Ticket::count(),
        ]);
    }
}