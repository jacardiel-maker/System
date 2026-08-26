<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_equipment_id', 'logged_by', 'action', 'quantity', 'remarks'
    ];

    public function eventEquipment()
    {
        return $this->belongsTo(EventEquipment::class);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}