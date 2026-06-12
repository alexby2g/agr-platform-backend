<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioSistema extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'usuario_sistemas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'usuario',
        'email',
        'password',
        'rol',
        'activo'
    ];

    protected $hidden = [
        'password'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}