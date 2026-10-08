<?php
// File ini hanya berisi HTML, CSS, dan JavaScript untuk sidebar bendahara
?>
<style>
    .sidebar {
        background: linear-gradient(180deg, #1a1a2e 0%, #0f0f1a 100%);
        min-height: 100vh;
        width: 260px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        padding-top: 70px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        height: 100vh;
        overflow-y: auto;
    }
    .sidebar .nav-link {
        color: #b0b0c0;
        padding: 12px 20px;
        transition: all 0.3s ease;
        border-radius: 10px;
        margin: 4px 12px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }
    .sidebar .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: #0d6efd;
        transform: scaleY(0);
        transition: transform 0.2s;
    }
    .sidebar .nav-link:hover::before,
    .sidebar .nav-link.active::before {
        transform: scaleY(1);
    }
    .sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
        color: white;
        transform: translateX(5px);
    }
    .sidebar .nav-link.active {
        background: linear-gradient(90deg, #0d6efd, #0b5ed7);
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    .sidebar .nav-link i {
        width: 28px;
        margin-right: 12px;
        font-size: 1.1rem;
        text-align: center;
    }
    .sidebar-logo {
        transition: all 0.3s;
        position: relative;
    }
    .sidebar-logo i {
        transition: transform 0.3s;
    }
    .sidebar-logo:hover i {
        transform: scale(1.1);
    }
    .sidebar-close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        z-index: 10;
    }
    .sidebar-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }
    .sidebar-close-btn i {
        font-size: 1.2rem;
    }
    .content-wrapper {
        margin-left: 260px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 20px;
        min-height: 100vh;
        background: #f4f6f9;
    }
    @media (max-width: 768px) {
        .sidebar {
            margin-left: -260px;
            box-shadow: none;
            z-index: 1050;
        }
        .sidebar.active {
            margin-left: 0;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.2);
        }
        .content-wrapper {
            margin-left: 0;
        }
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }
        .sidebar-overlay.active {
            display: block;
        }
        .sidebar-close-btn {
            display: flex;
        }
    }
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar::-webkit-scrollbar-track {
        background: #1e1e2e;
    }
    .sidebar::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
    }
</style>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <button class="sidebar-close-btn" id="sidebarCloseBtn"><i class="fas fa-times"></i></button>
    <div class="text-center py-3 border-bottom border-secondary sidebar-logo">
        <i class="fas fa-church fa-2x text-warning"></i>
        <h6 class="text-white mt-2 mb-0">Gereja St. Laurensius</h6>
        <small class="text-warning d-block fw-semibold" style="font-size:0.75rem;">Bendahara Panel</small>
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'donasi.php' ? 'active' : ''; ?>" href="donasi">
            <i class="fas fa-hand-holding-usd"></i> Data Donasi
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan_keuangan.php' ? 'active' : ''; ?>" href="laporan_keuangan">
            <i class="fas fa-chart-line"></i> Laporan Keuangan
        </a>
        <a class="nav-link text-danger" href="javascript:void(0);" id="sidebarLogoutBtn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>

<script>
    (function() {
        let toggleBtn = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeBtn = document.getElementById('sidebarCloseBtn');

        function closeSidebar() {
            if (sidebar && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        }
        function openSidebar() {
            sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
            if (toggleBtn) {
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            }
        }
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (sidebar.classList.contains('active')) closeSidebar();
                else openSidebar();
            });
        }
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (this.id !== 'sidebarLogoutBtn' && window.innerWidth <= 768) closeSidebar();
            });
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar && sidebar.classList.contains('active')) closeSidebar();
        });
        const logoutBtn = document.getElementById('sidebarLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
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
    })();
</script>