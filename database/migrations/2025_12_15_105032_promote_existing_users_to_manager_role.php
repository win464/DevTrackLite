<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Promote all non-admin users to manager role for backward compatibility
        DB::table('users')
            ->where('role', '!=', 'admin')
            ->update(['role' => 'manager']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert manager users back to viewer (cannot fully restore original state)
        DB::table('users')
            ->where('role', 'manager')
            ->update(['role' => 'viewer']);
    }
};
