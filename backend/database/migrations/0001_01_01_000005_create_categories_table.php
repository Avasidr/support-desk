<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('code', 60);
            $table->string('name', 120);

            $table->string('default_priority', 16)->nullable();

            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);

            $table->timestampsTz();

            $table->unique(
                ['department_id', 'code'],
                'categories_department_code_unique'
            );

            $table->unique(
                ['id', 'department_id'],
                'categories_id_department_unique'
            );

            $table->index(
                ['department_id', 'parent_id', 'is_active'],
                'categories_department_parent_active_index'
            );
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign(
                ['parent_id', 'department_id'],
                'categories_parent_department_foreign'
            )
                ->references(['id', 'department_id'])
                ->on('categories')
                ->restrictOnDelete();
        });

        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT categories_default_priority_check
            CHECK (
                default_priority IS NULL
                OR default_priority IN ('LOW', 'MEDIUM', 'HIGH', 'URGENT')
            )
        ");

        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT categories_sort_order_check
            CHECK (sort_order >= 0)
        ");

        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT categories_not_own_parent_check
            CHECK (parent_id IS NULL OR parent_id <> id)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
