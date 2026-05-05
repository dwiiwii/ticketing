<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
require_once 'db.php';
$user = $_SESSION['user'];

// Lazy setup of users table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        avatar VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// Fetch users
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $allUsers = $stmt->fetchAll();
    
    // Seed initial users if table is empty
    if (count($allUsers) === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin Fasremit', 'admin@fasremit.com', '123456', 'master']);
        $stmt->execute(['Operasional', 'operasional@fasremit.com', '654321', 'operasional']);
        $stmt->execute(['Finance', 'finance@fasremit.com', '111111', 'finance']);
        $stmt->execute(['Accounting', 'accounting@fasremit.com', '222222', 'accounting']);
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
        $allUsers = $stmt->fetchAll();
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
                    <h2>Pengaturan IT</h2>
                    <p>Kelola konfigurasi helpdesk dan anggota tim.</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card" style="grid-column: 1 / -1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Anggota Aktif</h3>
                        <button class="btn-primary" onclick="openAddModal()"><i class="ri-user-add-line"></i> Tambah Anggota</button>
                    </div>
                    <table class="tasks-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>PIN</th>
                                <th>Peran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <?php 
                                    $roleBadge = '';
                                    if ($u['role'] === 'admin' || $u['role'] === 'master') {
                                        $roleBadge = '<span style="background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Admin</span>';
                                    } else {
                                        $roleBadge = '<span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">User</span>';
                                    }
                                    
                                    $avatar = !empty($u['avatar']) ? $u['avatar'] : 'assets/avatar2.png';
                                    if ($u['role'] === 'admin' || $u['role'] === 'master') $avatar = 'assets/fasremit.png';
                                ?>
                            <tr>
                                <td><div class="admin-cell"><img src="<?= htmlspecialchars($avatar) ?>" alt=""> <?= htmlspecialchars($u['name']) ?></div></td>
                                <td><?= htmlspecialchars($u['password']) ?></td>
                                <td><?= $roleBadge ?></td>
                                <td>
                                    <select class="action-dropdown" onchange="handleAction(this, '<?= $u['id'] ?>', '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= htmlspecialchars(addslashes($u['password'])) ?>', '<?= $u['role'] ?>')" style="padding: 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; outline: none; cursor: pointer;">
                                        <option value="">Pilih Aksi</option>
                                        <option value="edit">Edit</option>
                                        <option value="delete">Hapus</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Tambah Anggota -->
            <div id="addMemberModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
                <div style="background: white; padding: 24px; border-radius: 12px; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                    <h3 style="margin-bottom: 16px;">Tambah Anggota Aktif</h3>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">Nama</label>
                        <input type="text" placeholder="Masukkan nama" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">PIN</label>
                        <input type="password" placeholder="Masukkan PIN (6 digit)" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                    <div class="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">Peran</label>
                        <select id="addRole" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button onclick="closeModals()" style="padding: 10px 16px; border: 1px solid var(--border-color); background: white; border-radius: 8px; cursor: pointer;">Batal</button>
                        <button onclick="saveNewMember()" style="padding: 10px 16px; border: none; background: var(--accent-blue); color: white; border-radius: 8px; cursor: pointer;">Simpan</button>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Anggota -->
            <div id="editMemberModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
                <div style="background: white; padding: 24px; border-radius: 12px; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                    <h3 style="margin-bottom: 16px;">Edit Anggota Aktif</h3>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">Nama</label>
                        <input type="text" id="editName" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">PIN</label>
                        <input type="password" id="editPin" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                    <div class="margin-bottom: 20px;">
                        <input type="hidden" id="editId">
                        <label style="display: block; margin-bottom: 6px; font-size: 14px; color: var(--text-secondary);">Peran</label>
                        <select id="editRole" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button onclick="closeModals()" style="padding: 10px 16px; border: 1px solid var(--border-color); background: white; border-radius: 8px; cursor: pointer;">Batal</button>
                        <button onclick="saveEditMember()" style="padding: 10px 16px; border: none; background: var(--accent-blue); color: white; border-radius: 8px; cursor: pointer;">Simpan Perubahan</button>
                    </div>
                </div>
            </div>

            <script>
                function openAddModal() {
                    document.getElementById('addMemberModal').style.display = 'flex';
                }

                function openEditModal(id, name, pin, role) {
                    document.getElementById('editId').value = id;
                    document.getElementById('editName').value = name;
                    document.getElementById('editPin').value = pin;
                    document.getElementById('editRole').value = role;
                    document.getElementById('editMemberModal').style.display = 'flex';
                }

                function closeModals() {
                    document.getElementById('addMemberModal').style.display = 'none';
                    document.getElementById('editMemberModal').style.display = 'none';
                }

                function handleAction(selectObj, id, name, pin, role) {
                    const action = selectObj.value;
                    selectObj.value = ""; // Reset dropdown
                    
                    if (action === 'edit') {
                        openEditModal(id, name, pin, role);
                    } else if (action === 'delete') {
                        if(confirm('Apakah Anda yakin ingin menghapus ' + name + '?')) {
                            const formData = new FormData();
                            formData.append('action', 'delete');
                            formData.append('id', id);
                            
                            fetch('user_action.php', { method: 'POST', body: formData })
                            .then(r => r.json()).then(res => {
                                alert(res.message);
                                if(res.success) location.reload();
                            });
                        }
                    }
                }

                function saveNewMember() {
                    const name = document.querySelector('#addMemberModal input[type="text"]').value;
                    const pin = document.querySelector('#addMemberModal input[type="password"]').value;
                    const role = document.getElementById('addRole').value;

                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('name', name);
                    formData.append('pin', pin);
                    formData.append('role', role);

                    fetch('user_action.php', { method: 'POST', body: formData })
                    .then(r => r.json()).then(res => {
                        alert(res.message);
                        if(res.success) location.reload();
                    });
                }

                function saveEditMember() {
                    const id = document.getElementById('editId').value;
                    const name = document.getElementById('editName').value;
                    const pin = document.getElementById('editPin').value;
                    const role = document.getElementById('editRole').value;

                    const formData = new FormData();
                    formData.append('action', 'edit');
                    formData.append('id', id);
                    formData.append('name', name);
                    formData.append('pin', pin);
                    formData.append('role', role);

                    fetch('user_action.php', { method: 'POST', body: formData })
                    .then(r => r.json()).then(res => {
                        alert(res.message);
                        if(res.success) location.reload();
                    });
                }
            </script>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
