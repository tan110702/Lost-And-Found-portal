<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "lost_found");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$conn->set_charset("utf8mb4");

function column_exists($conn, $table, $column) {
    $database = $conn->query("SELECT DATABASE() AS db")->fetch_assoc()["db"];
    $stmt = $conn->prepare("SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("sss", $database, $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)$row["total"] > 0;
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (!column_exists($conn, "users", "role")) {
    $conn->query("ALTER TABLE users ADD role ENUM('user','admin') NOT NULL DEFAULT 'user'");
}

if (!column_exists($conn, "users", "created_at")) {
    $conn->query("ALTER TABLE users ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$conn->query("CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  type ENUM('lost','found') NOT NULL,
  image VARCHAR(255) NOT NULL,
  location VARCHAR(255) NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (!column_exists($conn, "items", "created_at")) {
    $conn->query("ALTER TABLE items ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$conn->query("CREATE TABLE IF NOT EXISTS claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  user_id INT NOT NULL,
  phone VARCHAR(20) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_claim (item_id, user_id)
)");

if (!column_exists($conn, "claims", "status")) {
    $conn->query("ALTER TABLE claims ADD status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
}

if (!column_exists($conn, "claims", "phone")) {
    $conn->query("ALTER TABLE claims ADD phone VARCHAR(20) NOT NULL DEFAULT '' AFTER user_id");
}

if (!column_exists($conn, "claims", "created_at")) {
    $conn->query("ALTER TABLE claims ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}
?>
