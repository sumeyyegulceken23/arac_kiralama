<?php
include 'db.php'; // Veritabanı bağlantısı

session_start();
$user_id = $_SESSION['user_id']; // Örnek kullanıcı ID'si, oturumdan alabilirsiniz
$car_id = $_POST['car_id'];
$reservation_date = $_POST['reservation_date'];
$return_date = $_POST['return_date'];

$sql = "INSERT INTO reservations (user_id, car_id, reservation_date, return_date) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $car_id, $reservation_date, $return_date]);

if ($stmt) {
    echo "Rezervasyon başarılı! Onay bekleniyor.";
} else {
    echo "Bir hata oluştu.";
}
?>
