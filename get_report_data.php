<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));

try {
    // Daily data for the selected month (tickets completed = 'Selesai')
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $stmt = $pdo->prepare("
        SELECT DAY(updated_at) as day, COUNT(*) as total
        FROM tickets
        WHERE status = 'Selesai'
          AND MONTH(updated_at) = ?
          AND YEAR(updated_at) = ?
        GROUP BY DAY(updated_at)
        ORDER BY day ASC
    ");
    $stmt->execute([$month, $year]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // day => total

    $labels = [];
    $data   = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $labels[] = $d;
        $data[]   = isset($rows[$d]) ? intval($rows[$d]) : 0;
    }

    // Monthly summary for the selected year (all 12 months)
    $stmtMonthly = $pdo->prepare("
        SELECT MONTH(updated_at) as month, COUNT(*) as total
        FROM tickets
        WHERE status = 'Selesai'
          AND YEAR(updated_at) = ?
        GROUP BY MONTH(updated_at)
        ORDER BY month ASC
    ");
    $stmtMonthly->execute([$year]);
    $monthlyRows = $stmtMonthly->fetchAll(PDO::FETCH_KEY_PAIR);

    $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $monthlyLabels = [];
    $monthlyData   = [];
    for ($m = 1; $m <= 12; $m++) {
        $monthlyLabels[] = $monthNames[$m - 1];
        $monthlyData[]   = isset($monthlyRows[$m]) ? intval($monthlyRows[$m]) : 0;
    }

    // Per-division breakdown for the selected month
    $stmtDiv = $pdo->prepare("
        SELECT u.name as divisi, COUNT(*) as total
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.status = 'Selesai'
          AND MONTH(t.updated_at) = ?
          AND YEAR(t.updated_at) = ?
        GROUP BY u.name
        ORDER BY total DESC
    ");
    $stmtDiv->execute([$month, $year]);
    $divisionRows = $stmtDiv->fetchAll();

    echo json_encode([
        'success'        => true,
        'daily'          => ['labels' => $labels, 'data' => $data],
        'monthly'        => ['labels' => $monthlyLabels, 'data' => $monthlyData],
        'division'       => $divisionRows,
        'daysInMonth'    => $daysInMonth,
        'selectedMonth'  => $month,
        'selectedYear'   => $year,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
