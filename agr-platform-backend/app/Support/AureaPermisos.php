<?php

namespace App\Support;

use App\Models\UsuarioSistema;

class AureaPermisos
{
    public static function todos(): array
    {
        return [
            'aurea.dashboard.ver',
            'aurea.configuracion.ver',
            'aurea.configuracion.editar',

            'aurea.clientes.ver',
            'aurea.clientes.crear',
            'aurea.clientes.editar',
            'aurea.clientes.eliminar',
            'aurea.clientes.historial',

            'aurea.citas.ver',
            'aurea.citas.crear',
            'aurea.citas.editar',
            'aurea.citas.finalizar',
            'aurea.citas.eliminar',

            'aurea.pagos.ver',
            'aurea.pagos.crear',
            'aurea.pagos.factura',
            'aurea.pagos.eliminar',

            'aurea.servicios.ver',
            'aurea.servicios.crear',
            'aurea.servicios.editar',
            'aurea.servicios.eliminar',

            'aurea.empleados.ver',
            'aurea.empleados.activos',
            'aurea.empleados.crear',
            'aurea.empleados.editar',
            'aurea.empleados.eliminar',
            'aurea.empleados.comisiones',

            'aurea.caja.ver',
            'aurea.historial.ver',
            'aurea.historial.restaurar',
            'aurea.historial.eliminar',
            'aurea.reportes.ver',
            'aurea.notificaciones.ver',
            'aurea.notificaciones.editar',
        ];
    }

    public static function empleadoBase(): array
    {
        return [
            'aurea.dashboard.ver',

            'aurea.clientes.ver',
            'aurea.clientes.crear',
            'aurea.clientes.editar',
            'aurea.clientes.historial',

            'aurea.citas.ver',
            'aurea.citas.crear',
            'aurea.citas.editar',
            'aurea.citas.finalizar',

            'aurea.pagos.ver',
            'aurea.pagos.crear',
            'aurea.pagos.factura',

            'aurea.servicios.ver',
            'aurea.empleados.activos',

            'aurea.notificaciones.ver',
            'aurea.notificaciones.editar',
        ];
    }

    public static function paraUsuario(?UsuarioSistema $usuario): array
    {
        if (!$usuario) {
            return [];
        }

        $rol = UsuarioSistema::normalizarRol($usuario->rol);

        if (in_array($rol, [UsuarioSistema::ROL_SUPER_ADMIN, UsuarioSistema::ROL_ADMINISTRADOR], true)) {
            return self::todos();
        }

        return self::empleadoBase();
    }

    public static function usuarioPuede(?UsuarioSistema $usuario, string $permiso): bool
    {
        return in_array($permiso, self::paraUsuario($usuario), true);
    }
}
