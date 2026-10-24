<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function waiter()
    {
        return $this->hasOne(Waiter::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isWaiter(): bool
    {
        return $this->role === 'waiter';
    }

    /** Business owner: can only monitor (Dashboard + Reports), cannot change anything. */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /** Can open the Dashboard overview and the Reports (admin and owner). */
    public function canMonitor(): bool
    {
        return $this->isAdmin() || $this->isOwner();
    }
}