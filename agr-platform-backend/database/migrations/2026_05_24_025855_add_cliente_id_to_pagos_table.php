<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos') && !Schema::hasColumn('pagos', 'cliente_id')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->foreignId('cliente_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('clientes')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pagos') && Schema::hasColumn('pagos', 'cliente_id')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->dropForeign(['cliente_id']);
                $table->dropColumn('cliente_id');
            });
        }
    }
};
