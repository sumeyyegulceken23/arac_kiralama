<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $user_id = $_SESSION['user_id'];

    // Araç uygun mu kontrol et
    $stmt = $conn->prepare("SELECT is_available FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($car && $car['is_available']) {
        // Kiralama işlemi
        $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, rent_date) VALUES (?, ?, CURDATE())");
        $stmt->execute([$user_id, $car_id]);

        // Aracın durumunu güncelle
        $stmt = $conn->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
        $stmt->execute([$car_id]);

        echo "<script>alert('Araç başarıyla kiralandı!'); window.location.href='cars.php';</script>";
    } else {
        echo "<script>alert('Bu araç kiralanmış durumda.'); window.location.href='cars.php';</script>";
    }
} else {
    header('Location: cars.php');
    exit;
}
?>
