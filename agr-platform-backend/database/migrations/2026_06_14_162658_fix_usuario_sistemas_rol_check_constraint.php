<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE usuario_sistemas DROP CONSTRAINT IF EXISTS usuario_sistemas_rol_check');

            DB::statement("
                ALTER TABLE usuario_sistemas
                ADD CONSTRAINT usuario_sistemas_rol_check
                CHECK (rol IN ('super_admin', 'administrador', 'empleado'))
            ");
        }

        DB::table('usuario_sistemas')
            ->where('rol', 'admin_empresa')
            ->update(['rol' => 'administrador']);

        DB::table('usuario_sistemas')
            ->where('rol', 'usuario')
            ->update(['rol' => 'empleado']);
    }

    public function down(): void
    {
        DB::table('usuario_sistemas')
            ->where('rol', 'administrador')
            ->update(['rol' => 'admin_empresa']);

        DB::table('usuario_sistemas')
            ->where('rol', 'empleado')
            ->update(['rol' => 'usuario']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE usuario_sistemas DROP CONSTRAINT IF EXISTS usuario_sistemas_rol_check');

            DB::statement("
                ALTER TABLE usuario_sistemas
                ADD CONSTRAINT usuario_sistemas_rol_check
                CHECK (rol IN ('super_admin', 'admin_empresa', 'usuario'))
            ");
        }
    }
};