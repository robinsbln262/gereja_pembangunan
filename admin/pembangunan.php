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

function setFlash($status, $message)
{
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

// Progres dihitung dari realisasi pengeluaran
function hitungProgresDariRealisasi($total_realisasi, $kebutuhan_dana)
{
    if ($kebutuhan_dana <= 0) return 0;
    $progres = ($total_realisasi / $kebutuhan_dana) * 100;
    $progres = round($progres, 2);
    return min($progres, 100);
}

// ========== PROSES TAMBAH ==========
if (isset($_POST['tambah'])) {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kebutuhan_dana = !empty($_POST['kebutuhan_dana']) ? (float) str_replace(',', '.', $_POST['kebutuhan_dana']) : 0;
    $tgl_mulai = $_POST['tgl_mulai'] ?? null;
    $tgl_selesai = $_POST['tgl_selesai'] ?? null;
    $status = $_POST['status'] ?? 'direncanakan';
    $urutan = (int)$_POST['urutan'];

    // Cek duplikasi urutan
    $cek = mysqli_query($conn, "SELECT id_pembangunan FROM pembangunan WHERE urutan = $urutan");
    if (mysqli_num_rows($cek) > 0) {
        setFlash('error', 'Urutan sudah digunakan! Silakan gunakan urutan lain.');
        header("Location: pembangunan.php");
        exit();
    }

    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../uploads/pembangunan/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $gambar = uploadFile($_FILES['gambar'], $targetDir);
        if (!$gambar) {
            setFlash('error', 'Gagal upload gambar.');
            header("Location: pembangunan.php");
            exit();
        }
    }

    // Insert (dana_terpakai awal = 0)
    $stmt = mysqli_prepare($conn, "INSERT INTO pembangunan (judul, deskripsi, gambar, kebutuhan_dana, dana_terpakai, tgl_mulai, tgl_selesai, status, urutan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $dana_terpakai_temp = 0;
        mysqli_stmt_bind_param($stmt, "sssdssssi", $judul, $deskripsi, $gambar, $kebutuhan_dana, $dana_terpakai_temp, $tgl_mulai, $tgl_selesai, $status, $urutan);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Data berhasil ditambahkan');
        } else {
            setFlash('error', 'Gagal menambah: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: pembangunan.php");
    exit();
}

// ========== PROSES EDIT ==========
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kebutuhan_dana = !empty($_POST['kebutuhan_dana']) ? (float) str_replace(',', '.', $_POST['kebutuhan_dana']) : 0;
    $tgl_mulai = $_POST['tgl_mulai'] ?? null;
    $tgl_selesai = $_POST['tgl_selesai'] ?? null;
    $status = $_POST['status'] ?? 'direncanakan';
    $urutan = (int)$_POST['urutan'];

    // Cek duplikasi urutan (kecuali dirinya sendiri)
    $cek = mysqli_query($conn, "SELECT id_pembangunan FROM pembangunan WHERE urutan = $urutan AND id_pembangunan != $id");
    if (mysqli_num_rows($cek) > 0) {
        setFlash('error', 'Urutan sudah digunakan!');
        header("Location: pembangunan.php");
        exit();
    }

    $gambar = null;
    $isUpload = (isset($_FILES['gambar_edit']) && $_FILES['gambar_edit']['error'] === UPLOAD_ERR_OK);
    if ($isUpload) {
        $targetDir = '../uploads/pembangunan/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $gambar = uploadFile($_FILES['gambar_edit'], $targetDir);
        if (!$gambar) {
            setFlash('error', 'Gagal upload gambar baru');
            header("Location: pembangunan.php");
            exit();
        }
    }

    if ($gambar) {
        $stmt = mysqli_prepare($conn, "UPDATE pembangunan SET judul=?, deskripsi=?, gambar=?, kebutuhan_dana=?, tgl_mulai=?, tgl_selesai=?, status=?, urutan=? WHERE id_pembangunan=?");
        mysqli_stmt_bind_param($stmt, "sssdsssii", $judul, $deskripsi, $gambar, $kebutuhan_dana, $tgl_mulai, $tgl_selesai, $status, $urutan, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE pembangunan SET judul=?, deskripsi=?, kebutuhan_dana=?, tgl_mulai=?, tgl_selesai=?, status=?, urutan=? WHERE id_pembangunan=?");
        mysqli_stmt_bind_param($stmt, "ssdsssii", $judul, $deskripsi, $kebutuhan_dana, $tgl_mulai, $tgl_selesai, $status, $urutan, $id);
    }
    if ($stmt && mysqli_stmt_execute($stmt)) {
        setFlash('success', 'Data berhasil diupdate');
    } else {
        setFlash('error', 'Update gagal');
    }
    if ($stmt) mysqli_stmt_close($stmt);
    header("Location: pembangunan.php");
    exit();
}

// ========== HAPUS ==========
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $queryGambar = mysqli_query($conn, "SELECT gambar FROM pembangunan WHERE id_pembangunan=$id");
    if ($row = mysqli_fetch_assoc($queryGambar)) {
        if (!empty($row['gambar']) && file_exists("../uploads/pembangunan/" . $row['gambar'])) {
            unlink("../uploads/pembangunan/" . $row['gambar']);
        }
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM pembangunan WHERE id_pembangunan=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    setFlash('success', 'Data dihapus');
    header("Location: pembangunan.php");
    exit();
}

// ========== DATA UNTUK TABEL ==========
$data = mysqli_query($conn, "SELECT * FROM pembangunan ORDER BY urutan ASC");
if (!$data) $dataError = mysqli_error($conn);

$ringkasan = getRingkasanKeuangan($conn);
$totalMasuk = $ringkasan['total_masuk'];
$totalKebutuhan = $ringkasan['total_kebutuhan'];
$totalTerpakai = $ringkasan['total_terpakai'];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';
?>
<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-building me-2 text-primary"></i> Data Pembangunan & Timeline</h2>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-2"></i> Tambah Tahap
            </button>
        </div>
        <?php if ($flash): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $flash['status']; ?>',
                    title: '<?php echo $flash['status'] === 'success' ? 'Berhasil' : 'Gagal'; ?>',
                    text: '<?php echo addslashes($flash['message']); ?>',
                    timer: 2000
                });
            </script>
        <?php endif; ?>
        <!-- Ringkasan Keuangan -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
                    <div class="card-body">
                        <h6>Total Donasi Masuk</h6>
                        <h3><?php echo rupiah($totalMasuk); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-info text-white">
                    <div class="card-body">
                        <h6>Total Kebutuhan Dana</h6>
                        <h3><?php echo rupiah($totalKebutuhan); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark">
                    <div class="card-body">
                        <h6>Total Dana Terpakai (riil)</h6>
                        <h3><?php echo rupiah($totalTerpakai); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Kegiatan + Timeline -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Progres</th>
                                <th>Kebutuhan</th>
                                <th>Dana Terpakai (rencana)</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dataError)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-danger">Error: <?php echo htmlspecialchars($dataError); ?>ERB</td>
                                </tr>
                                <?php elseif (mysqli_num_rows($data) > 0): $no = 1;
                                while ($row = mysqli_fetch_assoc($data)):
                                    $id_kegiatan = $row['id_pembangunan'];
                                    // Hitung realisasi pengeluaran
                                    $queryRealisasi = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_pembangunan = $id_kegiatan");
                                    $totalRealisasi = 0;
                                    if ($queryRealisasi && $res = mysqli_fetch_assoc($queryRealisasi)) {
                                        $totalRealisasi = $res['total'] ?? 0;
                                    }
                                    $kebutuhan = $row['kebutuhan_dana'] ?? 0;
                                    $progres = hitungProgresDariRealisasi($totalRealisasi, $kebutuhan);
                                    $progres_tampil = number_format($progres, 2);
                                    $lebar_bar = $progres;
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-info" style="width: <?php echo $lebar_bar; ?>%;"></div>
                                                </div>
                                                <span class="small fw-semibold"><?php echo $progres_tampil; ?>%</span>
                                            </div>
                                        </td>
                                        <td><?php echo rupiah($kebutuhan); ?></td>
                                        <td><?php echo rupiah($totalRealisasi); ?></td>
                                        <td><?php echo $row['tgl_mulai'] ? date('d/m/Y', strtotime($row['tgl_mulai'])) : '-'; ?></td>
                                        <td><?php echo $row['tgl_selesai'] ? date('d/m/Y', strtotime($row['tgl_selesai'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'selesai' ? 'success' : ($row['status'] == 'berlangsung' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning rounded-pill px-3 me-1 btn-edit"
                                                data-id="<?php echo $row['id_pembangunan']; ?>"
                                                data-judul="<?php echo htmlspecialchars($row['judul']); ?>"
                                                data-deskripsi="<?php echo htmlspecialchars($row['deskripsi']); ?>"
                                                data-kebutuhan="<?php echo $kebutuhan; ?>"
                                                data-mulai="<?php echo $row['tgl_mulai']; ?>"
                                                data-selesai="<?php echo $row['tgl_selesai']; ?>"
                                                data-status="<?php echo $row['status']; ?>"
                                                data-urutan="<?php echo $row['urutan']; ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger rounded-pill px-3 btn-hapus" data-id="<?php echo $row['id_pembangunan']; ?>">
                                                <i class="fas fa-trash me-1"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">Belum ada data. Tambah tahap pertama.ERB</td>
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
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Tahap</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3"><label class="form-label fw-semibold">Judul / Nama Tahap</label><input type="text" name="judul" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="deskripsi" class="form-control rounded-3" rows="3"></textarea></div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" name="tgl_mulai" class="form-control rounded-3"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Selesai</label><input type="date" name="tgl_selesai" class="form-control rounded-3"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Kebutuhan Dana (Rp)</label><input type="text" name="kebutuhan_dana" class="form-control rounded-3" value="0" placeholder="Contoh: 50000000"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Urutan Tampil</label><input type="number" name="urutan" class="form-control rounded-3" value="1" required></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="direncanakan">Direncanakan</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Gambar</label><input type="file" name="gambar" class="form-control rounded-3" accept="image/*"></div>
                    <small class="text-muted">* Dana terpakai akan dihitung otomatis dari pengeluaran yang terkait.</small>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah" class="btn btn-primary rounded-pill px-4">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Edit Tahap</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3"><label class="form-label fw-semibold">Judul</label><input type="text" name="judul" id="edit_judul" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="form-control rounded-3" rows="3"></textarea></div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" name="tgl_mulai" id="edit_mulai" class="form-control rounded-3"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Selesai</label><input type="date" name="tgl_selesai" id="edit_selesai" class="form-control rounded-3"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Kebutuhan Dana (Rp)</label><input type="text" name="kebutuhan_dana" id="edit_kebutuhan" class="form-control rounded-3" step="any"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Urutan Tampil</label><input type="number" name="urutan" id="edit_urutan" class="form-control rounded-3" required></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="edit_status" class="form-select rounded-3">
                            <option value="direncanakan">Direncanakan</option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Gambar Baru (opsional)</label><input type="file" name="gambar_edit" class="form-control rounded-3" accept="image/*"><small class="text-muted">Kosongkan jika tidak ingin mengganti</small></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button><button type="submit" name="edit" class="btn btn-primary rounded-pill px-4">Update</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function parseAngka(value) {
        if (!value) return 0;
        value = value.toString().replace(/,/g, '.');
        let num = parseFloat(value);
        return isNaN(num) ? 0 : num;
    }

    // Isi modal edit
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_judul').value = this.dataset.judul;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
            document.getElementById('edit_kebutuhan').value = parseAngka(this.dataset.kebutuhan);
            document.getElementById('edit_mulai').value = this.dataset.mulai;
            document.getElementById('edit_selesai').value = this.dataset.selesai;
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_urutan').value = this.dataset.urutan;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        });
    });

    // Hapus confirm
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            Swal.fire({
                title: 'Yakin?',
                text: "Data tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = '?hapus=' + id;
            });
        });
    });
</script>

<style>
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.4s ease;
        border-radius: 10px;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
    }

    .btn:hover {
        transform: translateY(-2px);
    }
</style>

<?php include '../includes/footer_admin.php';
ob_end_flush(); ?>