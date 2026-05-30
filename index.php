<?PHP
session_start();  
include ("clases/mysql.inc.php");	
$db = new mod_db();

require_once "Utilidades/CSRFProtection.php";
CSRFProtection::verificarFormulario();
include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php");
include("clases/objLoginAdmin.php");

	
$tolog=false;
 
 // $topanel=false;
 if (isset($_POST["tolog"]))
 	
 	$tolog = $_POST["tolog"];
 
 
 
 
  //$tolog es el nombre de un hidden del form de login, si no llegara a funcionar en hosting, se debe obtener de $_POST
    if(isset($tolog)&&($tolog=="true")&& ($_SERVER['REQUEST_METHOD'] === 'POST') ){
		
		//echo "<pre>";
		//var_dump($_SERVER);
		//echo"</pre>";
             
			$Usuario = $_POST['usuario'];
			$ClaveKey = $_POST['contrasena'];
			//echo "3l usuario es: ".$Usuario."<br>";
			//echo "3l ClaveKey es: ".$ClaveKey."<br>";

			echo "La dirección IP es ".$_SERVER['REMOTE_ADDR'];
			$ipRemoto = $_SERVER['REMOTE_ADDR'];

			$Logearme = new ValidacionLogin($Usuario, $ClaveKey,$ipRemoto, $db);
			
		if ($Logearme->logger()){
    $Logearme->autenticar();
    if ($Logearme->getIntentoLogin()){
        $Logearme->registrarIntentos();
        $tolog=false;
        // Sesión temporal fase 1
        $_SESSION['pre_auth_user'] = $Logearme->getUsuario();
        redireccionar("verificar_2fa.php");
    }else {
        $Logearme->registrarIntentos();
        $_SESSION["emsg"] =1;
        redireccionar("login.php");		
    }
}else {
    $_SESSION["emsg"] =1;
    redireccionar("login.php");
}

			
	    
    } else {
		//echo "hola como estas<br>";
		redireccionar("login.php");
	}
	
 
?>