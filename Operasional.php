<?php
session_start();
require_once 'db.php';
// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
$user = $_SESSION['user'];

// Fetch user's tickets from DB
try {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching tickets: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operasional - IT Helpdesk</title>
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

            <!-- Tickets List -->
            <div class="card">
                <div class="header-with-btn">
                    <div>
                        <h2>Operasional</h2>

                    </div>
                    <a href="add_ticket.php" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;"><i class="ri-add-line"></i> Buat Tiket</a>
                </div>
                
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th style="min-width: 50px;">No.</th>
                            <th style="min-width: 120px;">No. Ticket</th>
                            <th style="min-width: 180px;">Nama</th>
                            <th style="min-width: 220px;">Kategori</th>
                            <th style="min-width: 200px;">Subjek</th>
                            <th style="min-width: 120px;">Status</th>
                            <th style="min-width: 100px;">SLA</th>
                            <th style="min-width: 200px;">Pembaharuan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $cat_labels = [
                                'hardware' => 'Perangkat Keras (Hardware)',
                                'software' => 'Perangkat Lunak (Software / Lisensi)',
                                'network'  => 'Jaringan & Internet',
                                'account'  => 'Akses Akun & Password',
                                'other'    => 'Lainnya'
                            ];
                        ?>
                        <?php if (count($tickets) > 0): ?>
                            <?php $no = 1; foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>
                                    <td><?= htmlspecialchars($ticket['requester_name']) ?></td>
                                    <td><?= htmlspecialchars($cat_labels[$ticket['category']] ?? ucfirst($ticket['category'])) ?></td>
                                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                    <td>
                                        <?php if ($ticket['status'] === 'Buka'): ?>
                                            <span class="status in-progress"><i class="ri-time-line"></i> Buka</span>
                                        <?php elseif (strtolower($ticket['status']) === 'proses'): ?>
                                            <span class="status in-progress" style="color: #d97706; background: #fef3c7; border-color: #fde68a;"><i class="ri-refresh-line"></i> Proses</span>
                                        <?php else: ?>
                                            <span class="status done"><i class="ri-check-line"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-weight: 500; color: #475569;"><?= !empty($ticket['sla']) ? htmlspecialchars($ticket['sla']) : '-' ?></span></td>
                                    <td><?= date('d F Y H:i', strtotime($ticket['updated_at'] ?? $ticket['created_at'])) ?> Wib</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-secondary);">
                                    <i class="ri-inbox-line" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                    Belum ada tiket yang dibuat.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-top: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary); font-size: 13px;">Menampilkan semua tiket (<?= count($tickets) ?>)</span>
                    <div style="display: flex; gap: 8px;">
                        <button style="padding: 6px 12px; border: 1px solid var(--border-color); background: transparent; border-radius: 6px; cursor: pointer; color: var(--text-secondary);" disabled>Prev</button>
                        <button style="padding: 6px 12px; border: 1px solid var(--accent-blue); background: var(--accent-blue); border-radius: 6px; cursor: pointer; color: white;">1</button>
                        <button style="padding: 6px 12px; border: 1px solid var(--border-color); background: transparent; border-radius: 6px; cursor: pointer; color: var(--text-primary);">2</button>
                        <button style="padding: 6px 12px; border: 1px solid var(--border-color); background: transparent; border-radius: 6px; cursor: pointer; color: var(--text-primary);">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
