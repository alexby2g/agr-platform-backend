<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario_sistemas', function (Blueprint $table) {
            if (!Schema::hasColumn('usuario_sistemas', 'empleado_id')) {
                $table->unsignedBigInteger('empleado_id')->nullable()->after('empresa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuario_sistemas', function (Blueprint $table) {
            if (Schema::hasColumn('usuario_sistemas', 'empleado_id')) {
                $table->dropColumn('empleado_id');
            }
        });
    }
};