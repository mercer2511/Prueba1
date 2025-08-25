<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('guest_email')->nullable()->after('user_id');
            $table->string('guest_phone')->nullable()->after('guest_email');
            $table->string('guest_name')->nullable()->after('guest_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['guest_email', 'guest_phone', 'guest_name']);
        });
    }
};
