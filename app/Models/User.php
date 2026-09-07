<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'phone',
        'password',
        'status',
        'access_starts_at',
        'access_expires_at',
        'first_login_at',
        'last_login_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role'               => 'string',
            'status'             => 'string',
            'password'           => 'hashed',
            'access_starts_at'   => 'datetime',
            'access_expires_at'  => 'datetime',
            'first_login_at'     => 'datetime',
            'last_login_at'      => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    // -----------------------------------------------------------------------
    // Helper methods
    // -----------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->hasAccessExpired();
    }

    public function hasAccessExpired(): bool
    {
        return $this->access_expires_at && $this->access_expires_at->isPast();
    }
}
