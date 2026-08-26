<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['code', 'correspondence_code', 'name', 'region_id', 'province_id', 'city_id'];
    public $timestamps = false;

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'city_id', 'city_id');
    }
}