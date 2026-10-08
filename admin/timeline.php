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
function setFlash($status, $message)
{
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

// Proses Tambah (urutan otomatis)
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_tahap']);
    $deskripsi = trim($_POST['deskripsi']);
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $status = $_POST['status'];

    // Ambil urutan terakhir + 1
    $queryMax = mysqli_query($conn, "SELECT MAX(urutan) as max_urutan FROM timeline_pembangunan");
    $row = mysqli_fetch_assoc($queryMax);
    $urutan = ($row['max_urutan'] ?? 0) + 1;

    $stmt = mysqli_prepare($conn, "INSERT INTO timeline_pembangunan (nama_tahap, deskripsi, tgl_mulai, tgl_selesai, urutan, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssis", $nama, $deskripsi, $tgl_mulai, $tgl_selesai, $urutan, $status);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Tahapan berhasil ditambahkan');
        } else {
            setFlash('error', 'Database error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: timeline.php");
    exit();
}

// Proses Edit (urutan tidak diubah)
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_tahap']);
    $deskripsi = trim($_POST['deskripsi']);
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $status = $_POST['status'];

    $stmt = mysqli_prepare($conn, "UPDATE timeline_pembangunan SET nama_tahap=?, deskripsi=?, tgl_mulai=?, tgl_selesai=?, status=? WHERE id_timeline=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssi", $nama, $deskripsi, $tgl_mulai, $tgl_selesai, $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Tahapan berhasil diupdate');
        } else {
            setFlash('error', 'Update gagal: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: timeline.php");
    exit();
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM timeline_pembangunan WHERE id_timeline=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Tahapan berhasil dihapus');
        } else {
            setFlash('error', 'Gagal hapus: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: timeline.php");
    exit();
}

// Ambil data
$data = mysqli_query($conn, "SELECT * FROM timeline_pembangunan ORDER BY urutan ASC");
if (!$data) {
    $dataError = mysqli_error($conn);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i> Timeline Pembangunan</h2>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-2"></i> Tambah Tahapan
            </button>
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

        <!-- Tabel Data -->
        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Tahap</th>
                                <th>Deskripsi</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dataError)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-danger">Error query: <?php echo htmlspecialchars($dataError); ?></td>
                                </tr>
                            <?php elseif (mysqli_num_rows($data) > 0): ?>
                                <?php $no = 1;
                                while ($row = mysqli_fetch_assoc($data)): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_tahap']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 80)); ?>...</td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_mulai'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_selesai'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    echo $row['status'] == 'selesai' ? 'success' : ($row['status'] == 'berlangsung' ? 'warning' : 'secondary');
                                                                    ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning rounded-pill px-3 me-1 btn-edit"
                                                data-id="<?php echo $row['id_timeline']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama_tahap']); ?>"
                                                data-deskripsi="<?php echo htmlspecialchars($row['deskripsi']); ?>"
                                                data-mulai="<?php echo $row['tgl_mulai']; ?>"
                                                data-selesai="<?php echo $row['tgl_selesai']; ?>"
                                                data-status="<?php echo $row['status']; ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger rounded-pill px-3 btn-hapus" data-id="<?php echo $row['id_timeline']; ?>">
                                                <i class="fas fa-trash me-1"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">Belum ada data timeline. Silakan tambah tahapan pertama.<?php echo $dataError; ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Tahapan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Tahap</label><input type="text" name="nama_tahap" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="deskripsi" class="form-control rounded-3" rows="3"></textarea></div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="form-control rounded-3" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="form-control rounded-3" required></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="direncanakan">Direncanakan</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <small class="text-muted">Urutan akan diatur otomatis.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Edit Tahapan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Tahap</label><input type="text" name="nama_tahap" id="edit_nama" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="form-control rounded-3" rows="3"></textarea></div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" name="tgl_mulai" id="edit_mulai" class="form-control rounded-3" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Selesai</label><input type="date" name="tgl_selesai" id="edit_selesai" class="form-control rounded-3" required></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="edit_status" class="form-select rounded-3">
                            <option value="direncanakan">Direncanakan</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary rounded-pill px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
            document.getElementById('edit_mulai').value = this.dataset.mulai;
            document.getElementById('edit_selesai').value = this.dataset.selesai;
            document.getElementById('edit_status').value = this.dataset.status;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-trash me-2"></i> Ya, hapus!',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?hapus=' + id;
                }
            });
        });
    });
</script>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
        transition: 0.2s;
    }

    .btn {
        transition: all 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .modal-content {
        border: none;
    }
</style>

<?php include '../includes/footer_admin.php';
ob_end_flush(); ?>