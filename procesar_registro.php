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
    <link rel="shortcut icon" href="patria/5564844.png">
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
    <style>
        .qr-container {
            width: 450px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            padding: 35px 40px;
            text-align: center;
        }
        .qr-container h2 {
            color: #1a5276;
            font-size: 22px;
            margin-bottom: 10px;
        }
        .qr-container p {
            color: #555;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .qr-container img {
            border: 3px solid #1a5276;
            border-radius: 8px;
            padding: 8px;
        }
        .qr-steps {
            background: #eaf4fb;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            font-size: 13px;
            color: #333;
        }
        .qr-steps ol {
            margin: 0;
            padding-left: 18px;
        }
        .qr-steps li {
            margin-bottom: 6px;
        }
        .btn-login {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 30px;
            background: #1a5276;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #154360;
        }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div class="qr-container">
        <h2>¡Registro exitoso!</h2>
        <p>Escanea este código QR con <strong>Google Authenticator</strong></p>
        <img src="<?php echo $qr_url; ?>" alt="Código QR">
        <div class="qr-steps">
            <strong>Pasos:</strong>
            <ol>
                <li>Abre <strong>Google Authenticator</strong> en tu celular</li>
                <li>Toca el botón <strong>+</strong> → Escanear código QR</li>
                <li>Apunta la cámara a este código</li>
                <li>Listo — ya puedes iniciar sesión</li>
            </ol>
        </div>
        <a href="login.php" class="btn-login">Ir al Login →</a>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
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