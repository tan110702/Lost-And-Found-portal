<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "lost_found";

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname
);

if ($conn->connect_error) {
    die("Connection Failed");
}

$conn->set_charset("utf8mb4");

?>
