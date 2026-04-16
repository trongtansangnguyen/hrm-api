<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePairs = DB::table('attendances')
            ->select('employee_id', 'date')
            ->groupBy('employee_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicatePairs as $pair) {
            $attendanceIds = DB::table('attendances')
                ->where('employee_id', $pair->employee_id)
                ->where('date', $pair->date)
                ->orderByRaw('check_out IS NOT NULL DESC')
                ->orderByRaw('check_in IS NOT NULL DESC')
                ->orderByDesc('working_hours')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('id');

            $keepId = $attendanceIds->shift();

            if ($keepId === null || $attendanceIds->isEmpty()) {
                continue;
            }

            DB::table('attendances')
                ->whereIn('id', $attendanceIds->all())
                ->delete();
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['employee_id', 'date'], 'attendances_employee_id_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_employee_id_date_unique');
        });
    }
};
