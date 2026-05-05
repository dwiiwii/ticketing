<?php
session_start();
require_once 'db.php';
// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
$user = $_SESSION['user'];

// RBAC Redirect for standard users
if (($user['role'] ?? 'user') === 'user') {
    $redirectUrl = 'Operasional.php';
    if (strtolower($user['name'] ?? '') === 'finance') $redirectUrl = 'Finance.php';
    elseif (strtolower($user['name'] ?? '') === 'accounting') $redirectUrl = 'Accounting.php';
    header('Location: ' . $redirectUrl);
    exit();
}

// Fetch all tickets and categorize them
$bukaTickets = [];
$prosesTickets = [];
$selesaiTickets = [];

try {
    $stmt = $pdo->query("SELECT * FROM tickets ORDER BY updated_at DESC, created_at DESC");
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as $t) {
        if ($t['status'] === 'Buka') {
            $bukaTickets[] = $t;
        } elseif (strtolower($t['status']) === 'proses') {
            $prosesTickets[] = $t;
        } else {
            $selesaiTickets[] = $t;
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasremit Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Flatpickr for Calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                        <span id="currentDate">Monday, 6th March</span>
                        <i class="ri-calendar-line"></i>
                    </div>
                    
                    <div class="auth-buttons">
                        <a href="logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
                    </div>
                </div>
            </header>

            <div class="header-with-btn">
                <div>
                    <h2>Tiket Tugas Saya</h2>
                    <p>Tampilan Kanban untuk alur kerja Anda.</p>
                </div>
            </div>

            <div class="kanban-board">
                <!-- Column: To Do -->
                <div class="kanban-column">
                    <div class="kanban-header">
                        <span>Diterima</span>
                        <span class="count"><?= count($bukaTickets) ?></span>
                    </div>
                    <?php foreach ($bukaTickets as $t): ?>
                        <div class="kanban-card">
                            <h4><?= htmlspecialchars($t['ticket_number']) ?>: <?= htmlspecialchars($t['subject']) ?></h4>
                            <p><?= htmlspecialchars($t['requester_name']) ?> • SLA: <?= htmlspecialchars($t['sla'] ?? '15 Menit') ?></p>
                            <span class="status"><i class="ri-time-line"></i> <?= date('d M Y', strtotime($t['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Column: In Progress -->
                <div class="kanban-column">
                    <div class="kanban-header">
                        <span>Proses</span>
                        <span class="count"><?= count($prosesTickets) ?></span>
                    </div>
                    <?php foreach ($prosesTickets as $t): ?>
                        <div class="kanban-card">
                            <h4><?= htmlspecialchars($t['ticket_number']) ?>: <?= htmlspecialchars($t['subject']) ?></h4>
                            <p><?= htmlspecialchars($t['requester_name']) ?> • SLA: <?= htmlspecialchars($t['sla'] ?? '15 Menit') ?></p>
                            <span class="status in-progress"><i class="ri-refresh-line"></i> Bekerja</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Column: Done -->
                <div class="kanban-column">
                    <div class="kanban-header">
                        <span>Selesai</span>
                        <span class="count"><?= count($selesaiTickets) ?></span>
                    </div>
                    <?php foreach ($selesaiTickets as $t): ?>
                        <div class="kanban-card" style="opacity: 0.7;">
                            <h4><?= htmlspecialchars($t['ticket_number']) ?>: <?= htmlspecialchars($t['subject']) ?></h4>
                            <p><?= htmlspecialchars($t['requester_name']) ?> • SLA: <?= htmlspecialchars($t['sla'] ?? '15 Menit') ?></p>
                            <span class="status done"><i class="ri-check-double-line"></i> Selesai</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
