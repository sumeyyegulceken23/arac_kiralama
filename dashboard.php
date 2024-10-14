<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Hoş Geldiniz, <?php echo $_SESSION['user_name']; ?>!</h2>
        <p>Buradan araç kiralama sistemine geçiş yapabilirsiniz.</p>
        <a href="cars.php" class="btn btn-primary">Araçları Görüntüle ve Kirala</a>
    </div>
</body>
</html>
