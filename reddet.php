<?php
session_start();
include 'db.php'; // Veritabanı bağlantısı

// Rezervasyon ID'sini al
if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];

    // Rezervasyonu reddet
    $stmt = $conn->prepare("UPDATE reservations SET status = 'Reddedildi' WHERE reservation_id = ?");
    if ($stmt->execute([$reservation_id])) {
        header("Location: admin_paneli.php?dashboard");
        exit;
    } else {
        echo "Rezervasyon reddedilirken bir hata oluştu.";
    }
} else {
    echo "Rezervasyon ID'si bulunamadı.";
}
?>
