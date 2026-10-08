<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    if ($_SESSION['admin_role'] == 'bendahara') {
        header("Location: ../bendahara/dashboard");
    } else {
        header("Location: dashboard");
    }
    exit();
}

include '../config/database.php';

$maxAttempts = 5;
$lockoutTime = 15 * 60;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');

    if ($_SESSION['login_attempts'] >= $maxAttempts && (time() - $_SESSION['last_attempt_time']) < $lockoutTime) {
        $remaining = ceil(($lockoutTime - (time() - $_SESSION['last_attempt_time'])) / 60);
        echo json_encode(['status' => 'error', 'message' => "Terlalu banyak percobaan. Coba lagi $remaining menit."]);
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Username dan password wajib diisi']);
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT id_admin, nama_lengkap, password, role FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (md5($password) === $row['password']) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['id_admin'];
            $_SESSION['admin_name'] = $row['nama_lengkap'];
            $_SESSION['admin_role'] = $row['role'];

            if ($row['role'] == 'bendahara') {
                echo json_encode(['status' => 'success', 'redirect' => '../bendahara/dashboard']);
            } else {
                echo json_encode(['status' => 'success', 'redirect' => 'dashboard']);
            }
            exit();
        } else {
            $_SESSION['login_attempts']++;
            echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
            exit();
        }
    } else {
        $_SESSION['login_attempts']++;
        echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gereja St. Laurensius</title>
    <link rel="icon" type="image/png" href="../uploads/informasi/logo-st-laurensius.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255,255,255,0.95);
            border-radius: 32px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .login-card:hover { transform: translateY(-5px); }
        .icon-church {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            width: 80px; height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .icon-church i { font-size: 40px; color: white; }
        .btn-login {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 40px;
            padding: 12px 20px;
            font-weight: 600;
            border: none;
        }
        .btn-login:hover { transform: scale(1.02); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .form-control { border-radius: 12px; padding: 12px 16px; }
        .input-icon { position: relative; }
        .input-icon i:first-child {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
        }
        .input-icon .form-control { padding-left: 42px; }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="login-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-church mx-auto"><i class="fas fa-church"></i></div>
                    <h3 class="mt-3 fw-bold">Login Panel</h3>
                    <p class="text-muted">Sistem Informasi Pembangunan Gereja</p>
                </div>
                <form id="loginForm">
                    <div class="mb-4 input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required autofocus>
                    </div>
                    <div class="mb-4 input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                    </div>
                    <button type="submit" id="loginBtn" class="btn btn-login w-100"><i class="fas fa-arrow-right-to-bracket me-2"></i> Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
        this.classList.toggle('fa-eye');
    });
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('loginBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('username', document.getElementById('username').value.trim());
        formData.append('password', passwordInput.value);

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil Login!', text: 'Mengarahkan...', timer: 1500, showConfirmButton: false })
                    .then(() => { window.location.href = result.redirect; });
            } else {
                Swal.fire({ icon: 'error', title: 'Login Gagal', text: result.message, confirmButtonColor: '#d33' });
                btn.innerHTML = originalText;
                btn.disabled = false;
                passwordInput.value = '';
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal terhubung ke server.', confirmButtonColor: '#d33' });
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
</script>
</body>
</html>