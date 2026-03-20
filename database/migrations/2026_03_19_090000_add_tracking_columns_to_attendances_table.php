<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('latitude_in', 10, 7)->nullable()->after('check_in');
            $table->decimal('longitude_in', 10, 7)->nullable()->after('latitude_in');
            $table->decimal('latitude_out', 10, 7)->nullable()->after('check_out');
            $table->decimal('longitude_out', 10, 7)->nullable()->after('latitude_out');
            $table->string('ip_address', 45)->nullable()->after('status');
            $table->string('device_id')->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'latitude_in',
                'longitude_in',
                'latitude_out',
                'longitude_out',
                'ip_address',
                'device_id',
            ]);
        });
    }
};
