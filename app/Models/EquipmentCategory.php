<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'image'];

    public function items()
    {
        return $this->hasMany(EquipmentItem::class, 'category_id');
    }

    /* ─── Helper: Image URL (null if no image) ─── */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}