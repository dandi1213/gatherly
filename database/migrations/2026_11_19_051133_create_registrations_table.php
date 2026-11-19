<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('attendee_phone')->nullable();
            $table->string('status')->default('registered'); // registered, cancelled
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });

        // Partial unique index: satu user maksimal satu registrasi aktif per event (PRD 8.2)
        DB::statement("
            CREATE UNIQUE INDEX registrations_event_user_active_unique
            ON registrations (event_id, user_id)
            WHERE status = 'registered'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
