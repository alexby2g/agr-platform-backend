<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Modulo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'ruta_frontend',
        'color',
        'activo'
    ];

    public function empresas()
    {
        return $this->belongsToMany(
            Empresa::class,
            'empresa_modulos'
        );
    }
}