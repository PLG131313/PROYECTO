<?php
// Configuración global de paginación para el sistema

class PaginacionConfig {
    // Configuración por defecto
    const VUELOS_POR_PAGINA_DEFAULT = 5;
    const PASAJEROS_POR_PAGINA_DEFAULT = 10;
    const COMPRAS_POR_PAGINA_DEFAULT = 8;

    // Opciones disponibles para el usuario
    const OPCIONES_VUELOS_POR_PAGINA = [5, 10, 15, 20];
    const OPCIONES_PASAJEROS_POR_PAGINA = [10, 20, 30, 50];

    /**
     * Obtiene el número de elementos por página desde la URL o usa el default
     */
    public static function obtenerElementosPorPagina($tipo = 'vuelos', $default = null) {
        $param_name = $tipo . '_por_pagina';
        $valor = isset($_GET[$param_name]) ? (int)$_GET[$param_name] : null;

        if ($default === null) {
            switch ($tipo) {
                case 'vuelos':
                    $default = self::VUELOS_POR_PAGINA_DEFAULT;
                    break;
                case 'pasajeros':
                    $default = self::PASAJEROS_POR_PAGINA_DEFAULT;
                    break;
                case 'compras':
                    $default = self::COMPRAS_POR_PAGINA_DEFAULT;
                    break;
                default:
                    $default = 10;
            }
        }

        // Validar que el valor esté en las opciones permitidas
        $opciones_permitidas = self::getOpcionesPermitidas($tipo);
        return in_array($valor, $opciones_permitidas) ? $valor : $default;
    }

    /**
     * Obtiene las opciones permitidas para un tipo de paginación
     */
    public static function getOpcionesPermitidas($tipo) {
        switch ($tipo) {
            case 'vuelos':
                return self::OPCIONES_VUELOS_POR_PAGINA;
            case 'pasajeros':
                return self::OPCIONES_PASAJEROS_POR_PAGINA;
            default:
                return [5, 10, 15, 20];
        }
    }

    /**
     * Genera HTML para selector de elementos por página
     */
    public static function generarSelectorElementosPorPagina($tipo, $actual, $url_base = '') {
        $opciones = self::getOpcionesPermitidas($tipo);
        $param_name = $tipo . '_por_pagina';

        $html = '<div class="d-flex align-items-center">';
        $html .= '<label class="me-2 text-muted">Mostrar:</label>';
        $html .= '<select class="form-select form-select-sm" style="width: auto;" onchange="cambiarElementosPorPagina(this.value, \'' . $tipo . '\')">';

        foreach ($opciones as $opcion) {
            $selected = ($opcion == $actual) ? 'selected' : '';
            $html .= '<option value="' . $opcion . '" ' . $selected . '>' . $opcion . '</option>';
        }

        $html .= '</select>';
        $html .= '<span class="ms-2 text-muted">por página</span>';
        $html .= '</div>';

        return $html;
    }
}
?>
