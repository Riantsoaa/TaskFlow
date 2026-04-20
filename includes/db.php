<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default WAMPP password is empty
$dbname = 'taskflow';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage() . "<br>Avez-vous bien importé la base de données ?");
}
?>
