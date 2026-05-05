<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = $_POST['ticket_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($ticketId) || empty($status)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit();
    }

    // In DB, 'Buka', 'Proses', 'Selesai'
    // Actually the user wants 'Proses' and 'Selesai'. Initial is 'Buka'.
    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $ticketId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
