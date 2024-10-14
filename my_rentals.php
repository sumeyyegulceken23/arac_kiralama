<?php
session_start();
include 'db.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kullanıcının kiraladığı araçları getir
$stmt = $conn->prepare("
    SELECT cars.brand, cars.model, rentals.rental_date 
    FROM rentals 
    JOIN cars ON rentals.car_id = cars.id 
    WHERE rentals.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiraladığım Araçlar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Kiraladığım Araçlar</h2>
        <ul class="list-group">
            <?php foreach ($rentals as $rental): ?>
                <li class="list-group-item">
                    <?php echo $rental['brand'] . " " . $rental['model']; ?> 
                    - Kiralama Tarihi: <?php echo $rental['rental_date']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
