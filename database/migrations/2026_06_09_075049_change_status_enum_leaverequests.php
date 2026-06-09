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
        DB::table('leave_requests')
        ->whereNotIn('status', ['pending', 'approved', 'denied'])
        ->update(['status' => 'pending']);
        
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'denied',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
