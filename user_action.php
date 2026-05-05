<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is master/admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['master', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $name = $_POST['name'] ?? '';
            $pin = $_POST['pin'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            // Basic validation
            if (empty($name) || empty($pin)) {
                echo json_encode(['success' => false, 'message' => 'Nama dan PIN wajib diisi.']);
                exit();
            }
            
            // Check if PIN already exists (we use PIN as password)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE password = ?");
            $stmt->execute([$pin]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'PIN sudah digunakan oleh anggota lain.']);
                exit();
            }
            
            // Insert new user. We'll use a dummy email based on PIN since email was unique but now we use PIN.
            $email = "user_" . $pin . "@fasremit.com";
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $pin, $role]);
            
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil ditambahkan.']);
            
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'] ?? '';
            $pin = $_POST['pin'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            if (empty($id) || empty($name) || empty($pin)) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
                exit();
            }
            
            // Check if PIN is used by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE password = ? AND id != ?");
            $stmt->execute([$pin, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'PIN sudah digunakan oleh anggota lain.']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $pin, $role, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Perubahan berhasil disimpan.']);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan.']);
                exit();
            }
            
            // Prevent deleting yourself
            if ($id == $_SESSION['user']['id']) {
                echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus akun Anda sendiri.']);
                exit();
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
