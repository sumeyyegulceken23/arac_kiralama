<?php
session_start();
include 'db.php'; // Veritabanı bağlantısı

$error = "";

// Kayıt İşlemi
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email']; // E-posta adresi alındı
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Aynı kullanıcı adı veya e-posta var mı kontrol et
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);

    if ($check->rowCount() > 0) {
        $error = "Bu kullanıcı adı veya e-posta zaten kayıtlı!";
    } else {
        // Kullanıcı kaydı
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$username, $email, $password]);

        header("Location: admin_paneli.php?login");
        exit;
    }
}

// Giriş İşlemi
if (isset($_POST['login'])) {
    $usernameOrEmail = $_POST['username']; // Kullanıcı adı veya e-posta
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Admin mi kontrol et
        if ($user['role'] === 'admin') {
            $_SESSION['admin'] = $user['username'];
            header("Location: admin_paneli.php?dashboard");
        } else {
            $error = "Bu sayfaya yalnızca adminler erişebilir!";
        }
        exit;
    } else {
        $error = "Kullanıcı adı veya şifre hatalı!";
    }
}

// Çıkış İşlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_paneli.php?login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Rezervasyon Yönetimi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        /* Mobilde tabloda taşma sorununu önlemek için */
        .table-responsive {
            overflow-x: auto;
        }

        /* Başlık ve düğme aralıkları */
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            width: 100%;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <?php if (isset($_GET['login'])): ?>
        <h2>Admin Giriş</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Kullanıcı Adı veya E-posta</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Giriş Yap</button>
            <p class="text-danger mt-2"><?= $error ?></p>
        </form>

    <?php elseif (isset($_GET['register'])): ?>
        <h2>Admin Kayıt</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>E-posta</label>
                <input type="email" name="email" class="form-control" required> <!-- E-posta alanı eklendi -->
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-success">Kayıt Ol</button>
            <p class="text-danger mt-2"><?= $error ?></p>
        </form>

    <?php elseif (isset($_GET['dashboard']) && isset($_SESSION['admin'])): ?>
        <h2>Rezervasyon Yönetimi</h2>

        <!-- Filtreleme Formu -->
        <form method="GET" class="row g-3 mb-3 justify-content-center">
            <div class="col-md-4 col-sm-6">
                <select name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    <option value="Onaylandı">Onaylandı</option>
                    <option value="Beklemede">Beklemede</option>
                    <option value="Reddedildi">Reddedildi</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </div>
        </form>

        <!-- Tablo: Responsive Yapı -->
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Rezervasyon ID</th>
                        <th>Kullanıcı</th>
                        <th>Araç</th>
                        <th>Başlangıç Tarihi</th>
                        <th>Dönüş Tarihi</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'db.php';  // Veritabanı bağlantısı

                    $status = $_GET['status'] ?? '';  

                    if ($status) {
                        $sql = "SELECT * FROM reservations WHERE status = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$status]);
                    } else {
                        $sql = "SELECT * FROM reservations";
                        $stmt = $conn->query($sql);
                    }

                    while ($row = $stmt->fetch()) {
                        echo "<tr>
                            <td>{$row['reservation_id']}</td>
                            <td>{$row['user_id']}</td>
                            <td>{$row['car_id']}</td>
                            <td>{$row['reservation_date']}</td>
                            <td>{$row['return_date']}</td>
                            <td>{$row['status']}</td>
                            <td>
                                <a href='onayla.php?id={$row['reservation_id']}' class='btn btn-success btn-sm'>Onayla</a>
                                <a href='reddet.php?id={$row['reservation_id']}' class='btn btn-danger btn-sm'>Reddet</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <a href="admin_paneli.php?logout" class="btn btn-danger">Çıkış Yap</a>

    <?php else: ?>
        <a href="admin_paneli.php?login" class="btn btn-primary">Giriş Yap</a>
        <a href="admin_paneli.php?register" class="btn btn-success">Kayıt Ol</a>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
