<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format: Y-m
            $table->integer('total_days');
            $table->integer('present_days');
            $table->integer('absent_days');
            $table->integer('late_days');
            $table->integer('half_days');
            $table->decimal('attendance_percentage', 5, 2);
            $table->timestamps();

            $table->unique(['student_id', 'month']);
            $table->index(['month', 'attendance_percentage']);
            $table->index(['student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
