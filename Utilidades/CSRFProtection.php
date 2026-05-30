<?php
/**
 * Sistema de protección CSRF
 * Ubicación: Utilidades/CSRFProtection.php
 * Versión: 2.0 - Actualizada y mejorada
 */

class CSRFProtection {
    
    /**
     * Generar token CSRF
     */
    public static function generarToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Obtener token actual
     */
    public static function obtenerToken() {
        return isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    }
    
    /**
     * Validar token CSRF
     */
    public static function validarToken($token_recibido) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Comparación segura para evitar timing attacks
        return hash_equals($_SESSION['csrf_token'], $token_recibido);
    }
    
    /**
     * Generar campo hidden para formularios
     */
    public static function campoHidden() {
        $token = self::generarToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Verificar y procesar formulario
     */
    public static function verificarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token_recibido = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            
            if (!self::validarToken($token_recibido)) {
                http_response_code(403);
                die('Error de seguridad: Token CSRF inválido');
            }
        }
    }
    
    /**
     * Verificar token para peticiones AJAX
     */
    public static function verificarAjax() {
        $token = '';
        
        // Buscar token en headers o POST
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } elseif (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }
        
        if (!self::validarToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }
    }
    
    /**
     * Regenerar token (después de acciones críticas)
     */
    public static function regenerarToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generar meta tag para JavaScript
     */
    public static function metaTag() {
        $token = self::generarToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}

// Funciones de conveniencia (estilo procedural para compatibilidad)

/**
 * Obtener token CSRF
 */
function csrf_token() {
    return CSRFProtection::obtenerToken();
}

/**
 * Campo hidden para formularios
 */
function csrf_field() {
    return CSRFProtection::campoHidden();
}

/**
 * Verificar CSRF en formularios
 */
function verificar_csrf() {
    CSRFProtection::verificarFormulario();
}

/**
 * Meta tag para JavaScript
 */
function csrf_meta() {
    return CSRFProtection::metaTag();
}
?>
