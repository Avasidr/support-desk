<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name', 120);
            $table->char('color', 7)->nullable();

            $table->boolean('accepts_tickets')->default(true);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);

            $table->timestampsTz();
        });

        DB::statement("
            ALTER TABLE departments
            ADD CONSTRAINT departments_sort_order_check
            CHECK (sort_order >= 0)
        ");

        DB::statement("
            ALTER TABLE departments
            ADD CONSTRAINT departments_color_check
            CHECK (
                color IS NULL
                OR color ~ '^#[0-9A-Fa-f]{6}$'
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
