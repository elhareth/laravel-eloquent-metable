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
        Schema::create('metables', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->uuidMorphs('metable');
            $table->string('name', 50);
            $table->longText('value')->nullable();
            $table->string('group', 20)->nullable();

            $table->unique([
                'name',
                'metable_id',
                'metable_type',
            ], 'metable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metables');
    }
};
