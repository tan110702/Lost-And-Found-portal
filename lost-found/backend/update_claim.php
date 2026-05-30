<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data["id"] ?? 0);
$status = $data["status"] ?? "";

if ($id <= 0 || !in_array($status, ["approved", "rejected"], true)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid claim update"]);
    exit;
}

$stmt = $conn->prepare("UPDATE claims SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Updated"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Could not update claim"]);
}
?>
