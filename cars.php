<?php
session_start();
include 'db.php';

// Araçları veritabanından getir
$stmt = $conn->prepare("SELECT * FROM cars");
$stmt->execute();
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Listeleme</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Araç Listesi</h2>
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?php echo $car['image_url']; ?>" class="card-img-top" alt="Araç Görseli" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $car['brand'] . " " . $car['model']; ?></h5>
                            <p class="card-text">Fiyat: <?php echo $car['price_per_day']; ?> TL / Gün</p>
                            <p class="card-text">Durum: 
                                <?php echo $car['is_available'] ? 'Uygun' : 'Kiralanmış'; ?>
                            </p>
                            <?php if ($car['is_available']): ?>
                            <a href="rent.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary">Kirala</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Kiralanmış</button>
                        <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
