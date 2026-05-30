<?php
include("mysql.inc.php");

$classPDO = new mod_db();
$conn = $classPDO->getConexion();

try {
    $email = $_POST['email'];
    $query = $conn->prepare("SELECT * FROM usuarios WHERE Correo = :email");
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo (count($result) >= 1) ? "existe" : "libre";

} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}
?>