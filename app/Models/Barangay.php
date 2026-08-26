<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barangay extends Model
{
    protected $fillable = ['code', 'correspondence_code', 'name', 'region_id', 'province_id', 'city_id'];
    public $timestamps = false;

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}