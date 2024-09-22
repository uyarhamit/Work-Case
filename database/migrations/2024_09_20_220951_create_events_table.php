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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->string('title');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->integer('duration');
            $table->integer('attendance_limit');
            $table->enum('is_expired', [0, 1])->default(1);
            $table->timestamps();

            $table->foreign('created_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
