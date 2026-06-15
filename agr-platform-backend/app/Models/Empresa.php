<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresa extends Model
{
    use HasFactory;

    public const PLAN_BASICO = 'basico';
    public const PLAN_PROFESIONAL = 'profesional';
    public const PLAN_EMPRESARIAL = 'empresarial';

    protected $fillable = [
        'nombre',
        'slug',
        'logo',
        'color_primario',
        'color_secundario',
        'plan',
        'fecha_vencimiento',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_vencimiento' => 'date',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public static function planesPermitidos(): array
    {
        return [
            self::PLAN_BASICO,
            self::PLAN_PROFESIONAL,
            self::PLAN_EMPRESARIAL,
        ];
    }

    public function getPlanNombreAttribute(): string
    {
        return match ($this->plan) {
            self::PLAN_PROFESIONAL => 'Profesional',
            self::PLAN_EMPRESARIAL => 'Empresarial',
            default => 'Básico',
        };
    }

    public function getVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento
            ? now()->startOfDay()->greaterThan($this->fecha_vencimiento)
            : false;
    }

    public function usuarios()
    {
        return $this->hasMany(UsuarioSistema::class);
    }

    public function modulos()
    {
        return $this->belongsToMany(
            Modulo::class,
            'empresa_modulos'
        )->withTimestamps();
    }
}
