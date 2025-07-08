<?php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}

if (isset($_SESSION['id_pengguna'])) {
            header('Location: index.php');
            exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                        $error = 'Username dan password wajib diisi.';
            } else {
                        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE username = ?");
                        $stmt->execute([$username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($user && password_verify($password, $user['password'])) {
                                    $_SESSION['id_pengguna'] = $user['id_pengguna'];
                                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                                    $_SESSION['role'] = $user['role'];

                                    header('Location: index.php');
                                    exit();
                        } else {
                                    $error = 'Username atau password salah.';
                        }
            }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Dashboard KPI</title>
            <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
            <style>
                        body {
                                    background-color: #f8f9fa;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    height: 100vh;
                        }

                        .login-card {
                                    max-width: 400px;
                                    width: 100%;
                        }
            </style>
</head>

<body>
            <div class="card login-card shadow-sm">
                        <div class="card-body">
                                    <h3 class="card-title text-center mb-4">Login Dashboard</h3>
                                    <?php if ($error): ?>
                                                <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST">
                                                <div class="mb-3">
                                                            <label for="username" class="form-label">Username</label>
                                                            <input type="text" class="form-control" id="username" name="username" required>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="password" class="form-label">Password</label>
                                                            <input type="password" class="form-control" id="password" name="password" required>
                                                </div>
                                                <div class="d-grid">
                                                            <button type="submit" class="btn btn-primary">Login</button>
                                                </div>
                                    </form>
                        </div>
            </div>
            <script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>

</html>