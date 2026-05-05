<?php
// db.php - Database Connection File with Railway Support

// Default Localhost (XAMPP) settings
$host     = 'localhost';
$dbname   = 'helpdesk_db';
$username = 'root';
$password = '';
$port     = '3306';

// Override with Railway Environment Variables if they exist
if (getenv('MYSQLHOST')) {
    $host     = getenv('MYSQLHOST');
    $dbname   = getenv('MYSQLDATABASE');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    $port     = getenv('MYSQLPORT');
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
