<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$item_id = (int)($data["item_id"] ?? 0);
$user_id = (int)($data["user_id"] ?? 0);
$phone = trim($data["phone"] ?? "");
$message = trim($data["message"] ?? "Phone verification submitted.");

if ($item_id <= 0 || $user_id <= 0 || $phone === "") {
    http_response_code(400);
    echo json_encode(["error" => "Phone number is required"]);
    exit;
}

if (!preg_match("/^[0-9+\-\s()]{7,20}$/", $phone)) {
    http_response_code(400);
    echo json_encode(["error" => "Enter a valid phone number"]);
    exit;
}

$ownerStmt = $conn->prepare("SELECT user_id FROM items WHERE id = ?");
$ownerStmt->bind_param("i", $item_id);
$ownerStmt->execute();
$item = $ownerStmt->get_result()->fetch_assoc();

if (!$item) {
    http_response_code(404);
    echo json_encode(["error" => "Item not found"]);
    exit;
}

if ((int)$item["user_id"] === $user_id) {
    http_response_code(400);
    echo json_encode(["error" => "You cannot claim your own item"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO claims (item_id, user_id, phone, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $item_id, $user_id, $phone, $message);

if ($stmt->execute()) {
    echo json_encode(["message" => "Claim sent"]);
} else {
    http_response_code(400);
    echo json_encode(["error" => $conn->errno === 1062 ? "You already claimed this item" : "Could not send claim"]);
}
?>
