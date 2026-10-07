<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer')->index(); // customer | admin | kurir | owner
            $t->string('phone')->nullable();
            $t->string('address')->nullable();
            $t->string('vehicle')->nullable(); // khusus kurir
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable()->unique();
            $t->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $t->string('service');                       // reguler | express
            $t->unsignedInteger('price_per_kg');          // harga dikunci saat order dibuat
            $t->decimal('weight_kg', 5, 1)->default(0);   // diisi kurir saat timbang
            $t->unsignedTinyInteger('status')->default(0)->index();
            $t->string('color');
            $t->string('perfume');
            $t->string('category');
            $t->text('note')->nullable();
            $t->string('pickup_address');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->date('pickup_date');
            $t->string('pickup_time');
            $t->string('delivery_slot')->nullable();
            $t->foreignId('pickup_courier_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('delivery_courier_id')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('arrived')->default(false);       // kurir sudah sampai di lokasi
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::create('order_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->timestamps();
        });

        Schema::create('order_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedTinyInteger('status');
            $t->string('message');
            $t->timestamps();
        });

        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->date('date')->index();
            $t->string('type');                           // in | out
            $t->string('category');
            $t->string('description');
            $t->unsignedBigInteger('amount');
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('position');
            $t->unsignedBigInteger('salary');
            $t->unsignedBigInteger('overtime')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['employees', 'transactions', 'order_logs', 'order_photos', 'orders'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone', 'address', 'vehicle']));
    }
};