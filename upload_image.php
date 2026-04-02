<?php
$name = $_GET['name'] ?? ("capture_" . date("Ymd_His") . ".jpg");

$path = "uploads/" . basename($name);

$data = file_get_contents("php://input");

if (file_put_contents($path, $data)) {
    echo "OK";
} else {
    echo "ERROR";
}
?>

