<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('type', 40);
            $table->string('visibility', 20);

            $table->jsonb('metadata')
                ->default(DB::raw("'{}'::jsonb"));

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->index(
                ['ticket_id', 'created_at'],
                'ticket_activities_ticket_created_index'
            );
        });

        DB::statement("
            ALTER TABLE ticket_activities
            ADD CONSTRAINT ticket_activities_type_check
            CHECK (
                type IN (
                    'CREATED',
                    'STATUS_CHANGED',
                    'ASSIGNED',
                    'UNASSIGNED',
                    'DEPARTMENT_CHANGED',
                    'CATEGORY_CHANGED',
                    'PRIORITY_CHANGED',
                    'CLOSED',
                    'REOPENED'
                )
            )
        ");

        DB::statement("
            ALTER TABLE ticket_activities
            ADD CONSTRAINT ticket_activities_visibility_check
            CHECK (
                visibility IN (
                    'PUBLIC',
                    'SUPPORT'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
