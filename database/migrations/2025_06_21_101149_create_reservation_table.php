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
        Schema::create('reservation', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->dateTime('date_in')->nullable();
            $table->dateTime('date_out')->nullable();
            $table->decimal('total_fee', 12, 2)->nullable()->default(0.00);
            $table->uuid('patient_id');
            $table->uuid('nurse_id');
            $table->string('room_id', 36);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('patient_id')->references('user_id')->on('patient')->onDelete('cascade');
            $table->foreign('nurse_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('room')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
