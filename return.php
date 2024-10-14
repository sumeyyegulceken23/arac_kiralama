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
    SELECT rentals.id, cars.brand, cars.model 
    FROM rentals 
    JOIN cars ON rentals.car_id = cars.id 
    WHERE rentals.user_id = ?
");
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['rental_id'])) {
    $rental_id = $_GET['rental_id'];

    // Araç geri teslim etme işlemi
    $stmt = $conn->prepare("DELETE FROM rentals WHERE id = ?");
    $stmt->execute([$rental_id]);

    // Öncelikle aracın durumunu güncelle
    $update_stmt = $conn->prepare("UPDATE cars SET is_available = 1 WHERE id = (SELECT car_id FROM rentals WHERE id = ?)");
    $update_stmt->execute([$rental_id]);

    echo "<script>alert('Araç başarıyla geri teslim edildi!'); window.location.href='return.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Geri Teslim</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Kiraladığım Araçları Geri Teslim Et</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marka</th>
                    <th>Model</th>
                    <th>Geri Teslim</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><?php echo $rental['id']; ?></td>
                        <td><?php echo $rental['brand']; ?></td>
                        <td><?php echo $rental['model']; ?></td>
                        <td>
                            <a href="return.php?rental_id=<?php echo $rental['id']; ?>" class="btn btn-danger">Geri Teslim Et</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
