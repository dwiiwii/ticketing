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

// Fetch all tickets
try {
    $stmt = $pdo->query("SELECT t.*, u.name as divisi FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
    $tickets = $stmt->fetchAll();
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

            <!-- Tickets List -->
            <div class="card">
                <div class="header-with-btn">
                    <div>
                        <h2>Semua Tiket</h2>
                        <p>Kelola dan saring semua permintaan IT masuk.</p>
                    </div>
                </div>
                
                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="tasks-table">
                    <thead>
                        <tr>
                            <th style="min-width: 50px;">No.</th>
                            <th style="min-width: 120px;">No. Ticket</th>
                            <th style="min-width: 180px;">Nama</th>
                            <th style="min-width: 150px;">Divisi</th>
                            <th style="min-width: 200px;">Kategori</th>
                            <th style="min-width: 250px;">Subjek</th>
                            <th style="min-width: 150px;">Deskripsi & Gambar</th>
                            <th style="min-width: 160px; padding-right: 20px;">Status & SLA</th>
                            <th style="min-width: 200px;">Pembaharuan Terakhir</th>
                            <th style="min-width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tickets) > 0): ?>
                            <?php $no = 1; foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>
                                    <td><?= htmlspecialchars($ticket['requester_name']) ?></td>
                                    <td>
                                        <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; color: #475569;">
                                            <?= htmlspecialchars($ticket['divisi'] ?? 'Umum') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(ucfirst($ticket['category'] ?? 'hardware')) ?></td>
                                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                    <td>
                                        <button onclick="showDescription(this)" data-desc="<?= htmlspecialchars($ticket['description'] ?? '') ?>" data-attach="<?= htmlspecialchars($ticket['attachment'] ?? '') ?>" style="padding: 6px 12px; border-radius: 6px; background: #f1f5f9; border: 1px solid #cbd5e1; cursor: pointer; font-size: 12px; color: #334155; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="ri-eye-line"></i> Lihat
                                        </button>
                                    </td>
                                    <td>
                                        <select class="status-dropdown" data-ticket-id="<?= $ticket['id'] ?>" onchange="updateStatus(this)" style="padding: 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 600; outline: none; cursor: pointer; <?php 
                                            if ($ticket['status'] === 'Buka') echo 'color: var(--accent-green); background: #dcfce7;'; 
                                            elseif (strtolower($ticket['status']) === 'proses') echo 'color: #d97706; background: #fef3c7;';
                                            else echo 'color: #16a34a; background: #dcfce7;';
                                        ?>">
                                            <option value="Buka" <?= $ticket['status'] === 'Buka' ? 'selected' : '' ?> style="color: var(--accent-green); font-weight: 600;">Diterima</option>
                                            <option value="Proses" <?= strtolower($ticket['status']) === 'proses' ? 'selected' : '' ?> style="color: #d97706; font-weight: 600;">Proses</option>
                                            <option value="Selesai" <?= strtolower($ticket['status']) === 'selesai' ? 'selected' : '' ?> style="color: #16a34a; font-weight: 600;">Selesai</option>
                                        </select>
                                        
                                        <div class="sla-container" id="sla-container-<?= $ticket['id'] ?>" style="margin-top: 8px; <?= strtolower($ticket['status']) === 'proses' ? 'display: block;' : 'display: none;' ?>">
                                            <select class="sla-dropdown" data-ticket-id="<?= $ticket['id'] ?>" onchange="updateSla(this)" style="padding: 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; color: #334155; outline: none; cursor: pointer; background: #fff;">
                                                <option value="" disabled <?= empty($ticket['sla']) ? 'selected' : '' ?>>Pilih SLA...</option>
                                                <option value="15 Menit" <?= ($ticket['sla'] ?? '') === '15 Menit' ? 'selected' : '' ?>>15 Menit</option>
                                                <option value="30 Menit" <?= ($ticket['sla'] ?? '') === '30 Menit' ? 'selected' : '' ?>>30 Menit</option>
                                                <option value="1 Jam" <?= ($ticket['sla'] ?? '') === '1 Jam' ? 'selected' : '' ?>>1 Jam</option>
                                                <option value="3 Jam" <?= ($ticket['sla'] ?? '') === '3 Jam' ? 'selected' : '' ?>>3 Jam</option>
                                                <option value="6 Jam" <?= ($ticket['sla'] ?? '') === '6 Jam' ? 'selected' : '' ?>>6 Jam</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td><?= date('d F Y H:i', strtotime($ticket['updated_at'] ?? $ticket['created_at'])) ?> Wib</td>
                                    <td>
                                        <?php if (strtolower($ticket['status']) === 'selesai'): ?>
                                            <button onclick="deleteTicket(<?= $ticket['id'] ?>)" class="btn-delete" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 6px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Hapus Tiket">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-secondary);">
                                    <i class="ri-inbox-line" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                    Belum ada tiket yang masuk.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Deskripsi -->
    <div id="descModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: white; padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px;"><i class="ri-file-text-line" style="color: var(--accent-blue);"></i> Deskripsi dan Gambar</h3>
                <button onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
            </div>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <p id="modalDescContent" style="color: #475569; font-size: 14px; line-height: 1.6; white-space: pre-wrap; margin: 0;"></p>
                <div id="modalAttachmentContainer" style="margin-top: 15px; display: none; text-align: center;">
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin-bottom: 15px;">
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 10px; text-align: left;"><strong>Lampiran Gambar:</strong></p>
                    <img id="modalAttachmentImage" src="" alt="Lampiran" style="max-width: 100%; border-radius: 8px; border: 1px solid #cbd5e1;">
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDescription(btn) {
            const desc = btn.getAttribute('data-desc');
            const attach = btn.getAttribute('data-attach');
            
            document.getElementById('modalDescContent').textContent = desc || 'Tidak ada deskripsi yang diberikan.';
            
            const attachContainer = document.getElementById('modalAttachmentContainer');
            const attachImg = document.getElementById('modalAttachmentImage');
            
            if (attach) {
                attachImg.src = 'uploads/' + attach;
                attachContainer.style.display = 'block';
            } else {
                attachContainer.style.display = 'none';
                attachImg.src = '';
            }
            
            document.getElementById('descModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('descModal').style.display = 'none';
        }
        
        // Tutup modal jika user mengklik area luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('descModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function updateStatus(selectElement) {
            const ticketId = selectElement.getAttribute('data-ticket-id');
            const newStatus = selectElement.value;
            
            // Update colors dynamically based on selection
            if (newStatus === 'Buka') {
                selectElement.style.color = 'var(--accent-green)';
                selectElement.style.background = '#dcfce7';
            } else if (newStatus === 'Proses') {
                selectElement.style.color = '#d97706';
                selectElement.style.background = '#fef3c7';
            } else {
                selectElement.style.color = '#16a34a';
                selectElement.style.background = '#dcfce7';
            }

            // Toggle SLA container visibility
            const slaContainer = document.getElementById('sla-container-' + ticketId);
            if (slaContainer) {
                if (newStatus === 'Proses') {
                    slaContainer.style.display = 'block';
                } else {
                    slaContainer.style.display = 'none';
                }
            }

            const formData = new FormData();
            formData.append('ticket_id', ticketId);
            formData.append('status', newStatus);

            fetch('update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui status: ' + (data.message || 'Error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghubungi server.');
            });
        }

        function updateSla(selectElement) {
            const ticketId = selectElement.getAttribute('data-ticket-id');
            const newSla = selectElement.value;

            const formData = new FormData();
            formData.append('ticket_id', ticketId);
            formData.append('sla', newSla);

            fetch('update_sla.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui SLA: ' + (data.message || 'Error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghubungi server.');
            });
        }

        function deleteTicket(ticketId) {
            if (confirm('Apakah Anda yakin ingin menghapus tiket ini? Tindakan ini tidak dapat dibatalkan.')) {
                const formData = new FormData();
                formData.append('ticket_id', ticketId);

                fetch('delete_ticket.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page or remove the row from the table
                        location.reload();
                    } else {
                        alert('Gagal menghapus tiket: ' + (data.message || 'Error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghubungi server.');
                });
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
