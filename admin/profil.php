<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

include '../config/database.php';
include '../core/functions.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Helper flash message
function setFlash($status, $message) {
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

// Ambil data admin saat ini
$admin_id = $_SESSION['admin_id'];
$query = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = $admin_id");
$admin = mysqli_fetch_assoc($query);

// Proses update profil
if (isset($_POST['update_profil'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    
    $stmt = mysqli_prepare($conn, "UPDATE admin SET nama_lengkap=?, email=? WHERE id_admin=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $nama_lengkap, $email, $admin_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['admin_name'] = $nama_lengkap;
            setFlash('success', 'Profil berhasil diperbarui');
        } else {
            setFlash('error', 'Gagal memperbarui profil: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: profil.php");
    exit();
}

// Proses ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = md5(trim($_POST['password_lama']));
    $password_baru = md5(trim($_POST['password_baru']));
    $konfirmasi = md5(trim($_POST['konfirmasi']));
    
    // Cek password lama
    $check = mysqli_query($conn, "SELECT password FROM admin WHERE id_admin=$admin_id");
    $row = mysqli_fetch_assoc($check);
    if ($row['password'] != $password_lama) {
        setFlash('error', 'Password lama salah');
    } elseif ($password_baru != $konfirmasi) {
        setFlash('error', 'Password baru dan konfirmasi tidak cocok');
    } elseif (strlen(trim($_POST['password_baru'])) < 4) {
        setFlash('error', 'Password minimal 4 karakter');
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE admin SET password=? WHERE id_admin=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $password_baru, $admin_id);
            if (mysqli_stmt_execute($stmt)) {
                setFlash('success', 'Password berhasil diubah');
            } else {
                setFlash('error', 'Gagal mengubah password: ' . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            setFlash('error', 'Prepare statement gagal');
        }
    }
    header("Location: profil.php");
    exit();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-user-circle me-2 text-primary"></i> Profil Admin</h2>
        </div>

        <?php if ($flash): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $flash['status']; ?>',
                title: '<?php echo $flash['status'] === 'success' ? 'Berhasil' : 'Gagal'; ?>',
                text: '<?php echo addslashes($flash['message']); ?>',
                timer: 2000,
                showConfirmButton: true
            });
        </script>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Edit Profil -->
            <div class="col-md-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-edit me-2 text-primary"></i> Edit Profil</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control rounded-3" 
                                       value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control rounded-3" 
                                       value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control rounded-3 bg-light" 
                                       value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <button type="submit" name="update_profil" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Form Ganti Password -->
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-key me-2 text-primary"></i> Ganti Password</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Lama</label>
                                <input type="password" name="password_lama" class="form-control rounded-3" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="password_baru" class="form-control rounded-3" required>
                                <small class="text-muted">Minimal 4 karakter</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi" class="form-control rounded-3" required>
                            </div>
                            <button type="submit" name="ganti_password" class="btn btn-warning rounded-pill px-4">
                                <i class="fas fa-exchange-alt me-2"></i> Ganti Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    .btn {
        transition: all 0.2s;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
</style>

<?php include '../includes/footer_admin.php'; ob_end_flush(); ?>