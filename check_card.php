<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parking_access";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Revisar si llega UID
if(isset($_GET['uid'])){
    // Limpiar UID: quitar espacios y forzar mayúsculas
    $uid = strtoupper(trim($_GET['uid']));

    // Debug opcional: log del UID recibido
    error_log("UID recibido: '$uid'");

    // Consultar si existe y está activo
    $sql = "SELECT * FROM rfid_cards WHERE UPPER(TRIM(card_uid))='$uid' AND access_status='ACTIVE'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "ACCESS_GRANTED";
        $status = "GRANTED";
    } else {
        echo "ACCESS_DENIED";
        $status = "DENIED";
    }

    // Registrar en access_log
    $log_sql = "INSERT INTO access_log (card_uid, access_status) VALUES ('$uid', '$status')";
    $conn->query($log_sql);
}
$conn->close();
?>


