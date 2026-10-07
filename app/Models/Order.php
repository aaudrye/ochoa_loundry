<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'arrived' => 'boolean',
            'pickup_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function pickupCourier() { return $this->belongsTo(User::class, 'pickup_courier_id'); }
    public function deliveryCourier() { return $this->belongsTo(User::class, 'delivery_courier_id'); }
    public function photos() { return $this->hasMany(OrderPhoto::class); }
    public function logs() { return $this->hasMany(OrderLog::class)->orderBy('id'); }

    public function getTotalAttribute(): int
    {
        return (int) round($this->weight_kg * $this->price_per_kg);
    }

    public function getServiceLabelAttribute(): string
    {
        return config("ochoa.services.{$this->service}.label", $this->service);
    }

    /** Kurir yang sedang bertugas pada status saat ini. */
    public function getCourierAttribute(): ?User
    {
        return match (true) {
            in_array($this->status->value, [1, 2]) => $this->pickupCourier,
            in_array($this->status->value, [8, 9]) => $this->deliveryCourier,
            default => null,
        };
    }

    public function scopeActiveFor(Builder $q, int $userId): Builder
    {
        return $q->where(fn ($w) => $w->where('pickup_courier_id', $userId)->whereIn('status', [1, 2]))
                 ->orWhere(fn ($w) => $w->where('delivery_courier_id', $userId)->whereIn('status', [8, 9]));
    }
}