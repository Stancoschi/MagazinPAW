<?php
$db_host = 'localhost';
$db_name = 'StoreDB';
$db_user = 'stanco';
$db_pass = 'stanco'; 

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Aruncă excepții la erori
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returnează array-uri asociative
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Folosește prepared statements native
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode()); // Aruncă excepții
}
?>
