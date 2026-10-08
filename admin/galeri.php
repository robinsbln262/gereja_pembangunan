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

// ========== HANDLER AJAX TAMBAH FOTO KE GRUP ==========
if (isset($_POST['tambah_ke_grup'])) {
    // Bersihkan semua buffer output
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    error_reporting(0);
    ini_set('display_errors', 0);

    $judul = trim($_POST['judul_grup'] ?? '');
    $tipe = $_POST['tipe_grup'] ?? 'foto';
    $keterangan_per_gambar = $_POST['keterangan_per_gambar_grup'] ?? [];
    $files = $_FILES['gambar_grup'] ?? [];

    if (empty($judul)) {
        echo json_encode(['status' => 'error', 'message' => 'Judul grup tidak valid']);
        exit();
    }

    if (empty($files['name'][0])) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang dipilih']);
        exit();
    }

    // Gunakan prepared statement untuk keamanan
    $stmtDesk = mysqli_prepare($conn, "SELECT deskripsi, tipe FROM galeri WHERE judul=? LIMIT 1");
    mysqli_stmt_bind_param($stmtDesk, "s", $judul);
    mysqli_stmt_execute($stmtDesk);
    $resultDesk = mysqli_stmt_get_result($stmtDesk);
    $rowDesk = mysqli_fetch_assoc($resultDesk);
    mysqli_stmt_close($stmtDesk);

    $deskripsi_global = $rowDesk['deskripsi'] ?? '';
    $tipe = $rowDesk['tipe'] ?? $tipe;

    $targetDir = '../uploads/galeri/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $successCount = 0;
    $errorCount = 0;
    $totalFiles = count($files['name']);

    for ($i = 0; $i < $totalFiles; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errorCount++;
            continue;
        }
        $keterangan = trim($keterangan_per_gambar[$i] ?? '');
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        // Tangkap output dari uploadFile (jika ada error yang di-echo)
        ob_start();
        $gambar = uploadFile($file, $targetDir, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $uploadOutput = trim(ob_get_clean());
        if (!empty($uploadOutput)) {
            // Ada output error dari uploadFile
            error_log("Upload file error: $uploadOutput");
            $errorCount++;
            continue;
        }

        if ($gambar) {
            // Dapatkan urutan terakhir untuk grup
            $stmtMax = mysqli_prepare($conn, "SELECT COALESCE(MAX(urutan),0)+1 as next_urut FROM galeri WHERE judul=?");
            mysqli_stmt_bind_param($stmtMax, "s", $judul);
            mysqli_stmt_execute($stmtMax);
            $resultMax = mysqli_stmt_get_result($stmtMax);
            $rowMax = mysqli_fetch_assoc($resultMax);
            mysqli_stmt_close($stmtMax);
            $urutan = $rowMax['next_urut'] ?? 1;

            $stmt = mysqli_prepare($conn, "INSERT INTO galeri (judul, deskripsi, keterangan, gambar, tipe, urutan) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssi", $judul, $deskripsi_global, $keterangan, $gambar, $tipe, $urutan);
                if (mysqli_stmt_execute($stmt)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                mysqli_stmt_close($stmt);
            } else {
                $errorCount++;
            }
        } else {
            $errorCount++;
        }
    }

    if ($successCount > 0) {
        echo json_encode(['status' => 'success', 'message' => "$successCount foto berhasil ditambahkan", 'successCount' => $successCount, 'errorCount' => $errorCount]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan foto. Pastikan file gambar valid.']);
    }
    exit();
}

