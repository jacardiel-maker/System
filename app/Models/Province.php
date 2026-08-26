<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = ['code', 'correspondence_code', 'name', 'region_id', 'province_id'];
    public $timestamps = false;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id', 'province_id');
    }
}