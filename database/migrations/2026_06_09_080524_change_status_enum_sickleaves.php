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
        DB::table('sick_leaves')
        ->whereNotIn('status', ['reported', 'approved', 'denied', 'incomplete'])
        ->update(['status' => 'reported']);
        
        Schema::table('sick_leaves', function (Blueprint $table) {
            $table->enum('status', [
                'reported',
                'approved',
                'denied',
                'incomplete',
            ])->default('reported')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sick_leaves', function (Blueprint $table) {
            $table->string('status')->default('reported')->change();
        });
    }
};
