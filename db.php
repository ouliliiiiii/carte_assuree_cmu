<?php
//$host = "localhost:3306";
//$db = "qr";
//$user = "root";
//$pass = "Dsi@sencsu2025!!";

$host = "localhost:3306";
$db = "qr";
$user = "root";
$pass = "0000";

try 
{
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) 
{
    die("Erreur : " . $e->getMessage());
}
?>
