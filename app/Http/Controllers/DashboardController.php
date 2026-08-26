<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EquipmentItem;
use App\Models\Waiter;

class DashboardController extends Controller
{
    public function index()
    {
        // Admin stats
        $stats = [
            'total_events' => Event::count(),
            'upcoming_events' => Event::where('event_date', '>=', now())->where('status', 'scheduled')->count(),
            'total_waiters' => Waiter::where('status', 'active')->count(),
            'low_stock_items' => EquipmentItem::whereColumn('available_quantity', '<', \DB::raw('total_quantity * 0.2'))->count(),
        ];

        $upcomingEvents = Event::where('event_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('event_date')
            ->take(5)
            ->get();

        return view('dashboard', compact('upcomingEvents', 'stats'));
    }
}