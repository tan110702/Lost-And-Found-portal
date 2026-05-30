<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($name === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Enter a valid email"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "Password must be at least 6 characters"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    echo json_encode(["message" => "User registered"]);
} else {
    http_response_code(400);
    echo json_encode(["error" => $conn->errno === 1062 ? "Email already registered" : "Registration failed"]);
}
?>
