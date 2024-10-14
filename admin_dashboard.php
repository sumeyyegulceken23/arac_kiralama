<?php 
session_start();
include 'db.php';

// Kullanıcı oturumu kontrolü yaptık
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Araçları ve kiralamaları veritabanından getirmeye yarar
$cars_stmt = $conn->query("SELECT COUNT(*) as total_cars FROM cars");
$total_cars = $cars_stmt->fetch(PDO::FETCH_ASSOC)['total_cars'];

$rentals_stmt = $conn->query("SELECT COUNT(*) as total_rentals FROM rentals");
$total_rentals = $rentals_stmt->fetch(PDO::FETCH_ASSOC)['total_rentals'];

$users_stmt = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role='customer'");
$total_users = $users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Süresi dolmak üzere olan kiralamaları kontrol et
$alerts_stmt = $conn->query("SELECT * FROM rentals WHERE return_date < NOW() AND return_date IS NOT NULL");
$alerts = $alerts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Araç kiralama trendleri için veri hazırlama
$months = [];
for ($i = 0; $i < 12; $i++) {
    $month = date('Y-m', strtotime("-$i month"));
    $months[$month] = 0; // Başlangıçta tüm ayların kiralama sayısını 0 olarak ayarlıyoruz
}

// Veritabanından kiralama sayısını çek
$stmt = $conn->prepare("SELECT DATE_FORMAT(rent_date, '%Y-%m') AS month, COUNT(*) AS rentals_count FROM rentals WHERE MONTH(rent_date) = 10 AND YEAR(rent_date) = YEAR(CURDATE()) GROUP BY month");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerçek kiralama sayılarını ilgili ayllar
foreach ($results as $result) {
    $months[$result['month']] = $result['rentals_count'];
}

// Kullanıcı artışı için veri hazırlama
$user_months = [];
for ($i = 0; $i < 12; $i++) {
    $month = date('Y-m', strtotime("-$i month"));
    $user_months[$month] = 0; // Başlangıçta tüm ayların kullanıcı sayısını 0 olarak ayarlıyoruz
}

// Veritabanından kullanıcı artışını çekmek için
$user_stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS user_count FROM users WHERE role='customer' GROUP BY month");
$user_stmt->execute();
$user_results = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerçek kullanıcı sayılarını ilgili aylara yerleştirme
foreach ($user_results as $user_result) {
    $user_months[$user_result['month']] = $user_result['user_count'];
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Admin Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Toplam Araç</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_cars; ?></h5>
                        <p class="card-text">Sistemde kayıtlı araç sayısı.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Toplam Kiralama</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_rentals; ?></h5>
                        <p class="card-text">Toplam kiralama sayısı.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Toplam Kullanıcı</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_users; ?></h5>
                        <p class="card-text">Sistemde kayıtlı kullanıcı sayısı.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafikler -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Araç Kiralama Trendleri</h4>
                <canvas id="rentalTrendChart"></canvas>
            </div>
            <div class="col-md-6">
                <h4>Kullanıcı Artışı</h4>
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Uyarılar -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h4>Uyarılar</h4>
                <div id="alerts" class="alert alert-warning" role="alert">
                    <!-- Buraya uyarılar eklenecek -->
                </div>
            </div>
        </div>

        <!-- Yeni Kullanıcılar -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h4>Yeni Kullanıcılar</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kullanıcı Adı</th>
                            <th>Email</th>
                            <th>Kayıt Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Son 5 kaydı çek
                        $new_users_stmt = $conn->query("SELECT username, email, created_at FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 5");
                        $new_users = $new_users_stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($new_users as $user) {
                            echo "<tr>
                                    <td>{$user['username']}</td>
                                    <td>{$user['email']}</td>
                                    <td>{$user['created_at']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const ctxRental = document.getElementById('rentalTrendChart').getContext('2d');
        const ctxUserGrowth = document.getElementById('userGrowthChart').getContext('2d');

        // Araç kiralama trendleri verisi
        const rentalTrendLabels = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        const rentalTrendData = <?php echo json_encode(array_values($months)); ?>; // Veritabanından çekilecek veri ile değiştirin.

        // Kullanıcı artışı verisi
        const userGrowthLabels = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        const userGrowthData = <?php echo json_encode(array_values($user_months)); ?>; // Veritabanından çekilecek veri ile değiştirin.

        const rentalTrendChart = new Chart(ctxRental, {
            type: 'line',
            data: {
                labels: rentalTrendLabels,
                datasets: [{
                    label: 'Kiralama Sayısı',
                    data: rentalTrendData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const userGrowthChart = new Chart(ctxUserGrowth, {
            type: 'bar',
            data: {
                labels: userGrowthLabels,
                datasets: [{
                    label: 'Kullanıcı Sayısı',
                    data: userGrowthData,
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Uyarılar
        const alertsDiv = document.getElementById('alerts');
        <?php if (count($alerts) > 0): ?>
            alertsDiv.innerHTML = 'Süreleri dolmak üzere olan kiralamalar var!';
        <?php else: ?>
            alertsDiv.innerHTML = 'Tüm kiralamalar zamanında dönecek.';
        <?php endif; ?>
    </script>
</body>
</html>
