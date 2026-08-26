<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EquipmentItem;
use App\Models\EventEquipment;
use App\Models\Waiter;
use App\Models\EquipmentLog;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function events(Request $request)
    {
        $query = Event::withCount('waiters', 'equipment');

        if ($request->filled('from')) {
            $query->whereDate('event_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('event_date', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->latest()->get();

        return view('reports.events', compact('events'));
    }

    public function equipmentUsage(Request $request)
    {
        $query = EventEquipment::with(['event', 'equipmentItem', 'assignedWaiter.user']);

        if ($request->filled('from')) {
            $query->whereHas('event', fn($q) => $q->whereDate('event_date', '>=', $request->from));
        }
        if ($request->filled('to')) {
            $query->whereHas('event', fn($q) => $q->whereDate('event_date', '<=', $request->to));
        }

        $records = $query->latest()->get();

        return view('reports.equipment', compact('records'));
    }

    public function accountability(Request $request)
    {
        $waiters = Waiter::with(['user', 'assignedEquipment' => function($q) {
            $q->whereIn('status', ['partially_returned', 'lost']);
        }])->get();

        return view('reports.accountability', compact('waiters'));
    }

    public function equipmentLogs(Request $request)
    {
        $query = EquipmentLog::with(['eventEquipment.event', 'eventEquipment.equipmentItem', 'logger']);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->get();

        return view('reports.equipment-logs', compact('logs'));
    }
}