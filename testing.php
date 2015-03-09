<?php

//require_once 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=cos', 'root', 'root');

print_r($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
exit;

$stmt = $pdo->prepare('SELECT * FROM users GROUP BY logins HAVING logins > ?');
$stmt->execute([10]);
$result = $stmt->fetchAll();

print_r($result);
