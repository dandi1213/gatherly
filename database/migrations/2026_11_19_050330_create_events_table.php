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
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('poster_path')->nullable();
            $table->string('format')->default('offline'); // online, offline, hybrid
            $table->string('location')->nullable();
            $table->string('online_url')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('registration_start_at');
            $table->dateTime('registration_end_at');
            $table->string('status')->default('draft'); // draft, published, cancelled
            $table->boolean('is_disabled')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_at']);
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
