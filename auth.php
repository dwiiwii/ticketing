<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE password = ? LIMIT 1");
        $stmt->execute([$pin]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'avatar' => !empty($user['avatar']) ? $user['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=random'
            ];
            
            // Redirect URL based on role and name
            $redirectUrl = 'Admin.php';
            if ($user['role'] === 'user') {
                if (strtolower($user['name']) === 'finance') $redirectUrl = 'Finance.php';
                elseif (strtolower($user['name']) === 'accounting') $redirectUrl = 'Accounting.php';
                else $redirectUrl = 'Operasional.php';
            }
            
            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirectUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'PIN Akses salah!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
