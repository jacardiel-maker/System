<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'serial_code', 'total_quantity',
        'available_quantity', 'condition'
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    public function eventAllocations()
    {
        return $this->hasMany(EventEquipment::class);
    }
}