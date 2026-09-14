<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_support_memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('role', 20);
            $table->boolean('is_active')->default(true);

            $table->timestampsTz();

            $table->unique(
                ['department_id', 'user_id'],
                'support_membership_department_user_unique'
            );

            $table->index(
                ['user_id', 'is_active'],
                'support_membership_user_active_index'
            );

            $table->index(
                ['department_id', 'role', 'is_active'],
                'support_membership_department_role_active_index'
            );
        });

        DB::statement("
            ALTER TABLE department_support_memberships
            ADD CONSTRAINT department_support_memberships_role_check
            CHECK (role IN ('AGENT', 'LEAD'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('department_support_memberships');
    }
};
