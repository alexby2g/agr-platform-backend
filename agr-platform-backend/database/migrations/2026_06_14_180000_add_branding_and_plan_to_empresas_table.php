<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'plan')) {
                $table->string('plan')->default('basico')->after('color_secundario');
            }

            if (!Schema::hasColumn('empresas', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('plan');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE empresas DROP CONSTRAINT IF EXISTS empresas_plan_check');
            DB::statement("
                ALTER TABLE empresas
                ADD CONSTRAINT empresas_plan_check
                CHECK (plan IN ('basico', 'profesional', 'empresarial'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE empresas DROP CONSTRAINT IF EXISTS empresas_plan_check');
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'fecha_vencimiento')) {
                $table->dropColumn('fecha_vencimiento');
            }

            if (Schema::hasColumn('empresas', 'plan')) {
                $table->dropColumn('plan');
            }
        });
    }
};
