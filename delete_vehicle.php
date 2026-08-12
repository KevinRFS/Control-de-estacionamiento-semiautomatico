<?php
$conn = new mysqli("localhost","root","","parking_access");

$data = json_decode(file_get_contents("php://input"), true);
$patente = $data['patente'];

$stmt = $conn->prepare("DELETE FROM vehicles WHERE plate_number = ?");
$stmt->bind_param("s", $patente);
$stmt->execute();

echo "OK";