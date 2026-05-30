<?php
require_once "SanitizarEntrada.php";

class RegistroUsuario {
    private $id;
    private $Nombre;
    private $Apellido;
    private $Sexo;
    private $Usuario;
    private $Correo;
    private $contrasena;
    private $hastGenerado;
    private $secret_2fa;
    private $pdo;
    private $tabla = "usuarios";
    private $FechaSistema;

    // Constructor: recibe y sanitiza los datos del POST
    public function __construct($datos, $pdo, &$arrMensaje) {
        $this->pdo = $pdo;
        $this->FechaSistema = date("Y-m-d H:i:s");

        if (isset($datos["nombre"]))
            $this->Nombre = SanitizarEntrada::CadTitulo($datos["nombre"]);
        else $arrMensaje[1] = "No trajo datos la Columna Nombre";

        if (isset($datos["apellido"]))
            $this->Apellido = SanitizarEntrada::CadTitulo($datos["apellido"]);
        else $arrMensaje[2] = "No trajo datos la Columna Apellido";

        if (isset($datos["sexo"]))
            $this->Sexo = SanitizarEntrada::validarSexo($datos["sexo"]);
        else $arrMensaje[6] = "No trajo datos la Columna Sexo";

        if (isset($datos["usuario"]))
            $this->Usuario = SanitizarEntrada::limpiarCadena($datos["usuario"]);
        else $arrMensaje[3] = "No trajo datos la Columna Usuario";

        if (isset($datos["email1"]))
            $this->Correo = SanitizarEntrada::limpiarEspacios($datos["email1"]);
        else $arrMensaje[4] = "No trajo datos la Columna Correo";

        if (isset($datos["clave"]))
            $this->contrasena = SanitizarEntrada::limpiarEspacios($datos["clave"]);
        else $arrMensaje[5] = "No trajo datos la Columna Clave";
    }

    // Encriptar contraseña
    public function encriptarClave() {
        $options = ['cost' => 13];
        $this->hastGenerado = password_hash(
            $this->contrasena, PASSWORD_BCRYPT, $options
        );
    }

    // Guardar usuario en BD
    public function Guardar_RegistroUsuario() {
        $this->encriptarClave();
        $data = [
            "Nombre"       => $this->Nombre,
            "Apellido"     => $this->Apellido,
            "Sexo"         => $this->Sexo,
            "Usuario"      => $this->Usuario,
            "Correo"       => $this->Correo,
            "HashMagic"    => $this->hastGenerado,
            "FechaSistema" => $this->FechaSistema
        ];
        $this->pdo->insertSeguro("usuarios", $data);
        $this->id = $this->pdo->insert_id();

        // Registrar en trazabilidad
        $dataTraz = [
            "Tabla"          => $this->tabla,
            "Acciones"       => "INSERT",
            "CodigoRegistro" => $this->id,
            "Usuario"        => $this->Usuario,
            "FechaSistema"   => $this->FechaSistema
        ];
        $this->pdo->insertSeguro("trazabilidad_acciones", $dataTraz);
    }

    // Guardar secreto 2FA
    public function GuardarMySecreto($secreto) {
        $datoSecreto = ["secret_2fa" => $secreto];
        $condicion   = ["id" => $this->id];
        if ($this->pdo->updateSeguro("usuarios", $datoSecreto, $condicion))
            return true;
        return false;
    }

    public function getUsuario() { return $this->Usuario; }
    public function getId()      { return $this->id; }
}
?>