// ========== TAMBAH MULTIPLE FOTO (Grup Baru) ==========
if (isset($_POST['tambah_multiple'])) {
    $judul = trim($_POST['judul']);
    $deskripsi_global = trim($_POST['deskripsi_global']);
    $tipe = $_POST['tipe'];
    $keterangan_per_gambar = $_POST['keterangan_per_gambar'] ?? [];
    $files = $_FILES['gambar_multiple'] ?? [];

    if (empty($judul)) {
        setFlash('error', 'Judul wajib diisi');
        header("Location: galeri");
        exit();
    }

    $successCount = 0;
    $errorCount = 0;
    $targetDir = '../uploads/galeri/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $totalFiles = count($files['name']);
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errorCount++;
            continue;
        }

        $keterangan = trim($keterangan_per_gambar[$i] ?? '');
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        $gambar = uploadFile($file, $targetDir, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        if ($gambar) {
            $queryMax = mysqli_query($conn, "SELECT COALESCE(MAX(urutan),0)+1 as next_urut FROM galeri WHERE judul='$judul'");
            $rowMax = mysqli_fetch_assoc($queryMax);
            $urutan = $rowMax['next_urut'];
            $stmt = mysqli_prepare($conn, "INSERT INTO galeri (judul, deskripsi, keterangan, gambar, tipe, urutan) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssi", $judul, $deskripsi_global, $keterangan, $gambar, $tipe, $urutan);
                if (mysqli_stmt_execute($stmt)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                mysqli_stmt_close($stmt);
            } else {
                $errorCount++;
            }
        } else {
            $errorCount++;
        }
    }

    if ($successCount > 0) {
        setFlash('success', "$successCount foto berhasil ditambahkan ke grup '$judul'" . ($errorCount > 0 ? ", $errorCount gagal." : ""));
    } else {
        setFlash('error', "Gagal menambahkan foto. Pastikan file valid dan format gambar didukung.");
    }
    header("Location: galeri");
    exit();
}

// ========== UPDATE GRUP (JUDUL, DESKRIPSI, TIPE) ==========
if (isset($_POST['edit_grup'])) {
    $judul_lama = trim($_POST['judul_lama']);
    $judul_baru = trim($_POST['judul_baru']);
    $deskripsi_baru = trim($_POST['deskripsi_baru']);
    $tipe_baru = $_POST['tipe_baru'];

    if (empty($judul_lama) || empty($judul_baru)) {
        setFlash('error', 'Judul tidak boleh kosong');
        header("Location: galeri");
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE galeri SET judul=?, deskripsi=?, tipe=? WHERE judul=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $judul_baru, $deskripsi_baru, $tipe_baru, $judul_lama);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', "Grup '$judul_lama' berhasil diupdate menjadi '$judul_baru'");
        } else {
            setFlash('error', 'Gagal update grup: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('error', 'Prepare statement gagal');
    }
    header("Location: galeri");
    exit();
}

// ========== UPDATE URUTAN & KETERANGAN FOTO DALAM GRUP ==========
if (isset($_POST['update_urutan_keterangan'])) {
    $judul = trim($_POST['judul_grup']);
    $ids = $_POST['id_galeri'] ?? [];
    $keterangans = $_POST['keterangan'] ?? [];
    $urutans = $_POST['urutan'] ?? [];

    for ($i = 0; $i < count($ids); $i++) {
        $id = (int)$ids[$i];
        $ket = trim($keterangans[$i]);
        $urutan = (int)$urutans[$i];
        $stmt = mysqli_prepare($conn, "UPDATE galeri SET keterangan=?, urutan=? WHERE id_galeri=?");
        mysqli_stmt_bind_param($stmt, "sii", $ket, $urutan, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    setFlash('success', 'Urutan dan keterangan foto berhasil diperbarui.');
    header("Location: galeri");
    exit();
}

// ========== HAPUS GRUP ==========
if (isset($_GET['hapus_grup'])) {
    $judul = trim($_GET['hapus_grup']);
    $query = mysqli_query($conn, "SELECT gambar FROM galeri WHERE judul='$judul'");
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $filePath = '../uploads/galeri/' . $row['gambar'];
            if (file_exists($filePath)) unlink($filePath);
        }
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM galeri WHERE judul=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $judul);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    setFlash('success', "Grup '$judul' berhasil dihapus beserta semua fotonya.");
    header("Location: galeri");
    exit();
}

