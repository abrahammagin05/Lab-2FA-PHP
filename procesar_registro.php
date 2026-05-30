<?php
session_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once "Utilidades/CSRFProtection.php";
CSRFProtection::verificarFormulario();

include("clases/mysql.inc.php");
include("clases/SanitizarEntrada.php");
include("clases/RegistroUsuario.php");
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

$pdo = new mod_db();
$arrMensaje = array();

try {
    $MyRegistro = new RegistroUsuario($_POST, $pdo, $arrMensaje);

    if (count($arrMensaje) == 0) {
        $Accion = $_POST['Accion'];

        if ($Accion == "Guardar") {
            $MyRegistro->Guardar_RegistroUsuario();

            // Generar secreto 2FA
            $g      = new GoogleAuthenticator();
            $secret = $g->generateSecret();
            $MyRegistro->GuardarMySecreto($secret);

            // Generar QR
            $nombre_usuario   = $MyRegistro->getUsuario();
            $nombre_app       = 'MiSistemaLogin';
            $url              = GoogleQrUrl::generate($nombre_usuario, $secret, $nombre_app);
            $qr_url           = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $url;

            // Guardar en sesión
            $_SESSION['qr_url']        = $qr_url;
            $_SESSION['usuario_nuevo'] = $nombre_usuario;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escanea tu QR</title>
</head>
<body style="text-align:center; padding:40px;">
    <h2>¡Registro exitoso!</h2>
    <p>Escanea este código con <strong>Google Authenticator</strong></p>
    <img src="<?php echo $qr_url; ?>" alt="Código QR">
    <br><br>
    <p>Luego haz clic en: <a href="login.php">Ir al Login</a></p>
</body>
</html>
<?php
        }
    } else {
        foreach ($arrMensaje as $val) echo $val . '<br>';
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $pdo = null;
    $MyRegistro = null;
}
?>