<?php

namespace app\models;

class EmpresaChipax {
    const OTZI = 1;
    const CONEJERO_MAQUINARIAS_SPA = 2;

    public static function getName($value) {
        switch ($value) {
            case self::OTZI:
                return 'Otzi';
            case self::CONEJERO_MAQUINARIAS_SPA:
                return 'Conejero Maquinarias SPA';
            default:
                return null;
        }
    }
}
