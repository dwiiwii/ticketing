<?php
session_start();
require_once 'db.php';
// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengambilan Aset - IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            
            <!-- Topbar -->
            <header class="topbar">
                <div class="search-bar">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Cari...">
                </div>
                
                <div class="topbar-actions">
                    <div class="date-selector" id="datePicker">
                        <span id="currentDate">Senin, 6 Maret</span>
                        <i class="ri-calendar-line"></i>
                    </div>
                    
                    <div class="auth-buttons">
                        <a href="logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
                    </div>
                </div>
            </header>

            <div class="header-with-btn">
                <div>
                    <h2>Pengambilan Aset</h2>
                    <p>Halaman ini akan digunakan untuk pengajuan aset IT baru.</p>
                </div>
            </div>

            <div class="card" style="text-align: center; padding: 100px 20px; color: var(--text-secondary);">
                <i class="ri-tools-line" style="font-size: 48px; display: block; margin-bottom: 20px;"></i>
                <h3>Segera Hadir</h3>
                <p>Fitur pengambilan aset sedang dalam pengembangan.</p>
            </div>
            
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>
