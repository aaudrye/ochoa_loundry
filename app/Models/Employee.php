<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    public function getTotalAttribute(): int
    {
        return $this->salary + $this->overtime;
    }
}