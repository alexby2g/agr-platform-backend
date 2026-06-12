<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\UsuarioSistema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgrPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // EMPRESAS

        $aurea = Empresa::updateOrCreate(
            ['slug' => 'aurea'],
            [
                'nombre' => 'AUREA Beauty',
                'logo' => null,
                'color_primario' => '#ec4899',
                'color_secundario' => '#9333ea',
                'activo' => true
            ]
        );

        $carlafit = Empresa::updateOrCreate(
            ['slug' => 'carlafit'],
            [
                'nombre' => 'CarlaFit',
                'logo' => null,
                'color_primario' => '#7c3aed',
                'color_secundario' => '#2563eb',
                'activo' => true
            ]
        );

        $electro = Empresa::updateOrCreate(
            ['slug' => 'electrofrio'],
            [
                'nombre' => 'Electro Frío',
                'logo' => null,
                'color_primario' => '#0284c7',
                'color_secundario' => '#06b6d4',
                'activo' => true
            ]
        );

        // MÓDULOS

        $m1 = Modulo::updateOrCreate(
            ['slug' => 'aurea'],
            [
                'nombre' => 'AUREA Beauty',
                'icono' => 'spa',
                'ruta_frontend' => '/apps/aurea',
                'color' => '#ec4899',
                'activo' => true
            ]
        );

        $m2 = Modulo::updateOrCreate(
            ['slug' => 'carlafit'],
            [
                'nombre' => 'CarlaFit',
                'icono' => 'fitness_center',
                'ruta_frontend' => '/apps/carlafit',
                'color' => '#7c3aed',
                'activo' => true
            ]
        );

        $m3 = Modulo::updateOrCreate(
            ['slug' => 'electrofrio'],
            [
                'nombre' => 'Electro Frío',
                'icono' => 'ac_unit',
                'ruta_frontend' => '/apps/electrofrio',
                'color' => '#0284c7',
                'activo' => true
            ]
        );

        // PERMISOS EMPRESA - MÓDULO

        $aurea->modulos()->syncWithoutDetaching([$m1->id]);
        $carlafit->modulos()->syncWithoutDetaching([$m2->id]);
        $electro->modulos()->syncWithoutDetaching([$m3->id]);

        // SUPER ADMIN

        UsuarioSistema::updateOrCreate(
            ['usuario' => 'alex'],
            [
                'empresa_id' => null,
                'nombre' => 'Alexander',
                'email' => 'alex@agrstudio.com',
                'password' => Hash::make('123456'),
                'rol' => 'super_admin',
                'activo' => true
            ]
        );
    }
}