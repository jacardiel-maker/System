<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'customer_name',
        'venue_address',
        'event_date',
        'setup_time',
        'guest_count',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'setup_time' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function waiters()
    {
        return $this->belongsToMany(Waiter::class, 'event_waiter')
            ->withPivot('confirmation_status', 'role', 'confirmed_at', 'time_in', 'time_out', 'decline_reason')
            ->withTimestamps();
    }

    public function equipment()
    {
        return $this->hasMany(EventEquipment::class);
    }

    // Uses waiters() above, so it already includes decline_reason
    public function confirmedWaiters()
    {
        return $this->waiters()->wherePivot('confirmation_status', 'confirmed');
    }
}