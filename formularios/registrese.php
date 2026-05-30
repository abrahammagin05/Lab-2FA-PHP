<?php
session_start();
require_once "../Utilidades/CSRFProtection.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <link rel="shortcut icon" href="../patria/5564844.png">
    <link rel="stylesheet" href="../css/cmxform.css">
    <link rel="stylesheet" href="../Estilos/Techmania.css">
    <style>
        .registro-container {
            width: 480px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            padding: 30px 40px;
        }
        .registro-container h2 {
            text-align: center;
            color: #1a5276;
            margin-bottom: 25px;
            font-size: 22px;
        }
        .campo {
            margin-bottom: 15px;
        }
        .campo label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 13px;
        }
        .campo input, .campo select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .campo input:focus, .campo select:focus {
            border-color: #1a5276;
            outline: none;
            box-shadow: 0 0 5px rgba(26,82,118,0.3);
        }
        .btn-registrar {
            width: 100%;
            padding: 11px;
            background: #1a5276;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .btn-registrar:hover {
            background: #154360;
        }
        #mensaje-estado {
            display: block;
            text-align: center;
            margin-bottom: 10px;
            font-size: 13px;
        }
        label.error {
            color: red;
            font-size: 12px;
            font-weight: normal;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }
        .login-link a {
            color: #1a5276;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>

    <div class="registro-container">
        <h2>Registro de Usuario</h2>

        <span id="mensaje-estado"></span>

        <form id="form1" method="POST" action="../procesar_registro.php">
            <?php echo CSRFProtection::campoHidden(); ?>
            <input type="hidden" name="Accion" value="Guardar">

            <div class="campo">
                <label>Nombre:</label>
                <input type="text" name="nombre" id="nombre" placeholder="Tu nombre">
            </div>

            <div class="campo">
                <label>Apellido:</label>
                <input type="text" name="apellido" id="apellido" placeholder="Tu apellido">
            </div>

            <div class="campo">
                <label>Sexo:</label>
                <select name="sexo" id="sexo">
                    <option value="">Selecciona</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div class="campo">
                <label>Usuario:</label>
                <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario">
            </div>

            <div class="campo">
                <label>Correo electrónico:</label>
                <input type="email" name="email1" id="email1" placeholder="correo@ejemplo.com">
            </div>

            <div class="campo">
                <label>Contraseña:</label>
                <input type="password" name="clave" id="clave" placeholder="Mínimo 8 caracteres">
            </div>

            <div class="campo">
                <label>Repetir Contraseña:</label>
                <input type="password" name="clave_again" id="clave_again" placeholder="Repite tu contraseña">
            </div>

            <button type="submit" class="btn-registrar">Registrarse</button>
        </form>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="../login.php">Inicia sesión</a>
        </div>
    </div>

    <?php include("../comunes/footer.php"); ?>
</div>

<script>
$(document).ready(function() {
    $("#form1").validate({
        rules: {
            nombre:     { required: true },
            apellido:   { required: true },
            sexo:       { required: true },
            usuario:    { required: true },
            clave:      { required: true, minlength: 8 },
            clave_again:{ required: true, equalTo: "#clave" },
            email1:     { required: true, email: true }
        },
        messages: {
            clave_again: { equalTo: "Las contraseñas no coinciden" },
            email1:      { email: "Ingresa un correo válido" },
            clave:       { minlength: "Mínimo 8 caracteres" }
        },
        submitHandler: function(form1) {
            var email1 = $("#email1").val();
            $.ajax({
                type: "POST",
                url: "../clases/VerificarCorreo.php",
                data: { email: email1 },
                dataType: "html",
                beforeSend: function() {
                    $("#mensaje-estado").html("Verificando correo...");
                },
                success: function(datos) {
                    datos = $.trim(datos);
                    if (datos == "libre") {
                        $("#mensaje-estado").html("");
                        form1.submit();
                    } else {
                        $("#mensaje-estado").html(
                            "<span style='color:red'>❌ Este correo ya está en uso.</span>"
                        );
                    }
                },
                error: function() { alert("Error al verificar correo"); }
            });
        }
    });
});
</script>
</body>
</html>