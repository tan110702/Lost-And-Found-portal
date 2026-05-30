<?php
include "db.php";

$title = trim($_POST["title"] ?? "");
$desc = trim($_POST["description"] ?? "");
$type = $_POST["type"] ?? "";
$location = trim($_POST["location"] ?? "");
$user_id = (int)($_POST["user_id"] ?? 0);

if ($title === "" || $desc === "" || $location === "" || !in_array($type, ["lost", "found"], true) || $user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Please complete all item fields"]);
    exit;
}

if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "Image upload is required"]);
    exit;
}

$allowedTypes = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif", "image/webp" => "webp"];
$mime = mime_content_type($_FILES["image"]["tmp_name"]);

if (!isset($allowedTypes[$mime])) {
    http_response_code(400);
    echo json_encode(["error" => "Upload a JPG, PNG, GIF, or WebP image"]);
    exit;
}

if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(["error" => "Image must be 5MB or smaller"]);
    exit;
}

$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$image = time() . "_" . bin2hex(random_bytes(6)) . "." . $allowedTypes[$mime];
$target = $uploadDir . $image;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
    http_response_code(500);
    echo json_encode(["error" => "Could not save uploaded image"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO items (title, description, type, image, location, user_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $title, $desc, $type, $image, $location, $user_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Item added"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Could not add item"]);
}
?>
