<?php
/*$host = "localhost:3306";
$db = "carteassure_db";
$user = "root";
$pass = "Dsi@sencsu2025!!"; */

$host = "localhost:3306";
$db = "code_qr";
$user = "root";
$pass = "";

try 
{
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) 
{
    die("Erreur : " . $e->getMessage());
}
?>
