<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waiter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'emergency_contact',
        'address',
        'date_hired',
        'photo',
        'status',
        'region_id',
        'province_id',
        'city_id',
        'barangay_code',
    ];

    protected $casts = [
        'date_hired' => 'date',
    ];

    /* ─── Existing relationships ─── */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_waiter')
            ->withPivot('confirmation_status', 'role', 'confirmed_at', 'time_in', 'time_out')
            ->withTimestamps();
    }

    public function assignedEquipment(): HasMany
    {
        return $this->hasMany(EventEquipment::class, 'assigned_waiter_id');
    }

    /* ─── Philippine Address relationships ─── */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_code', 'code');
    }

    /* ─── Helper: Photo URL (null if no photo) ─── */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /* ─── Helper: Full formatted address ─── */
    public function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;           // street / building
        }
        if ($this->barangay) {
            $parts[] = $this->barangay->name;
        }
        if ($this->city) {
            $parts[] = $this->city->name;
        }
        if ($this->province) {
            $parts[] = $this->province->name;
        }
        if ($this->region) {
            $parts[] = $this->region->name;
        }

        return count($parts) ? implode(', ', $parts) : 'N/A';
    }
}