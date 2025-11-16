<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day']);
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['student_id', 'date']);
            $table->index(['recorded_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
