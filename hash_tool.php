<?php
include("comunes/bloque_Seguridad.php");
include("Utilidades/CSRFProtection.php");

$hash_generado = '';
$resultado_verificacion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verificarFormulario();

    if ($_POST['accion'] === 'generar') {
        $clave = htmlspecialchars(trim($_POST['clave_plain']));
        $options = ['cost' => 13];
        $hash_generado = password_hash($clave, PASSWORD_BCRYPT, $options);
    }

    if ($_POST['accion'] === 'validar') {
        $clave = $_POST['clave_validar'];
        $hash  = $_POST['hash_validar'];
        $resultado_verificacion = password_verify($clave, $hash)
            ? "✅ El hash es válido — las contraseñas coinciden"
            : "❌ El hash NO es válido";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Herramienta de Hash</title>
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div align="center">
        <br>
        <h2>Generador y Validador de Hash BCrypt</h2>
        <br>

        <!-- Generar hash -->
        <fieldset style="width:500px; margin-bottom:20px;">
            <legend><strong>Generar Hash</strong></legend>
            <form method="POST">
                <?php echo CSRFProtection::campoHidden(); ?>
                <input type="hidden" name="accion" value="generar">
                <br>
                <label>Contraseña:</label>
                <input type="text" name="clave_plain" required placeholder="Escribe una contraseña">
                <br><br>
                <input type="submit" value="Generar Hash" class="clear">
            </form>
            <?php if ($hash_generado): ?>
                <br>
                <p><strong>Hash generado:</strong></p>
                <textarea rows="3" cols="60" readonly><?php echo htmlspecialchars($hash_generado); ?></textarea>
            <?php endif; ?>
        </fieldset>

        <!-- Validar hash -->
        <fieldset style="width:500px; margin-bottom:20px;">
            <legend><strong>Validar Hash</strong></legend>
            <form method="POST">
                <?php echo CSRFProtection::campoHidden(); ?>
                <input type="hidden" name="accion" value="validar">
                <br>
                <label>Contraseña:</label>
                <input type="text" name="clave_validar" required placeholder="Contraseña en texto plano">
                <br><br>
                <label>Hash:</label>
                <input type="text" name="hash_validar" required placeholder="Pega el hash aquí" style="width:350px;">
                <br><br>
                <input type="submit" value="Validar" class="clear">
            </form>
            <?php if ($resultado_verificacion): ?>
                <br>
                <p style="font-size:18px;"><?php echo $resultado_verificacion; ?></p>
            <?php endif; ?>
        </fieldset>

        <a href="formularios/PanelControl.php">← Volver al panel</a>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>