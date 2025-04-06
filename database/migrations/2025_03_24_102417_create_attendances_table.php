<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id'); // assuming users/students
        $table->date('date'); // the date of attendance
        $table->time('time_in')->nullable(); // check-in time
        $table->time('time_out')->nullable(); // check-out time
        $table->timestamps();

        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        $table->unique(['student_id', 'date']); // prevent multiple attendance records for the same day
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
