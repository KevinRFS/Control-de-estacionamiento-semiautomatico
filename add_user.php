<?php
$conn = new mysqli("localhost","root","","parking_access");

$data = json_decode(file_get_contents("php://input"), true);

$uid = strtoupper(trim($data['uid']));
$nombre = $data['nombre'];

$stmt = $conn->prepare("
    INSERT INTO rfid_cards (card_uid, owner_name, access_status)
    VALUES (?, ?, 'ACTIVE')
");

$stmt->bind_param("ss", $uid, $nombre);
$stmt->execute();

echo "OK";