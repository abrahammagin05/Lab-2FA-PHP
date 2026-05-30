<?PHP
class SanitizarEntrada {

    
    // Método 1: Para nombres y apellidos
    public static function CadTitulo($valor) {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        return ucwords(strtolower($valor));
    }

    // Método 2: Para usuario (solo letras, números y guión bajo)
    public static function limpiarCadena($valor) {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        $valor = preg_replace('/[^a-zA-Z0-9_]/', '', $valor);
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    // Método 3: Para espacios y caracteres peligrosos
    public static function limpiarEspacios($valor) {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    // Método 4: Para correos
    public static function sanitizarEmail($valor) {
        $valor = trim($valor);
        $valor = filter_var($valor, FILTER_SANITIZE_EMAIL);
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) return false;
        return strtolower($valor);
    }

    // Método 5: Para validar Sexo
    public static function validarSexo($valor) {
        $permitidos = ['M', 'F', 'Otro'];
        return in_array($valor, $permitidos) ? $valor : false;
    }

   

}//SanitizarEntrada

//$nombre = "<b>Juan</b> ";
//$nombreLimpio = SanitizarEntrada::limpiarCadena($nombre);  
//echo "la salida es: ".$nombre."<br>";
?>