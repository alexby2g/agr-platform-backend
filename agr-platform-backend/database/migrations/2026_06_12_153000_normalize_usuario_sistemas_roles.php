<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('usuario_sistemas')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE usuario_sistemas MODIFY rol ENUM('super_admin','admin_empresa','usuario','administrador','empleado') NOT NULL DEFAULT 'empleado'");
        }

        DB::table('usuario_sistemas')
            ->where('rol', 'admin_empresa')
            ->update(['rol' => 'administrador']);

        DB::table('usuario_sistemas')
            ->where('rol', 'usuario')
            ->update(['rol' => 'empleado']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE usuario_sistemas MODIFY rol ENUM('super_admin','administrador','empleado') NOT NULL DEFAULT 'empleado'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('usuario_sistemas')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE usuario_sistemas MODIFY rol ENUM('super_admin','admin_empresa','usuario','administrador','empleado') NOT NULL DEFAULT 'usuario'");
        }

        DB::table('usuario_sistemas')
            ->where('rol', 'administrador')
            ->update(['rol' => 'admin_empresa']);

        DB::table('usuario_sistemas')
            ->where('rol', 'empleado')
            ->update(['rol' => 'usuario']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE usuario_sistemas MODIFY rol ENUM('super_admin','admin_empresa','usuario') NOT NULL DEFAULT 'usuario'");
        }
    }
};
