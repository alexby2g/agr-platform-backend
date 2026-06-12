<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_sistemas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            $table->string('nombre');
            $table->string('usuario')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');

            $table->enum('rol', [
                'super_admin',
                'admin_empresa',
                'usuario'
            ])->default('usuario');

            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_sistemas');
    }
};
