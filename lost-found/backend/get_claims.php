<?php
include "db.php";

$user_id = (int)($_GET["user_id"] ?? 0);
$role = $_GET["role"] ?? "user";

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "User is required"]);
    exit;
}

if ($role === "admin") {
    $stmt = $conn->prepare("SELECT claims.id, claims.phone, claims.message, claims.status, claims.created_at,
        items.title, items.image,
        users.name AS claimant_name, users.email AS claimant_email
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users ON claims.user_id = users.id
        ORDER BY claims.created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT claims.id, claims.phone, claims.message, claims.status, claims.created_at,
        items.title, items.image,
        users.name AS claimant_name, users.email AS claimant_email
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users ON claims.user_id = users.id
        WHERE items.user_id = ?
        ORDER BY claims.created_at DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
