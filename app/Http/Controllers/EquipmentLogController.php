<?php

namespace App\Http\Controllers;

use App\Models\EquipmentLog;
use App\Models\EventEquipment;
use Illuminate\Http\Request;

class EquipmentLogController extends Controller
{
    public function index()
    {
        $logs = EquipmentLog::with(['eventEquipment.event', 'eventEquipment.equipmentItem', 'logger'])->latest()->paginate(20);
        return view('equipment-logs.index', compact('logs'));
    }

    public function show(EquipmentLog $equipmentLog)
    {
        $equipmentLog->load(['eventEquipment.event', 'eventEquipment.equipmentItem', 'eventEquipment.assignedWaiter.user', 'logger']);
        return view('equipment-logs.show', compact('equipmentLog'));
    }
}