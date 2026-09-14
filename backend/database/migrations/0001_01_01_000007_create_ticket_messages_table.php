<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->foreignId('author_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('type', 20);
            $table->text('body');

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->unique(
                ['id', 'ticket_id'],
                'ticket_messages_id_ticket_unique'
            );

            $table->index(
                ['ticket_id', 'created_at'],
                'ticket_messages_ticket_created_index'
            );
        });

        DB::statement("
            ALTER TABLE ticket_messages
            ADD CONSTRAINT ticket_messages_type_check
            CHECK (type IN ('PUBLIC', 'INTERNAL'))
        ");

        DB::statement("
            ALTER TABLE ticket_messages
            ADD CONSTRAINT ticket_messages_body_check
            CHECK (
                CHAR_LENGTH(TRIM(body))
                BETWEEN 1 AND 10000
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
