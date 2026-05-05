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
    $sla = $_POST['sla'] ?? '';

    if (empty($ticketId) || empty($sla)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE tickets SET sla = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$sla, $ticketId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
