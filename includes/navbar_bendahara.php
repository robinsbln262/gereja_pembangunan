<?php
// Jangan panggil session_start() di sini
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bendahara - Sistem Informasi Pembangunan Gereja</title>
    <link rel="icon" type="image/png" href="../uploads/informasi/logo-st-laurensius.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        h1,h2,h3,h4,.navbar-brand { font-family: 'Poppins', sans-serif; }
        .navbar-admin {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 0.75rem 0;
            z-index: 1030;
        }
        .navbar-brand { font-size: 1.4rem; transition: transform 0.2s; }
        .navbar-brand:hover { transform: scale(1.02); color: #f8f9fa !important; }
        .dropdown-user {
            background: rgba(255,255,255,0.1);
            border-radius: 40px;
            padding: 8px 20px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .dropdown-user:hover { background: rgba(255,255,255,0.2); transform: translateY(-1px); }
        #mobile-menu-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 8px 12px;
        }
        #mobile-menu-toggle:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
        .dropdown-item { padding: 10px 20px; border-radius: 8px; margin: 2px 8px; }
        .dropdown-item:hover { background-color: #f8f9fa; transform: translateX(5px); }
        #loading-screen {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: opacity 0.5s ease;
        }
        .loading-spinner {
            width: 60px; height: 60px;
            border: 5px solid rgba(255,193,7,0.2);
            border-top: 5px solid #ffc107;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loading-fadeout { opacity: 0; visibility: hidden; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid px-4">
        <button class="btn btn-light d-lg-none" id="mobile-menu-toggle" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand text-white fw-bold" href="dashboard"><i class="fas fa-church me-2"></i> Bendahara Panel</a>
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn dropdown-toggle text-white dropdown-user" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Bendahara'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li><a class="dropdown-item" href="javascript:void(0);" id="navbarLogoutBtn"><i class="fas fa-sign-out-alt me-2 text-danger"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<div id="loading-screen"><div class="loading-spinner"></div><p class="text-white mt-4 fw-semibold">Memuat Panel Bendahara...</p></div>
<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.classList.add('loading-fadeout');
            setTimeout(function() { loadingScreen.style.display = 'none'; }, 500);
        }
    }, 500);
});

document.addEventListener('DOMContentLoaded', function() {
    const navbarLogoutBtn = document.getElementById('navbarLogoutBtn');
    if (navbarLogoutBtn) {
        navbarLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: 'Anda akan keluar dari halaman bendahara!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-sign-out-alt me-2"></i> Ya, logout!',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Logout...',
                        text: 'Sedang mengarahkan ke halaman login',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        window.location.href = 'logout';
                    }, 500);
                }
            });
        });
    }
});
</script>