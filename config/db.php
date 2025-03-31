<?php
$host = 'localhost'; // Host
$dbname = 'zouhair_elearning'; // Database name
$username = 'root'; // MySQL username
$password = ''; // MySQL password

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
