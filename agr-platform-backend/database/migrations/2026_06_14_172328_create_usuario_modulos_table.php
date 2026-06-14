<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_sistema_id')->constrained('usuario_sistemas')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['usuario_sistema_id', 'modulo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_modulos');
    }
};