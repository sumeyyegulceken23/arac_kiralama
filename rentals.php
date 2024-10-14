<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının kiraladığı araçları getir
$stmt = $conn->prepare("
    SELECT rentals.id, cars.brand, cars.model, rentals.rent_date 
    FROM rentals 
    JOIN cars ON rentals.car_id = cars.id 
    WHERE rentals.user_id = ?
");
$stmt->execute([$user_id]);
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
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marka</th>
                    <th>Model</th>
                    <th>Kiralama Tarihi</th>
                    <th>Geri Teslim</th> <!-- Geri teslim için başlık eklendi -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><?php echo $rental['id']; ?></td>
                        <td><?php echo $rental['brand']; ?></td>
                        <td><?php echo $rental['model']; ?></td>
                        <td><?php echo $rental['rent_date']; ?></td>
                        <td>
                            <a href="return.php?rental_id=<?php echo $rental['id']; ?>" class="btn btn-danger">Geri Teslim Et</a>
                        </td> <!-- Geri teslim butonu eklendi -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
