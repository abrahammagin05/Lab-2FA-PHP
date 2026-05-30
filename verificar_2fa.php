<?php
session_start();

// Si no pasó la fase 1, redirigir al login
if (!isset($_SESSION['pre_auth_user'])) {
    header("Location: login.php");
    exit;
}

require 'vendor/autoload.php';
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

include("clases/mysql.inc.php");
include("Utilidades/CSRFProtection.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verificarFormulario();

    $pdo     = new mod_db();
    $conn    = $pdo->getConexion();
    $usuario = $_SESSION['pre_auth_user'];

    // Obtener el secreto 2FA del usuario
    $stmt = $conn->prepare("SELECT secret_2fa FROM usuarios WHERE Usuario = :u");
    $stmt->execute([':u' => $usuario]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $g    = new GoogleAuthenticator();
    $code = trim($_POST['codigo_2fa']);

    if ($g->checkCode($row['secret_2fa'], $code)) {
        // Fase 2 exitosa — destruir sesión temporal y crear sesión definitiva
        $nombre = $_SESSION['pre_auth_user'];
        session_destroy();
        session_start();
        $_SESSION['autenticado'] = "SI";
        $_SESSION['Usuario']     = $nombre;
        header("Location: formularios/PanelControl.php");
        exit;
    } else {
        $error = "Código incorrecto. Intenta de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div align="center">
        <br><br>
        <h2>Verificación de dos factores</h2>
        <p>Ingresa el código de 6 dígitos de <strong>Google Authenticator</strong></p>

        <?php if ($error): ?>
            <p style="color:red"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" class="cmxform">
            <?php echo CSRFProtection::campoHidden(); ?>
            <table width="400" border="0" align="center">
                <tr>
                    <td>Código:</td>
                    <td>
                        <input type="text" name="codigo_2fa" 
                               maxlength="6" required 
                               placeholder="000000"
                               style="font-size:24px; text-align:center; width:150px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <br>
                        <input type="submit" value="Verificar" class="clear">
                    </td>
                </tr>
            </table>
        </form>
        <br>
        <a href="login.php">← Volver al login</a>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>