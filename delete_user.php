<?php
$conn = new mysqli("localhost","root","","parking_access");

$data = json_decode(file_get_contents("php://input"), true);
$uid = $data['uid'];

$stmt = $conn->prepare("DELETE FROM rfid_cards WHERE card_uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();

echo "OK";