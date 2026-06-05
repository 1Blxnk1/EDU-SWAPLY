<?php
// Database connection. Uses env vars in production (Railway provides MYSQL*),
// falls back to XAMPP defaults for local dev.

$host     = getenv('MYSQLHOST')     ?: 'localhost';
$port     = (int)(getenv('MYSQLPORT') ?: 3306);
$username = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'swaply';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
