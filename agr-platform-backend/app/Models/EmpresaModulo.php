<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaModulo extends Model
{
    use HasFactory;

    protected $table = 'empresa_modulos';

    protected $fillable = [
        'empresa_id',
        'modulo_id',
        'activo',
        'fecha_inicio',
        'fecha_fin'
    ];
}