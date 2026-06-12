<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'logo',
        'color_primario',
        'color_secundario',
        'activo'
    ];

    public function usuarios()
    {
        return $this->hasMany(UsuarioSistema::class);
    }

    public function modulos()
    {
        return $this->belongsToMany(
            Modulo::class,
            'empresa_modulos'
        );
    }
}