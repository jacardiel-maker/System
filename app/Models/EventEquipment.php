<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventEquipment extends Model
{
    use HasFactory;

    protected $table = 'event_equipment';

    protected $fillable = [
        'event_id', 'equipment_item_id', 'assigned_waiter_id',
        'quantity_allocated', 'quantity_returned', 'quantity_damaged',
        'quantity_missing', 'status', 'notes', 'returned_at'
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function equipmentItem()
    {
        return $this->belongsTo(EquipmentItem::class);
    }

    public function assignedWaiter()
    {
        return $this->belongsTo(Waiter::class, 'assigned_waiter_id');
    }

    public function logs()
    {
        return $this->hasMany(EquipmentLog::class);
    }
}