// ========== HAPUS SATU FOTO ==========
if (isset($_GET['hapus_foto']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT gambar FROM galeri WHERE id_galeri=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $filePath = '../uploads/galeri/' . $row['gambar'];
            if (file_exists($filePath)) unlink($filePath);
        }
        mysqli_stmt_close($stmt);
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM galeri WHERE id_galeri=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    setFlash('success', 'Foto berhasil dihapus.');
    header("Location: galeri");
    exit();
}

// ========== AMBIL DATA GRUP ==========
$queryGrup = mysqli_query($conn, "
    SELECT 
        judul, 
        MAX(tanggal_upload) as tgl_terbaru, 
        COUNT(*) as jumlah_foto,
        MIN(id_galeri) as id_first,
        deskripsi,
        tipe
    FROM galeri
    GROUP BY judul
    ORDER BY tgl_terbaru ASC
");
if (!$queryGrup) {
    $dataError = mysqli_error($conn);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/navbar_admin.php';
include '../includes/sidebar_admin.php';
?>

<!-- CDN jQuery & Summernote -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div class="content-wrapper">
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pt-2">
            <h2 class="fw-bold"><i class="fas fa-images me-2 text-primary"></i> Galeri Foto</h2>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahMultiple">
                <i class="fas fa-plus me-2"></i> Tambah Grup Galeri
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

        <div class="row g-4">
            <?php if (isset($dataError)): ?>
                <div class="col-12">
                    <div class="alert alert-danger">Error query: <?php echo htmlspecialchars($dataError); ?></div>
                </div>
            <?php elseif (mysqli_num_rows($queryGrup) > 0): ?>
                <?php while ($grup = mysqli_fetch_assoc($queryGrup)):
                    $queryFirst = mysqli_query($conn, "SELECT gambar FROM galeri WHERE judul='{$grup['judul']}' ORDER BY urutan ASC, id_galeri ASC LIMIT 1");
                    $first = mysqli_fetch_assoc($queryFirst);
                    $thumbnail = $first ? $first['gambar'] : '';

                    $deskripsi_plain = strip_tags($grup['deskripsi']);
                    $deskripsi_plain = htmlspecialchars($deskripsi_plain);
                    $deskripsi_cut = substr($deskripsi_plain, 0, 100);
                    $deskripsi_cut = !empty($deskripsi_cut) ? nl2br($deskripsi_cut) : '';
                ?>
                    <div class="col-md-4 col-sm-6" data-aos="fade-up">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-card">
                            <?php if ($thumbnail): ?>
                                <img src="../uploads/galeri/<?php echo htmlspecialchars($thumbnail); ?>"
                                    class="card-img-top rounded-top-4" style="height: 220px; object-fit: cover;"
                                    alt="<?php echo htmlspecialchars($grup['judul']); ?>">
                            <?php else: ?>
                                <div class="bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 220px;">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($grup['judul']); ?></h5>
                                <?php if (!empty($deskripsi_cut)): ?>
                                    <p class="small text-muted"><?php echo $deskripsi_cut; ?></p>
                                <?php endif; ?>
                                <span class="badge bg-secondary rounded-pill mb-2">
                                    <i class="fas fa-tag me-1"></i> <?php echo $grup['tipe'] == 'foto' ? 'Foto Kegiatan' : ($grup['tipe'] == 'desain' ? 'Desain Gereja' : 'Kegiatan'); ?>
                                </span>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-info rounded-pill px-3 me-1 btn-lihat-foto"
                                        data-judul="<?php echo htmlspecialchars($grup['judul']); ?>">
                                        <i class="fas fa-images me-1"></i> Lihat (<?php echo $grup['jumlah_foto']; ?> foto)
                                    </button>
                                    <button class="btn btn-sm btn-warning rounded-pill px-3 me-1 btn-edit-grup"
                                        data-judul="<?php echo htmlspecialchars($grup['judul']); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($grup['deskripsi']); ?>"
                                        data-tipe="<?php echo $grup['tipe']; ?>">
                                        <i class="fas fa-edit me-1"></i> Edit Grup
                                    </button>
                                    <button class="btn btn-sm btn-danger rounded-pill px-3 btn-hapus-grup" data-judul="<?php echo htmlspecialchars($grup['judul']); ?>">
                                        <i class="fas fa-trash me-1"></i> Hapus Grup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-images fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada grup galeri. Klik "Tambah Grup Galeri" untuk memulai.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH GRUP BARU -->
<div class="modal fade" id="modalTambahMultiple" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form method="POST" enctype="multipart/form-data" id="formMultipleGaleri">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Grup Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3"><label class="form-label fw-semibold">Judul Grup</label><input type="text" name="judul" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi Grup (opsional)</label><textarea name="deskripsi_global" id="deskripsiTambah" class="form-control rounded-3 summernote-deskripsi" rows="4" placeholder="Deskripsi umum untuk grup ini..."></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Tipe Galeri</label><select name="tipe" class="form-select rounded-3">
                            <option value="foto">Foto Kegiatan</option>
                            <option value="desain">Desain Gereja</option>
                            <option value="kegiatan">Kegiatan</option>
                        </select></div>
                    <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i> Pilih beberapa file gambar sekaligus.</div>
                    <div class="mb-3"><input type="file" name="gambar_multiple[]" id="gambarMultipleInput" class="form-control rounded-3" accept="image/*" multiple required></div>
                    <div id="galeriItemsContainer" class="mt-3"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_multiple" class="btn btn-primary rounded-pill px-4"><i class="fas fa-upload me-1"></i> Buat Grup</button></div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT GRUP (GABUNG ATUR FOTO + UPLOAD) -->
<div class="modal fade" id="modalEditGrup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form method="POST" id="formEditGrup">
                <input type="hidden" name="judul_lama" id="edit_judul_lama">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-edit me-2 text-warning"></i> Edit Grup & Atur Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="fw-bold text-primary">Informasi Grup</h6>
                        <div class="mb-3"><label class="form-label fw-semibold">Judul Grup Baru</label><input type="text" name="judul_baru" id="edit_judul_baru" class="form-control rounded-3" required></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Deskripsi Grup</label><textarea name="deskripsi_baru" id="edit_deskripsi_baru" class="form-control rounded-3 summernote-deskripsi" rows="4"></textarea></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Tipe</label><select name="tipe_baru" id="edit_tipe_baru" class="form-select rounded-3">
                                <option value="foto">Foto Kegiatan</option>
                                <option value="desain">Desain Gereja</option>
                                <option value="kegiatan">Kegiatan</option>
                            </select></div>
                    </div>

                    <h6 class="fw-bold text-primary mb-2">Atur Foto dalam Grup (drag & drop untuk urutan)</h6>
                    <div id="fotoContainer" class="sortable-container"></div>
                    <input type="hidden" name="judul_grup" id="edit_judul_grup_for_foto">

                    <!-- Form upload foto baru -->
                    <div class="mt-4 pt-2">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-upload me-2"></i> Tambah Foto Baru ke Grup Ini</h6>
                        <div class="alert alert-info alert-sm py-2"><i class="fas fa-info-circle me-1"></i> Pilih beberapa file gambar sekaligus.</div>
                        <div class="mb-3"><input type="file" id="uploadFotoGrupInput" class="form-control rounded-3" accept="image/*" multiple></div>
                        <div id="uploadPreviewContainer" class="mt-3"></div>
                        <button type="button" id="btnUploadFotoGrup" class="btn btn-primary rounded-pill px-4 mt-2"><i class="fas fa-cloud-upload-alt me-1"></i> Upload Foto</button>
                        <div id="uploadStatus" class="mt-2 small"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_grup" class="btn btn-primary rounded-pill px-4 me-2">Update Info Grup</button>
                    <button type="submit" name="update_urutan_keterangan" class="btn btn-success rounded-pill px-4">Simpan Urutan & Keterangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL LIHAT FOTO -->
<div class="modal fade" id="modalLihatFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold"><i class="fas fa-images me-2 text-primary"></i> Foto dalam Grup: <span id="lihatJudul"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3" id="daftarFotoGrup"></div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        $('.summernote-deskripsi').summernote({
            height: 200,
            minHeight: 150,
            maxHeight: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            placeholder: 'Tulis deskripsi grup di sini...'
        });
    });

    function confirmDeleteFoto(id) {
        Swal.fire({
            title: 'Hapus Foto?',
            text: "Foto akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = `?hapus_foto=1&id=${id}`;
        });
    }

    // Preview untuk tambah grup baru
    const fileInput = document.getElementById('gambarMultipleInput');
    const container = document.getElementById('galeriItemsContainer');
    let currentFiles = [];

    function updateFileInput() {
        const dt = new DataTransfer();
        currentFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function renderPreview() {
        container.innerHTML = '';
        currentFiles.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'galeri-item mb-3 p-3 border rounded-3';
                div.setAttribute('data-index', idx);
                div.innerHTML = `
                    <div class="row g-3 align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="badge bg-secondary me-2">${idx+1}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary move-up" ${idx===0?'disabled':''}><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary move-down" ${idx===currentFiles.length-1?'disabled':''}><i class="fas fa-arrow-down"></i></button>
                        </div>
                        <div class="col-md-3"><img src="${e.target.result}" class="img-fluid rounded" style="max-height:100px; object-fit:cover;"></div>
                        <div class="col-md-7"><label class="form-label fw-semibold">Keterangan Gambar (opsional)</label><textarea name="keterangan_per_gambar[]" class="form-control rounded-3" rows="2" placeholder="Keterangan khusus untuk foto ini..."></textarea></div>
                        <div class="col-md-1 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-barang" data-idx="${idx}"><i class="fas fa-trash"></i></button></div>
                    </div>
                `;
                container.appendChild(div);
                div.querySelector('.move-up')?.addEventListener('click', () => moveItem(idx, idx - 1));
                div.querySelector('.move-down')?.addEventListener('click', () => moveItem(idx, idx + 1));
                div.querySelector('.btn-hapus-barang').addEventListener('click', function() {
                    const i = parseInt(this.dataset.idx);
                    currentFiles.splice(i, 1);
                    updateFileInput();
                    renderPreview();
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function moveItem(from, to) {
        if (to < 0 || to >= currentFiles.length) return;
        const [m] = currentFiles.splice(from, 1);
        currentFiles.splice(to, 0, m);
        updateFileInput();
        renderPreview();
    }
    if (fileInput) fileInput.addEventListener('change', function(e) {
        currentFiles = Array.from(e.target.files);
        renderPreview();
    });

    // Upload foto baru di modal edit
    let currentUploadFiles = [];
    const uploadInput = document.getElementById('uploadFotoGrupInput');
    const uploadContainer = document.getElementById('uploadPreviewContainer');

    function updateUploadFileInput() {
        const dt = new DataTransfer();
        currentUploadFiles.forEach(f => dt.items.add(f));
        uploadInput.files = dt.files;
    }

    function renderUploadPreview() {
        uploadContainer.innerHTML = '';
        currentUploadFiles.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'galeri-item mb-3 p-3 border rounded-3';
                div.setAttribute('data-index', idx);
                div.innerHTML = `
                    <div class="row g-3 align-items-center">
                        <div class="col-md-1 text-center"><span class="badge bg-secondary me-2">${idx+1}</span>
                        <button type="button" class="btn btn-sm btn-outline-primary move-up-upload" ${idx===0?'disabled':''}><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-primary move-down-upload" ${idx===currentUploadFiles.length-1?'disabled':''}><i class="fas fa-arrow-down"></i></button></div>
                        <div class="col-md-3"><img src="${e.target.result}" class="img-fluid rounded" style="max-height:100px; object-fit:cover;"></div>
                        <div class="col-md-7"><label class="form-label fw-semibold">Keterangan Gambar (opsional)</label><textarea name="keterangan_upload[]" class="form-control rounded-3" rows="2" placeholder="Keterangan khusus untuk foto ini..."></textarea></div>
                        <div class="col-md-1 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-upload" data-idx="${idx}"><i class="fas fa-trash"></i></button></div>
                    </div>
                `;
                uploadContainer.appendChild(div);
                div.querySelector('.move-up-upload')?.addEventListener('click', () => moveUploadItem(idx, idx - 1));
                div.querySelector('.move-down-upload')?.addEventListener('click', () => moveUploadItem(idx, idx + 1));
                div.querySelector('.btn-hapus-upload')?.addEventListener('click', function() {
                    const i = parseInt(this.dataset.idx);
                    currentUploadFiles.splice(i, 1);
                    updateUploadFileInput();
                    renderUploadPreview();
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function moveUploadItem(from, to) {
        if (to < 0 || to >= currentUploadFiles.length) return;
        const [m] = currentUploadFiles.splice(from, 1);
        currentUploadFiles.splice(to, 0, m);
        updateUploadFileInput();
        renderUploadPreview();
    }
    if (uploadInput) uploadInput.addEventListener('change', function(e) {
        currentUploadFiles = Array.from(e.target.files);
        renderUploadPreview();
    });

    const uploadBtn = document.getElementById('btnUploadFotoGrup');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            if (currentUploadFiles.length === 0) {
                Swal.fire('Info', 'Pilih file gambar terlebih dahulu.', 'info');
                return;
            }
            const judul = document.getElementById('edit_judul_lama').value;
            if (!judul) {
                Swal.fire('Error', 'Grup tidak valid.', 'error');
                return;
            }
            const formData = new FormData();
            // 🔥 WAJIB: tambahkan parameter untuk trigger handler PHP
            formData.append('tambah_ke_grup', '1');
            formData.append('judul_grup', judul);
            formData.append('tipe_grup', document.getElementById('edit_tipe_baru').value);
            const keteranganTextareas = document.querySelectorAll('#uploadPreviewContainer textarea[name="keterangan_upload[]"]');
            for (let i = 0; i < currentUploadFiles.length; i++) {
                formData.append('gambar_grup[]', currentUploadFiles[i]);
                const ket = keteranganTextareas[i] ? keteranganTextareas[i].value : '';
                formData.append('keterangan_per_gambar_grup[]', ket);
            }
            const statusDiv = document.getElementById('uploadStatus');
            statusDiv.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-pulse"></i> Mengupload...</span>';
            uploadBtn.disabled = true;
            fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + ' Memuat ulang daftar foto...</span>';
                        loadFotoGrup(judul);
                        currentUploadFiles = [];
                        updateUploadFileInput();
                        renderUploadPreview();
                        setTimeout(() => {
                            statusDiv.innerHTML = '';
                        }, 3000);
                    } else {
                        statusDiv.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    statusDiv.innerHTML = '<span class="text-danger">Error: ' + err.message + '</span>';
                })
                .finally(() => {
                    uploadBtn.disabled = false;
                });
        });
    }

    let sortableInstance = null;

    function loadFotoGrup(judul) {
        fetch(`get_foto_grup.php?judul=${encodeURIComponent(judul)}`)
            .then(res => res.json())
            .then(data => {
                const containerFoto = document.getElementById('fotoContainer');
                containerFoto.innerHTML = '';
                data.forEach((foto, idx) => {
                    const div = document.createElement('div');
                    div.className = 'sortable-item mb-3 p-3 border rounded-3';
                    div.setAttribute('data-id', foto.id_galeri);
                    div.innerHTML = `
                    <div class="row g-3 align-items-center">
                        <div class="col-md-1 text-center"><i class="fas fa-grip-vertical me-2 drag-handle" style="cursor:move;"></i><span class="badge bg-secondary">${idx+1}</span></div>
                        <div class="col-md-2"><img src="../uploads/galeri/${foto.gambar}" class="img-fluid rounded" style="max-height:80px; object-fit:cover;"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Keterangan</label><textarea name="keterangan[]" class="form-control rounded-3" rows="2">${foto.keterangan ? foto.keterangan.replace(/"/g,'&quot;') : ''}</textarea></div>
                        <div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-foto-edit" data-id="${foto.id_galeri}">Hapus</button></div>
                    </div>
                    <input type="hidden" name="id_galeri[]" value="${foto.id_galeri}"><input type="hidden" name="urutan[]" value="${foto.urutan ? foto.urutan : idx}">
                `;
                    containerFoto.appendChild(div);
                });
                document.querySelectorAll('#fotoContainer .btn-hapus-foto-edit').forEach(btn => btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    confirmDeleteFoto(this.dataset.id);
                }));
                if (sortableInstance) sortableInstance.destroy();
                sortableInstance = new Sortable(containerFoto, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function() {
                        document.querySelectorAll('#fotoContainer .sortable-item').forEach((item, newIdx) => {
                            const u = item.querySelector('input[name="urutan[]"]');
                            if (u) u.value = newIdx;
                        });
                    }
                });
            })
            .catch(error => console.error('Error loading photos:', error));
    }

    document.querySelectorAll('.btn-edit-grup').forEach(btn => {
        btn.addEventListener('click', function() {
            const judul = this.dataset.judul,
                deskripsi = this.dataset.deskripsi,
                tipe = this.dataset.tipe;
            document.getElementById('edit_judul_lama').value = judul;
            document.getElementById('edit_judul_baru').value = judul;
            $('#edit_deskripsi_baru').summernote('code', deskripsi);
            document.getElementById('edit_tipe_baru').value = tipe;
            document.getElementById('edit_judul_grup_for_foto').value = judul;
            currentUploadFiles = [];
            updateUploadFileInput();
            renderUploadPreview();
            document.getElementById('uploadStatus').innerHTML = '';
            loadFotoGrup(judul);
            new bootstrap.Modal(document.getElementById('modalEditGrup')).show();
        });
    });

    document.querySelectorAll('.btn-hapus-grup').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const judul = this.dataset.judul;
            Swal.fire({
                    title: 'Hapus Grup?',
                    text: `Semua foto dalam grup "${judul}" akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                })
                .then((result) => {
                    if (result.isConfirmed) window.location.href = '?hapus_grup=' + encodeURIComponent(judul);
                });
        });
    });

    document.querySelectorAll('.btn-lihat-foto').forEach(btn => {
        btn.addEventListener('click', function() {
            const judul = this.dataset.judul;
            document.getElementById('lihatJudul').innerText = judul;
            fetch(`get_foto_grup.php?judul=${encodeURIComponent(judul)}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('daftarFotoGrup');
                    container.innerHTML = '';
                    if (data.length === 0) container.innerHTML = '<div class="col-12 text-center text-muted">Tidak ada foto.</div>';
                    else {
                        data.forEach(foto => {
                            const col = document.createElement('div');
                            col.className = 'col-md-4 col-sm-6 mb-3';
                            col.innerHTML = `<div class="card h-100 shadow-sm"><img src="../uploads/galeri/${foto.gambar}" class="card-img-top" style="height:180px; object-fit:cover;"><div class="card-body p-2"><p class="small mb-2">${foto.keterangan||'Tidak ada keterangan'}</p><button class="btn btn-sm btn-outline-danger btn-hapus-foto-libat" data-id="${foto.id_galeri}">Hapus Foto</button></div></div>`;
                            container.appendChild(col);
                        });
                        document.querySelectorAll('#daftarFotoGrup .btn-hapus-foto-libat').forEach(btnHapus => btnHapus.addEventListener('click', function(e) {
                            e.preventDefault();
                            confirmDeleteFoto(this.dataset.id);
                        }));
                    }
                    new bootstrap.Modal(document.getElementById('modalLihatFoto')).show();
                }).catch(console.error);
        });
    });
</script>

<style>
    .transition-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .transition-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .galeri-item .move-up,
    .galeri-item .move-down {
        margin: 2px;
    }

    .sortable-container {
        min-height: 200px;
    }

    .sortable-item {
        background: #f8f9fa;
        cursor: default;
    }

    .drag-handle {
        cursor: grab;
    }

    .note-editor {
        border-radius: 0.75rem !important;
        border-color: #dee2e6 !important;
    }

    .note-toolbar {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
</style>

<?php include '../includes/footer_admin.php';
ob_end_flush();
?>