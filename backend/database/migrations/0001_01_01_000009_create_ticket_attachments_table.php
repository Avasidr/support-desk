<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('message_id')
                ->nullable();

            $table->foreignId('uploaded_by_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('visibility', 20);

            $table->string('original_name', 255);
            $table->string('storage_path', 500)->unique();
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->index(
                'ticket_id',
                'ticket_attachments_ticket_index'
            );
        });

        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->foreign(
                ['message_id', 'ticket_id'],
                'ticket_attachments_message_ticket_foreign'
            )
                ->references(['id', 'ticket_id'])
                ->on('ticket_messages')
                ->cascadeOnDelete();
        });

        DB::statement("
            ALTER TABLE ticket_attachments
            ADD CONSTRAINT ticket_attachments_visibility_check
            CHECK (
                visibility IN (
                    'PUBLIC',
                    'SUPPORT'
                )
            )
        ");

        DB::statement("
            ALTER TABLE ticket_attachments
            ADD CONSTRAINT ticket_attachments_size_check
            CHECK (
                size_bytes > 0
                AND size_bytes <= 10485760
            )
        ");

        DB::statement("
            ALTER TABLE ticket_attachments
            ADD CONSTRAINT ticket_attachments_name_check
            CHECK (
                CHAR_LENGTH(TRIM(original_name)) > 0
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
