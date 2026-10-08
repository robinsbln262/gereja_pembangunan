<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'bendahara') {
    header("Location: ../admin/login");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/database.php';
include '../core/functions.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function setFlash($status, $message)
{
    $_SESSION['flash'] = ['status' => $status, 'message' => $message];
}

function getTotalPemasukanGlobal($conn)
{
    $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM donasi WHERE status='verified'");
    if ($query) {
        $row = mysqli_fetch_assoc($query);
        return $row['total'] ?? 0;
    }
    return 0;
}

function getTotalPengeluaranGlobal($conn)
{
    $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran");
    if ($query) {
        $row = mysqli_fetch_assoc($query);
        return $row['total'] ?? 0;
    }
    return 0;
}

$kegiatanList = [];
$queryKegiatan = mysqli_query($conn, "SELECT id_pembangunan, judul FROM pembangunan ORDER BY judul");
while ($row = mysqli_fetch_assoc($queryKegiatan)) {
    $kegiatanList[] = $row;
}

if (isset($_POST['tambah_pengeluaran'])) {
    $tanggal = $_POST['tanggal'];
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = trim($_POST['kategori']);
    $jumlah = (float)$_POST['jumlah'];
    $id_pembangunan = !empty($_POST['id_pembangunan']) ? (int)$_POST['id_pembangunan'] : null;

    $total_masuk = getTotalPemasukanGlobal($conn);
    $total_keluar_sekarang = getTotalPengeluaranGlobal($conn);
    $saldo_sekarang = $total_masuk - $total_keluar_sekarang;

    if ($jumlah > $saldo_sekarang) {
        setFlash('error', 'Pengeluaran gagal! Dana tidak mencukupi. Saldo saat ini: ' . rupiah($saldo_sekarang));
        header("Location: laporan_keuangan");
        exit();
    }

    $bukti = '';
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../uploads/pengeluaran/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $bukti = uploadFile($_FILES['bukti'], $targetDir, ['jpg', 'jpeg', 'png', 'pdf']);
        if (!$bukti) {
            setFlash('error', 'Upload bukti gagal.');
            header("Location: laporan_keuangan");
            exit();
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO pengeluaran (tanggal, deskripsi, kategori, jumlah, bukti, id_pembangunan) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssdsi", $tanggal, $deskripsi, $kategori, $jumlah, $bukti, $id_pembangunan);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Pengeluaran berhasil ditambahkan');
        } else {
            setFlash('error', 'Gagal menambah: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: laporan_keuangan");
    exit();
}

if (isset($_GET['hapus_pengeluaran'])) {
    $id = (int)$_GET['hapus_pengeluaran'];
    $query = mysqli_query($conn, "SELECT bukti FROM pengeluaran WHERE id=$id");
    if ($row = mysqli_fetch_assoc($query)) {
        if (!empty($row['bukti']) && file_exists("../uploads/pengeluaran/" . $row['bukti'])) {
            unlink("../uploads/pengeluaran/" . $row['bukti']);
        }
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM pengeluaran WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    setFlash('success', 'Data pengeluaran dihapus');
    header("Location: laporan_keuangan");
    exit();
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$filter_date = $tahun . '-' . sprintf("%02d", $bulan);

$sql_pemasukan = "SELECT id_donasi as id, created_at as tanggal, nama_donatur as sumber, jumlah, metode_pembayaran as tipe FROM donasi WHERE status='verified' AND DATE_FORMAT(created_at, '%Y-%m') = '$filter_date' ORDER BY created_at DESC";
$pemasukan = mysqli_query($conn, $sql_pemasukan);
if (!$pemasukan) die("Error query pemasukan: " . mysqli_error($conn));

$sql_pengeluaran = "SELECT p.*, pb.judul as nama_kegiatan 
                    FROM pengeluaran p
                    LEFT JOIN pembangunan pb ON p.id_pembangunan = pb.id_pembangunan
                    WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_date' 
                    ORDER BY p.tanggal DESC";
$pengeluaran = mysqli_query($conn, $sql_pengeluaran);
if (!$pengeluaran) die("Error query pengeluaran: " . mysqli_error($conn));

$total_masuk_global = getTotalPemasukanGlobal($conn);
$total_keluar_global = getTotalPengeluaranGlobal($conn);
$saldo_global = $total_masuk_global - $total_keluar_global;
$saldo_tampil = max(0, $saldo_global);
$saldo_minus = ($saldo_global < 0);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_bendahara.php';
include '../includes/sidebar_bendahara.php';
?>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i> Laporan Keuangan</h2>
            <button class="btn btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran"><i class="fas fa-minus-circle me-1"></i> Tambah Pengeluaran</button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select rounded-3">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($bulan == $i) ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $i, 1)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select rounded-3">
                            <?php for ($y = 2024; $y <= 2030; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($tahun == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto"><button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-filter me-1"></i> Filter</button></div>
                    <div class="col-auto"><a href="export_pdf?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" target="_blank" class="btn btn-secondary rounded-pill px-4"><i class="fas fa-file-pdf me-1"></i> Cetak PDF</a></div>
                    <div class="col-auto"><a href="export_excel?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn btn-info rounded-pill px-4"><i class="fas fa-file-excel me-1"></i> Export Excel</a></div>
                </form>
            </div>
        </div>

        <?php if ($flash): ?>
            <script>
                Swal.fire({ icon: '<?php echo $flash['status']; ?>', title: '<?php echo $flash['status'] === 'success' ? 'Berhasil' : 'Gagal'; ?>', text: '<?php echo addslashes($flash['message']); ?>', timer: 2000, showConfirmButton: true });
            </script>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 bg-success text-white"><div class="card-body"><h6>Total Pemasukan</h6><h3><?php echo rupiah($total_masuk_global); ?></h3></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 bg-danger text-white"><div class="card-body"><h6>Total Pengeluaran</h6><h3><?php echo rupiah($total_keluar_global); ?></h3></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 <?php echo ($saldo_tampil > 0 ? 'bg-primary' : 'bg-secondary'); ?> text-white"><div class="card-body"><h6>Sisa Dana</h6><h3><?php echo rupiah($saldo_tampil); ?></h3><?php if ($saldo_minus): ?><small><i class="fas fa-exclamation-triangle me-1"></i> Dana tidak mencukupi</small><?php endif; ?></div></div></div>
        </div>

        <!-- Detail Pemasukan -->
        <div class="accordion mb-4" id="accordionPemasukan">
            <div class="accordion-item border-0 shadow-sm rounded-4">
                <h2 class="accordion-header" id="headingPemasukan">
                    <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePemasukan">
                        <i class="fas fa-donate me-2 text-primary"></i> Detail Pemasukan <span class="badge bg-primary ms-2"><?php echo mysqli_num_rows($pemasukan); ?></span>
                    </button>
                </h2>
                <div id="collapsePemasukan" class="accordion-collapse collapse">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Tanggal</th><th>Donatur</th><th>Metode</th><th>Jumlah</th></tr></thead>
                                <tbody>
                                    <?php while ($d = mysqli_fetch_assoc($pemasukan)): ?>
                                        <tr><td><?php echo date('d/m/Y H:i', strtotime($d['tanggal'])); ?></td><td><?php echo htmlspecialchars($d['sumber']); ?></td><td><?php echo ucfirst($d['tipe']); ?></td><td class="text-success fw-bold"><?php echo rupiah($d['jumlah']); ?></td></tr>
                                    <?php endwhile; if (mysqli_num_rows($pemasukan)==0): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data pemasukan</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Pengeluaran -->
        <div class="accordion" id="accordionPengeluaran">
            <div class="accordion-item border-0 shadow-sm rounded-4">
                <h2 class="accordion-header" id="headingPengeluaran">
                    <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePengeluaran">
                        <i class="fas fa-hand-holding-usd me-2 text-danger"></i> Detail Pengeluaran <span class="badge bg-danger ms-2"><?php echo mysqli_num_rows($pengeluaran); ?></span>
                    </button>
                </h2>
                <div id="collapsePengeluaran" class="accordion-collapse collapse">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Kegiatan</th><th>Jumlah</th><th>Bukti</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    <?php while ($k = mysqli_fetch_assoc($pengeluaran)): ?>
                                        <tr><td><?php echo date('d/m/Y', strtotime($k['tanggal'])); ?></td><td><?php echo htmlspecialchars($k['deskripsi']); ?></td><td><?php echo htmlspecialchars($k['kategori']); ?></td><td><?php echo htmlspecialchars($k['nama_kegiatan']??'-'); ?></td><td class="text-danger fw-bold"><?php echo rupiah($k['jumlah']); ?></td>
                                        <td><?php if ($k['bukti']): ?><a href="../uploads/pengeluaran/<?php echo $k['bukti']; ?>" target="_blank" class="btn btn-sm btn-info">Lihat</a><?php else: ?>-<?php endif; ?></td>
                                        <td><button class="btn btn-sm btn-danger rounded-pill btn-hapus-pengeluaran" data-id="<?php echo $k['id']; ?>"><i class="fas fa-trash"></i> Hapus</button></td>
                                    </tr><?php endwhile; if (mysqli_num_rows($pengeluaran)==0): ?>
                                        <tr><td colspan="7" class="text-center text-muted">Tidak ada data pengeluaran</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="modalTambahPengeluaran" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0 pt-4 px-4"><h5 class="fw-bold"><i class="fas fa-minus-circle me-2 text-danger"></i> Tambah Pengeluaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body px-4">
                    <div class="mb-3"><label>Tanggal</label><input type="date" name="tanggal" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label>Deskripsi</label><input type="text" name="deskripsi" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label>Kategori</label><select name="kategori" class="form-select rounded-3"><option>Material</option><option>Upah Pekerja</option><option>Perizinan</option><option>Transportasi</option><option>Lainnya</option></select></div>
                    <div class="mb-3"><label>Jumlah (Rp)</label><input type="number" name="jumlah" class="form-control rounded-3" step="1000" required></div>
                    <div class="mb-3"><label>Kegiatan Terkait</label><select name="id_pembangunan" class="form-select rounded-3"><option value="">-- Tidak terkait --</option><?php foreach($kegiatanList as $kg): ?><option value="<?php echo $kg['id_pembangunan']; ?>"><?php echo htmlspecialchars($kg['judul']); ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label>Bukti</label><input type="file" name="bukti" class="form-control rounded-3" accept="image/*,application/pdf"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_pengeluaran" class="btn btn-danger rounded-pill px-4">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-hapus-pengeluaran').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            Swal.fire({ title: 'Hapus Pengeluaran?', text: "Data akan dihapus permanen!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) window.location.href = '?hapus_pengeluaran=' + id; });
        });
    });
</script>

<?php include '../includes/footer_admin.php'; ob_end_flush(); ?>