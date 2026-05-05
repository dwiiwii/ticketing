<?php
session_start();
require_once 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
$user = $_SESSION['user'];

$backLink = 'Admin.php';
if ($user['role'] === 'user') {
    if (strtolower($user['name']) === 'finance') $backLink = 'Finance.php';
    elseif (strtolower($user['name']) === 'accounting') $backLink = 'Accounting.php';
    else $backLink = 'Operasional.php';
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $category = $_POST['category'] ?? '';
    $requester = $_POST['requester'] ?? $user['name'];
    $description = $_POST['description'] ?? '';
    $priority = 'Sedang'; // Default priority
    $status = 'Buka';
    
    // Generate sequential ticket number FR-X
    $stmt = $pdo->query("SELECT MAX(id) FROM tickets");
    $maxId = $stmt->fetchColumn();
    $nextId = $maxId ? $maxId + 1 : 1;
    $ticket_number = 'FR-' . $nextId;
    
    // Ensure attachment column exists (lazy setup)
    try {
        $pdo->exec("ALTER TABLE tickets ADD COLUMN attachment VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $e) {
        // Ignore if already exists
    }

    $attachment = null;
    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = time() . '_' . basename($_FILES['attachment']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'png', 'jpeg', 'pdf');
        
        if (in_array($fileType, $allowedTypes) && $_FILES['attachment']['size'] <= 5000000) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
                $attachment = $fileName;
            } else {
                $error_msg = "Maaf, terjadi kesalahan saat mengunggah file.";
            }
        } else {
            $error_msg = "Format file tidak didukung atau ukuran melebihi 5MB.";
        }
    }

    if (empty($error_msg)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, user_id, requester_name, category, priority, subject, description, status, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ticket_number, $user['id'], $requester, $category, $priority, $subject, $description, $status, $attachment]);
            
            $success_msg = "Tiket berhasil dibuat dengan nomor $ticket_number!";
        } catch (PDOException $e) {
            $error_msg = "Gagal membuat tiket: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Tiket Baru - IT Helpdesk</title>
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

            <!-- Create Ticket Form -->
            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <div class="header-with-btn" style="margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
                    <div>
                        <h2>Buat Tiket Baru</h2>
                        <p style="color: var(--text-secondary); margin-top: 8px;">Silakan isi formulir di bawah ini dengan detail masalah yang Anda alami.</p>
                    </div>
                </div>
                
                <?php if ($success_msg): ?>
                    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="ri-checkbox-circle-fill" style="font-size: 20px;"></i>
                        <span><?= $success_msg ?> <a href="<?= $backLink ?>" style="color: #166534; font-weight: bold; text-decoration: underline; margin-left: 10px;">Lihat Tiket Saya</a></span>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="ri-error-warning-fill" style="font-size: 20px;"></i>
                        <span><?= $error_msg ?></span>
                    </div>
                <?php endif; ?>
                
                <form action="add_ticket.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="subject">Subjek / Judul Masalah <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="Contoh: Permintaan Akses Folder Finance" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label" for="requester">Nama Pengajuan <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="requester" name="requester" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="category">Kategori Keluhan <span style="color: #ef4444;">*</span></label>
                            <select id="category" name="category" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <option value="hardware">Perangkat Keras (Hardware)</option>
                                <option value="software">Perangkat Lunak (Software / Lisensi)</option>
                                <option value="network">Jaringan & Internet</option>
                                <option value="account">Akses Akun & Password</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Deskripsi Lengkap <span style="color: #ef4444;">*</span></label>
                        <textarea id="description" name="description" class="form-control" placeholder="Jelaskan secara rinci masalah yang Anda hadapi, langkah-langkah yang sudah dicoba, atau pesan error yang muncul..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="attachment">Lampiran (Opsional)</label>
                        <div style="border: 2px dashed var(--border-color); padding: 30px; text-align: center; border-radius: var(--radius-md); background-color: rgba(0,0,0,0.02);">
                            <i class="ri-upload-cloud-2-line" style="font-size: 32px; color: var(--text-secondary); margin-bottom: 10px; display: block;"></i>
                            <p style="font-size: 14px; color: var(--text-primary); margin-bottom: 5px;">Tarik & Lepas file ke sini atau <span style="color: var(--accent-blue); font-weight: 500; cursor: pointer;">Pilih File</span></p>
                            <p style="font-size: 12px; color: var(--text-secondary);">Maks. 5MB (JPG, PNG, PDF)</p>
                            <input type="file" id="attachment" name="attachment" style="display: none;">
                        </div>
                    </div>

                    <div class="form-actions" style="justify-content: flex-end; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 20px;">
                        <a href="<?= $backLink ?>" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary" style="padding: 10px 24px;">Kirim Tiket</button>
                    </div>
                </form>
            </div>
            
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Simple script to make upload box clickable
        document.querySelector('.ri-upload-cloud-2-line').parentElement.addEventListener('click', function() {
            document.getElementById('attachment').click();
        });
    </script>
</body>
</html>
