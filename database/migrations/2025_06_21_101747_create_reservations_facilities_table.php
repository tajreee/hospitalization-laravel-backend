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
        Schema::create('reservations_facilities', function (Blueprint $table) {
            $table->uuid('reservation_id');
            $table->uuid('facility_id');
            $table->foreign('reservation_id')
                ->references('id')
                ->on('reservation')
                ->onDelete('cascade');
            $table->foreign('facility_id')
                ->references('id')
                ->on('facility')
                ->onDelete('cascade');
            $table->primary(['reservation_id', 'facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations_facilities');
    }
};
