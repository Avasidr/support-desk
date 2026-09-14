<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->ulid('public_id')->unique();

            $table->foreignId('requester_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('requester_department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->foreignId('destination_department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('subject', 180);
            $table->text('description');

            $table->string('status', 32)->default('OPEN');
            $table->string('priority', 16);
            $table->string('visibility', 20)->default('PRIVATE');

            $table->text('resolution')->nullable();

            $table->timestampTz('first_response_at')->nullable();
            $table->timestampTz('last_activity_at');

            $table->timestampTz('closed_at')->nullable();

            $table->foreignId('closed_by_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestampsTz();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign(
                ['category_id', 'destination_department_id'],
                'tickets_category_department_foreign'
            )
                ->references(['id', 'department_id'])
                ->on('categories')
                ->restrictOnDelete();
        });

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_status_check
            CHECK (
                status IN (
                    'OPEN',
                    'RECEIVED',
                    'IN_PROGRESS',
                    'WAITING_REQUESTER',
                    'WAITING_EXTERNAL',
                    'CLOSED'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_priority_check
            CHECK (
                priority IN (
                    'LOW',
                    'MEDIUM',
                    'HIGH',
                    'URGENT'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_visibility_check
            CHECK (
                visibility IN (
                    'PRIVATE',
                    'DEPARTMENT',
                    'ORGANIZATION'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_subject_check
            CHECK (
                CHAR_LENGTH(TRIM(subject)) BETWEEN 3 AND 180
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_description_check
            CHECK (
                CHAR_LENGTH(TRIM(description)) > 0
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_closed_state_check
            CHECK (
                (
                    status = 'CLOSED'
                    AND resolution IS NOT NULL
                    AND CHAR_LENGTH(TRIM(resolution)) > 0
                    AND closed_at IS NOT NULL
                    AND closed_by_id IS NOT NULL
                )
                OR
                (
                    status <> 'CLOSED'
                    AND resolution IS NULL
                    AND closed_at IS NULL
                    AND closed_by_id IS NULL
                )
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_first_response_check
            CHECK (
                first_response_at IS NULL
                OR created_at IS NULL
                OR first_response_at >= created_at
            )
        ");

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_last_activity_check
            CHECK (
                created_at IS NULL
                OR last_activity_at >= created_at
            )
        ");

        DB::statement("
            CREATE INDEX tickets_department_status_activity_index
            ON tickets (
                destination_department_id,
                status,
                last_activity_at DESC
            )
        ");

        DB::statement("
            CREATE INDEX tickets_assignee_status_activity_index
            ON tickets (
                assigned_to_id,
                status,
                last_activity_at DESC
            )
        ");

        DB::statement("
            CREATE INDEX tickets_requester_created_index
            ON tickets (
                requester_id,
                created_at DESC
            )
        ");

        DB::statement("
            CREATE INDEX tickets_requester_department_visibility_index
            ON tickets (
                requester_department_id,
                visibility,
                created_at DESC
            )
        ");

        DB::statement("
            CREATE INDEX tickets_active_queue_index
            ON tickets (
                destination_department_id,
                last_activity_at DESC
            )
            WHERE status <> 'CLOSED'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
