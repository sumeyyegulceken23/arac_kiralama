<?php
session_start();
include 'db.php';

// Kullanıcının oturum açıp açmadığını kontrol et
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Araç ID'sinin gönderilip gönderilmediğini kontrol et
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $user_id = $_SESSION['user_id'];

    // Araç uygun mu kontrol et
    $stmt = $conn->prepare("SELECT is_available FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($car && $car['is_available']) {
        // Teslim tarihi al
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $return_date = $_POST['return_date'];

            try {
                $conn->beginTransaction();

                // Kiralama kaydını ekle
                $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, rent_date, return_date) VALUES (?, ?, CURDATE(), ?)");
                if ($stmt->execute([$user_id, $car_id, $return_date])) {
                    // Rezervasyonu ekle
                    $stmt = $conn->prepare("INSERT INTO reservations (user_id, car_id, reservation_date, return_date, status) VALUES (?, ?, CURDATE(), ?, 'Onaylandı')");
                    $stmt->execute([$user_id, $car_id, $return_date]); // return_date ekleniyor

                    // Aracın durumunu güncelle
                    $stmt = $conn->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
                    $stmt->execute([$car_id]);

                    $conn->commit();
                    echo "<script>alert('Araç başarıyla kiralandı!'); window.location.href='cars.php';</script>";
                } else {
                    throw new Exception("Kiralama işlemi sırasında bir hata oluştu.");
                }
            } catch (Exception $e) {
                $conn->rollBack();
                echo "<script>alert('" . $e->getMessage() . "'); window.location.href='cars.php';</script>";
            }
        } else {
            // Teslim tarihi formu göster
            echo '<form method="POST" action="">
                    <label for="return_date">Araç Teslim Tarihi:</label>
                    <input type="date" id="return_date" name="return_date" required>
                    <button type="submit" class="btn btn-primary">Kiralama İşlemini Tamamla</button>
                  </form>';
        }
    } else {
        echo "<script>alert('Bu araç kiralanmış durumda.'); window.location.href='cars.php';</script>";
    }
} else {
    header('Location: cars.php');
    exit;
}
?>
