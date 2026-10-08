<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'bendahara') {
    header("Location: ../admin/login");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

include '../config/database.php';
include '../core/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function setFlash($status, $message)
{
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

// ========== FUNGSI KIRIM EMAIL ==========
function kirimEmailNotifikasi($email, $nama, $status, $jumlah, $alasan = '')
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pp.sls.selayang@gmail.com';
        $mail->Password   = 'raqa crtw ilul glyc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('pp.sls.selayang@gmail.com', 'Panitia Pembangunan Gereja St. Laurensius');
        $mail->addAddress($email, $nama);
        $mail->isHTML(true);

        if ($status == 'verified') {
            $mail->Subject = 'Donasi Anda Telah Diverifikasi ✅';
            $mail->Body    = "
                <html>
                <body>
                    <h3>Yth. {$nama},</h3>
                    <p>Donasi Anda sebesar <strong>Rp " . number_format($jumlah, 0, ',', '.') . "</strong> telah <span style='color:green'>diverifikasi</span> oleh panitia.</p>
                    <p>Terima kasih atas partisipasi Anda dalam pembangunan Gereja St. Laurensius. Tuhan memberkati.</p>
                    <hr><small>Sistem Informasi Pembangunan Gereja</small>
                </body>
                </html>
            ";
        } else {
            // Untuk status pending yang ditolak/tidak valid
            $mail->Subject = 'Perbaikan Data Donasi - Gereja St. Laurensius';
            $mail->Body    = "
                <html>
                <body>
                    <h3>Yth. {$nama},</h3>
                    <p>Donasi Anda sebesar <strong>Rp " . number_format($jumlah, 0, ',', '.') . "</strong> <span style='color:orange'>belum dapat diverifikasi</span> karena:</p>
                    <p><em>{$alasan}</em></p>
                    <p>Silakan lengkapi atau perbaiki data donasi Anda, atau hubungi panitia untuk informasi lebih lanjut.</p>
                    <p>Donasi Anda tetap tercatat dalam sistem kami.</p>
                    <hr><small>Sistem Informasi Pembangunan Gereja</small>
                </body>
                </html>
            ";
        }
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ========== VERIFIKASI ==========
if (isset($_GET['verify'])) {
    $id = (int)$_GET['verify'];
    $query = mysqli_query($conn, "SELECT email, nama_donatur, jumlah FROM donasi WHERE id_donasi = $id");
    $donatur = mysqli_fetch_assoc($query);

    $stmt = mysqli_prepare($conn, "UPDATE donasi SET status='verified', alasan_ditolak=NULL WHERE id_donasi=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $emailStatus = '';
            if ($donatur && !empty($donatur['email'])) {
                if (kirimEmailNotifikasi($donatur['email'], $donatur['nama_donatur'], 'verified', $donatur['jumlah'])) {
                    $emailStatus = ' & email notifikasi terkirim';
                } else {
                    $emailStatus = ' (email gagal terkirim)';
                }
            }
            setFlash('success', 'Donasi berhasil diverifikasi' . $emailStatus);
        } else {
            setFlash('error', 'Gagal memverifikasi donasi: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: donasi");
    exit();
}

// ========== TOLAK / TANDAI PERLU PERBAIKAN (status tetap PENDING) ==========
if (isset($_POST['reject']) && isset($_POST['id']) && isset($_POST['alasan'])) {
    $id = (int)$_POST['id'];
    $alasan = trim($_POST['alasan']);
    if (empty($alasan)) {
        setFlash('error', 'Alasan harus diisi.');
        header("Location: donasi");
        exit();
    }
    $query = mysqli_query($conn, "SELECT email, nama_donatur, jumlah FROM donasi WHERE id_donasi = $id");
    $donatur = mysqli_fetch_assoc($query);

    // Status tetap 'pending', hanya simpan alasan penolakan
    $stmt = mysqli_prepare($conn, "UPDATE donasi SET status='pending', alasan_ditolak=? WHERE id_donasi=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $alasan, $id);
        if (mysqli_stmt_execute($stmt)) {
            $emailStatus = '';
            if ($donatur && !empty($donatur['email'])) {
                if (kirimEmailNotifikasi($donatur['email'], $donatur['nama_donatur'], 'pending', $donatur['jumlah'], $alasan)) {
                    $emailStatus = ' & email notifikasi terkirim';
                } else {
                    $emailStatus = ' (email gagal terkirim)';
                }
            }
            setFlash('success', 'Donasi ditandai perlu perbaikan' . $emailStatus . '. Status tetap Pending.');
        } else {
            setFlash('error', 'Gagal menyimpan: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: donasi");
    exit();
}

// ========== HAPUS (untuk data yang benar-benar ingin dihapus) ==========
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = mysqli_query($conn, "SELECT bukti_transfer FROM donasi WHERE id_donasi=$id");
    if ($row = mysqli_fetch_assoc($query)) {
        if (!empty($row['bukti_transfer']) && file_exists("../uploads/bukti_donasi/" . $row['bukti_transfer'])) {
            unlink("../uploads/bukti_donasi/" . $row['bukti_transfer']);
        }
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM donasi WHERE id_donasi=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Data donasi berhasil dihapus');
        } else {
            setFlash('error', 'Gagal menghapus donasi: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: donasi");
    exit();
}

// ========== AMBIL DATA ==========
$donasi = mysqli_query($conn, "SELECT * FROM donasi ORDER BY created_at DESC");
if (!$donasi) {
    $dataError = mysqli_error($conn);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_bendahara.php';
include '../includes/sidebar_bendahara.php';
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-2 flex-wrap">
            <h2 class="fw-bold"><i class="fas fa-hand-holding-usd me-2 text-primary"></i> Data Donasi</h2>
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

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Donatur</th>
                                <th>Jumlah</th>
                                <th>Bukti Transfer</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dataError)): ?>
                                <tr><td colspan="7" class="text-center text-danger">Error: <?php echo htmlspecialchars($dataError); ?>ERB</td></tr>
                            <?php elseif (mysqli_num_rows($donasi) > 0): ?>
                                <?php $no = 1;
                                while ($d = mysqli_fetch_assoc($donasi)): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $no++; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($d['nama_donatur']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($d['email']); ?></small>
                                        </td>
                                        <td class="text-success fw-semibold"><?php echo rupiah($d['jumlah']); ?></td>
                                        <td>
                                            <?php if ($d['bukti_transfer']): ?>
                                                <a href="../uploads/bukti_donasi/<?php echo $d['bukti_transfer']; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $d['status'];
                                            $badgeClass = ($status == 'verified') ? 'success' : 'warning';
                                            $icon = ($status == 'verified') ? 'fa-check-circle' : 'fa-clock';
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?> px-3 py-2 rounded-pill">
                                                <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo ucfirst($status); ?>
                                            </span>
                                            <?php if (!empty($d['alasan_ditolak'])): ?>
                                                <br><small class="text-muted" title="<?php echo htmlspecialchars($d['alasan_ditolak']); ?>">
                                                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars(substr($d['alasan_ditolak'], 0, 50)); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><i class="far fa-calendar-alt me-1 text-muted"></i> <?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($d['status'] == 'pending'): ?>
                                                    <button class="btn btn-sm btn-success btn-verify" data-id="<?php echo $d['id_donasi']; ?>">
                                                        <i class="fas fa-check"></i> Verifikasi
                                                    </button>
                                                    <button class="btn btn-sm btn-warning btn-reject-modal" data-id="<?php echo $d['id_donasi']; ?>">
                                                        <i class="fas fa-edit"></i> Perbaiki
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-dark btn-hapus" data-id="<?php echo $d['id_donasi']; ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada data donasi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ALASAN PERBAIKAN (bukan penolakan) -->
<div class="modal fade" id="modalAlasanTolak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="" id="formTolakDonasi">
                <input type="hidden" name="id" id="reject_id">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Perbaikan Data Donasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Perbaikan</label>
                        <select name="alasan_select" id="alasan_select" class="form-select rounded-3" required>
                            <option value="">-- Pilih alasan --</option>
                            <option value="Bukti transfer tidak jelas / tidak terbaca">Bukti transfer tidak jelas / tidak terbaca</option>
                            <option value="Jumlah donasi tidak sesuai dengan bukti transfer">Jumlah donasi tidak sesuai dengan bukti transfer</option>
                            <option value="Data donatur tidak lengkap atau tidak valid">Data donatur tidak lengkap atau tidak valid</option>
                            <option value="Donasi double / duplikat">Donasi double / duplikat</option>
                            <option value="Nomor rekening tujuan tidak sesuai">Nomor rekening tujuan tidak sesuai</option>
                            <option value="Melanggar ketentuan donasi">Melanggar ketentuan donasi</option>
                            <option value="Donasi tidak melalui prosedur yang benar">Donasi tidak melalui prosedur yang benar</option>
                            <option value="Lainnya">Lainnya (isi di kotak teks)</option>
                        </select>
                        <div id="alasan_lainnya_container" style="display:none; margin-top:8px;">
                            <label class="form-label fw-semibold">Alasan Lainnya</label>
                            <textarea name="alasan_lainnya" class="form-control rounded-3" rows="2" placeholder="Tulis alasan perbaikan..."></textarea>
                        </div>
                        <small class="text-muted">Donasi akan tetap tercatat dengan status Pending. Donatur akan dihubungi via email.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reject" class="btn btn-warning rounded-pill px-4">Simpan & Notifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.body.addEventListener('click', function(e) {
        const btnHapus = e.target.closest('.btn-hapus');
        if (btnHapus) {
            e.preventDefault();
            const id = btnHapus.getAttribute('data-id');
            if (id) {
                Swal.fire({
                    title: 'Hapus Donasi',
                    text: "Data donasi akan dihapus permanen. Lanjutkan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '<i class="fas fa-trash me-2"></i> Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = '?hapus=' + id;
                });
            }
            return;
        }

        const btnVerify = e.target.closest('.btn-verify');
        if (btnVerify) {
            e.preventDefault();
            const id = btnVerify.getAttribute('data-id');
            if (id) {
                Swal.fire({
                    title: 'Verifikasi Donasi',
                    text: "Apakah Anda yakin ingin memverifikasi donasi ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fas fa-check me-2"></i> Ya, Verifikasi',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = '?verify=' + id;
                });
            }
            return;
        }

        const btnTolak = e.target.closest('.btn-reject-modal');
        if (btnTolak) {
            e.preventDefault();
            const id = btnTolak.getAttribute('data-id');
            if (id) {
                document.getElementById('reject_id').value = id;
                const select = document.getElementById('alasan_select');
                select.value = '';
                document.getElementById('alasan_lainnya_container').style.display = 'none';
                document.querySelector('textarea[name="alasan_lainnya"]').value = '';
                const oldHidden = document.querySelector('#formTolakDonasi input[name="alasan"]');
                if (oldHidden) oldHidden.remove();
                new bootstrap.Modal(document.getElementById('modalAlasanTolak')).show();
            }
            return;
        }
    });

    const alasanSelect = document.getElementById('alasan_select');
    const alasanLainnyaDiv = document.getElementById('alasan_lainnya_container');
    if (alasanSelect) {
        alasanSelect.addEventListener('change', function() {
            alasanLainnyaDiv.style.display = this.value === 'Lainnya' ? 'block' : 'none';
        });
    }

    const formTolak = document.getElementById('formTolakDonasi');
    if (formTolak) {
        formTolak.addEventListener('submit', function(e) {
            const selectVal = alasanSelect.value;
            const alasanLainnya = document.querySelector('textarea[name="alasan_lainnya"]').value.trim();
            let finalAlasan = '';
            if (selectVal === 'Lainnya') {
                if (alasanLainnya === '') {
                    e.preventDefault();
                    Swal.fire('Error', 'Silakan isi alasan perbaikan.', 'error');
                    return false;
                }
                finalAlasan = alasanLainnya;
            } else if (selectVal !== '') {
                finalAlasan = selectVal;
            } else {
                e.preventDefault();
                Swal.fire('Error', 'Pilih alasan perbaikan.', 'error');
                return false;
            }
            const oldHidden = this.querySelector('input[name="alasan"]');
            if (oldHidden) oldHidden.remove();
            let hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'alasan';
            hiddenInput.value = finalAlasan;
            this.appendChild(hiddenInput);
        });
    }

    document.getElementById('modalAlasanTolak').addEventListener('hidden.bs.modal', function() {
        document.getElementById('reject_id').value = '';
        alasanSelect.value = '';
        alasanLainnyaDiv.style.display = 'none';
        document.querySelector('textarea[name="alasan_lainnya"]').value = '';
    });
</script>

<style>
    .table-responsive { overflow-x: auto; }
    .table th, .table td { vertical-align: middle; white-space: nowrap; }
    .table td:first-child, .table th:first-child { white-space: normal; }
    .btn-sm { margin: 2px; }
    @media (max-width: 768px) {
        .table th, .table td { font-size: 0.85rem; }
        .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.7rem; }
    }
</style>

<?php include '../includes/footer_admin.php'; ob_end_flush(); ?>