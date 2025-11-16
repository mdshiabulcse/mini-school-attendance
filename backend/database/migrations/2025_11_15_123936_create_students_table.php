<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('class');
            $table->string('section');
            $table->string('roll_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('parent_name');
            $table->string('parent_email')->nullable();
            $table->string('parent_phone');
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class', 'section']);
            $table->index(['is_active']);
            $table->index(['created_at']);
            $table->index(['student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
