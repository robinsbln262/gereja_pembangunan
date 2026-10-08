<?php
ob_start(); // START OUTPUT BUFFERING

// ========== PEMROSESAN FORM ==========
include 'config/database.php';
include 'config/session.php';
include 'core/functions.php';

// Proses donasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donasi'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $jumlah = (float)$_POST['jumlah'];
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    // ========== VALIDASI DATA KESELURUHAN (CEK DUPLIKAT) ==========
    $errorValidasi = false;

    // 1. Cek donasi dengan data identik (nama, email, telepon) dalam 24 jam terakhir
    $stmt = mysqli_prepare($conn, "SELECT id_donasi FROM donasi WHERE nama_donatur = ? AND email = ? AND no_telepon = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $telepon);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        setFlash('error', 'Maaf, Anda sudah melakukan donasi dalam 24 jam terakhir. Silakan tunggu sebelum donasi lagi.');
        $errorValidasi = true;
    }
    mysqli_stmt_close($stmt);
    if ($errorValidasi) {
        header("Location: donasi.php");
        ob_end_flush();
        exit();
    }

    // 2. Cek donasi dengan email yang sama dalam 7 hari terakhir (jika ingin lebih ketat)
    $stmt = mysqli_prepare($conn, "SELECT id_donasi FROM donasi WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        setFlash('error', 'Email ini sudah digunakan untuk donasi dalam 1 minggu terakhir. Harap gunakan email lain atau hubungi panitia.');
        $errorValidasi = true;
    }
    mysqli_stmt_close($stmt);
    if ($errorValidasi) {
        header("Location: donasi.php");
        ob_end_flush();
        exit();
    }

    // 3. Cek duplikat file bukti transfer (nama file)
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $buktiNama = $_FILES['bukti']['name'];
        $stmt = mysqli_prepare($conn, "SELECT id_donasi FROM donasi WHERE bukti_transfer = ?");
        mysqli_stmt_bind_param($stmt, "s", $buktiNama);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            setFlash('error', 'File bukti transfer sudah digunakan sebelumnya. Silakan gunakan file lain.');
            $errorValidasi = true;
        }
        mysqli_stmt_close($stmt);
        if ($errorValidasi) {
            header("Location: donasi.php");
            ob_end_flush();
            exit();
        }
    }

    // ========== PROSES UPLOAD BUKTI ==========
    $bukti = '';
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $targetDir = 'uploads/bukti_donasi/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $bukti = uploadFile($_FILES['bukti'], $targetDir, ['jpg', 'jpeg', 'png', 'pdf']);
        if (!$bukti) {
            setFlash('error', 'Upload bukti gagal. Format harus JPG/PNG/PDF.');
            header("Location: donasi.php");
            ob_end_flush();
            exit();
        }
    }

    // ========== SIMPAN KE DATABASE ==========
    $query = "INSERT INTO donasi (nama_donatur, email, no_telepon, jumlah, bukti_transfer, pesan, status) 
              VALUES ('$nama', '$email', '$telepon', '$jumlah', '$bukti', '$pesan', 'pending')";
    if (mysqli_query($conn, $query)) {
        setFlash('success', 'Terima kasih! Donasi Anda akan segera diverifikasi.');
    } else {
        setFlash('error', 'Gagal menyimpan donasi. Silakan coba lagi.');
    }
    header("Location: donasi.php");
    ob_end_flush();
    exit();
}

$flashSuccess = getFlash('success');
$flashError = getFlash('error');

// ========== TAMPILAN HTML ==========
include 'includes/navbar_user.php';
?>

<div class="container mt-5" data-aos="fade-up">
    <div class="row justify-content-center">
        <!-- Kolom Tengah -->
        <div class="col-md-8">
            <!-- Card Informasi Rekening -->
            <div class="card border-0 shadow-lg rounded-4 mb-4">
                <div class="card-header bg-primary text-white text-center py-2">
                    <i class="fas fa-university me-2"></i> Informasi Rekening Donasi
                </div>
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-1">Bank Syariah Indonesia (BSI)</h5>
                            <p class="mb-0">No. Rekening: <strong>7264424989</strong></p>
                            <p class="mb-0">a.n. <strong>Panitia Pembangunan</strong></p>
                            <small class="text-muted">Setelah transfer, upload bukti transfer pada form di bawah.</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-credit-card fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Form Donasi -->
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-white border-0 pt-3 text-center">
                    <i class="fas fa-hand-holding-heart fa-2x text-primary"></i>
                    <h4 class="fw-bold mt-1">Form Donasi</h4>
                    <p class="small">Isi data berikut untuk berdonasi. Kami akan menghubungi Anda.</p>
                </div>
                <div class="card-body p-3">
                    <?php if ($flashSuccess): ?>
                        <div class="alert alert-success small"><?php echo $flashSuccess; ?></div>
                    <?php elseif ($flashError): ?>
                        <div class="alert alert-danger small"><?php echo $flashError; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold small">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold small">Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold small">No. Telepon</label>
                                <input type="text" name="telepon" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold small">Jumlah Donasi (Rp)</label>
                                <input type="number" name="jumlah" class="form-control form-control-sm" min="10000" step="10000" required>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold small">Bukti Transfer</label>
                                <input type="file" name="bukti" class="form-control form-control-sm" accept="image/*,application/pdf">
                                <small class="text-muted">Upload screenshot/foto bukti transfer (disarankan).</small>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold small">Pesan / Doa (Opsional)</label>
                                <textarea name="pesan" rows="2" class="form-control form-control-sm" placeholder="Tuliskan doa atau pesan Anda..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="donasi" class="btn btn-donasi w-100 py-1">Kirim Donasi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';
ob_end_flush(); ?>