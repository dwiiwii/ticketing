<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
$user = $_SESSION['user'];

$searchQuery = trim($_GET['ticket'] ?? '');
$ticket = null;
$error = '';

if ($searchQuery) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_number = ?");
        $stmt->execute([$searchQuery]);
        $ticket = $stmt->fetch();
        
        if (!$ticket) {
            $error = "Tiket dengan nomor <strong>" . htmlspecialchars($searchQuery) . "</strong> tidak ditemukan.";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Tiket - IT Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .search-box input {
            flex: 1;
            padding: 14px 20px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--accent-blue);
        }
        .search-box button {
            padding: 0 24px;
            font-size: 15px;
        }

        /* Timeline Styles */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
            position: relative;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 20px;
            bottom: 20px;
            width: 2px;
            background-color: var(--border-color);
            z-index: 1;
        }
        .timeline-item {
            display: flex;
            gap: 20px;
            position: relative;
            z-index: 2;
        }
        .timeline-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background-color: var(--bg-main);
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .timeline-content {
            background-color: var(--bg-main);
            border: 1px solid var(--border-color);
            padding: 15px 20px;
            border-radius: var(--radius-md);
            flex: 1;
        }
        .timeline-content h4 {
            margin: 0 0 5px 0;
            color: var(--text-primary);
        }
        .timeline-content p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        /* Active States */
        .timeline-item.active .timeline-icon {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .timeline-item.done .timeline-icon {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
        }
        
        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(0,0,0,0.02);
            border-radius: var(--radius-md);
            border: 1px dashed var(--border-color);
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .detail-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 14px;
        }
    </style>
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
                    <div class="auth-buttons">
                        <a href="logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
                    </div>
                </div>
            </header>

            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <div class="header-with-btn" style="margin-bottom: 20px;">
                    <div>
                        <h2>Cek Status Tiket</h2>

                    </div>
                </div>
                
                <form action="track_ticket.php" method="GET" class="search-box">
                    <input type="text" name="ticket" placeholder="Masukkan Nomor Tiket (Contoh: FR-1)" value="<?= htmlspecialchars($searchQuery) ?>" required>
                    <button type="submit" class="btn-primary"><i class="ri-search-line"></i> Lacak</button>
                </form>

                <?php if ($error): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                        <i class="ri-error-warning-fill" style="font-size: 20px;"></i>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($ticket): 
                    $status = strtolower($ticket['status']);
                    // Logic progress
                    $isCreated = true; // Selalu true
                    $isProgress = in_array($status, ['proses', 'selesai']);
                    $isDone = ($status === 'selesai');
                ?>
                    <div class="ticket-details">
                        <div class="detail-item">
                            <span class="detail-label">Nomor Tiket</span>
                            <span class="detail-value" style="color: var(--accent-blue);"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nama Pengajuan</span>
                            <span class="detail-value"><?= htmlspecialchars($ticket['requester_name']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Subjek</span>
                            <span class="detail-value"><?= htmlspecialchars($ticket['subject']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Kategori</span>
                            <span class="detail-value"><?= htmlspecialchars(ucfirst($ticket['category'])) ?></span>
                        </div>
                    </div>

                    <h3 style="font-size: 16px; margin-bottom: 10px; color: var(--text-primary);">Linimasa Penanganan</h3>
                    <div class="timeline">
                        <!-- Step 1: Dibuat -->
                        <div class="timeline-item <?= $isCreated && !$isProgress ? 'active' : 'done' ?>">
                            <div class="timeline-icon">
                                <i class="<?= $isProgress ? 'ri-check-line' : 'ri-file-add-line' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Tiket Dibuat</h4>
                                <p>Tiket telah diterima oleh sistem pada <?= date('d M Y, H:i', strtotime($ticket['created_at'])) ?>.</p>
                            </div>
                        </div>

                        <!-- Step 2: Sedang Diproses -->
                        <div class="timeline-item <?= $isProgress && !$isDone ? 'active' : ($isDone ? 'done' : '') ?>">
                            <div class="timeline-icon">
                                <i class="<?= $isDone ? 'ri-check-line' : 'ri-settings-4-line' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Sedang Diproses</h4>
                                <p>Tim IT sedang memeriksa dan memperbaiki masalah.</p>
                            </div>
                        </div>

                        <!-- Step 3: Selesai -->
                        <div class="timeline-item <?= $isDone ? 'active done' : '' ?>">
                            <div class="timeline-icon">
                                <i class="ri-flag-2-line"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Selesai Diperbaiki</h4>
                                <p><?= $isDone ? 'Masalah telah berhasil diselesaikan oleh Tim IT.' : 'Menunggu perbaikan selesai.' ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>
