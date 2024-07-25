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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // $table->decimal('price', 10, 2)->nullable();
            $table->integer('price')->nullable();
            $table->integer('points')->nullable();
            // $table->foreignId('specialtyId')->onDelete('cascade');
            $table->foreignId('specialtyId')->default(1) // Foreign key to Specialty
                ->constrained('specialties')
                ->onDelete('cascade');
                $table->integer('experience')->nullable(); // Add experience column
                $table->float('stars', 3, 1)->nullable(); // Add stars column with precision 3 and scale 2
                $table->integer('ratings_count')->default(0);
            $table->rememberToken();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
