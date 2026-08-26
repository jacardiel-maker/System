<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['code', 'correspondence_code', 'name', 'region_id'];
    public $timestamps = false;

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'region_id', 'region_id');
    }
}