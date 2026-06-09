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
        DB::table('time_entries')
        ->whereNotIn('status', ['open', 'submitted', 'approved', 'corrected'])
        ->update(['status' => 'open']);
        
        Schema::table('time_entries', function (Blueprint $table) {
            $table->enum('status', [
                'open',
                'submitted',
                'approved',
                'corrected',
            ])->default('open')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
