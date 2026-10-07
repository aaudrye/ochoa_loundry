<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'address', 'vehicle'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.incoming',
            'kurir' => 'courier.home',
            'owner' => 'owner.dashboard',
            default => 'customer.home',
        };
    }

    public function getTelAttribute(): string
    {
        return preg_replace('/\D/', '', (string) $this->phone);
    }

    public function getWaNumberAttribute(): string
    {
        return '62' . ltrim($this->tel, '0');
    }

    /** Kurir dianggap sibuk jika punya tugas jemput (status 1-2) atau antar (status 8-9). */
    public function isBusy(): bool
    {
        return Order::activeFor($this->id)->exists();
    }
}