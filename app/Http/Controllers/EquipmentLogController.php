<?php

namespace App\Http\Controllers;

use App\Models\EquipmentLog;
use App\Models\EventEquipment;
use Illuminate\Http\Request;

class EquipmentLogController extends Controller
{
   public function index(Request $request)
    {
        $query = EquipmentLog::with(['eventEquipment.event', 'eventEquipment.equipmentItem', 'logger']);

        // Search by event name or equipment name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('eventEquipment', function ($q) use ($search) {
                $q->whereHas('event', function ($eq) use ($search) {
                    $eq->where('event_name', 'like', "%{$search}%");
                })->orWhereHas('equipmentItem', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('equipment-logs.index', compact('logs'));
    }

    public function show(EquipmentLog $equipmentLog)
    {
        $equipmentLog->load(['eventEquipment.event', 'eventEquipment.equipmentItem', 'eventEquipment.assignedWaiter.user', 'logger']);
        return view('equipment-logs.show', compact('equipmentLog'));
    }
}