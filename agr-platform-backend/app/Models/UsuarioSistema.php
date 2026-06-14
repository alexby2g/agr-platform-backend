<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioSistema extends Authenticatable
{
    use HasFactory, HasApiTokens;

    public const ROL_SUPER_ADMIN = 'super_admin';
    public const ROL_ADMINISTRADOR = 'administrador';
    public const ROL_EMPLEADO = 'empleado';

    protected $table = 'usuario_sistemas';

    protected $fillable = [
        'empresa_id',
        'empleado_id',
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

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    public static function rolesPermitidos(): array
    {
        return [
            self::ROL_SUPER_ADMIN,
            self::ROL_ADMINISTRADOR,
            self::ROL_EMPLEADO,
        ];
    }

    public static function normalizarRol(?string $rol): string
    {
        return match ($rol) {
            'admin_empresa' => self::ROL_ADMINISTRADOR,
            'usuario' => self::ROL_EMPLEADO,
            self::ROL_SUPER_ADMIN,
            self::ROL_ADMINISTRADOR,
            self::ROL_EMPLEADO => $rol,
            default => self::ROL_EMPLEADO,
        };
    }

    public function getRolNombreAttribute(): string
    {
        return match ($this->rol) {
            self::ROL_SUPER_ADMIN => 'Super administrador',
            self::ROL_ADMINISTRADOR => 'Administrador',
            self::ROL_EMPLEADO => 'Empleado',
            default => 'Empleado',
        };
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === self::ROL_SUPER_ADMIN;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esEmpleado(): bool
    {
        return $this->rol === self::ROL_EMPLEADO;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
