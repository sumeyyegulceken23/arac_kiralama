<?php
$host = 'localhost';
$db = 'arac_kiralama';
$user = 'root';  // Eğer başka bir kullanıcı varsa onun adını yaz
$pass = '';  // Eğer şifre belirlediysen buraya ekle

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
