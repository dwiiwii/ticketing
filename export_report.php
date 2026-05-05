<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}

// Only admin/master can export
if (($_SESSION['user']['role'] ?? 'user') === 'user') {
    die('Akses ditolak.');
}

$year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));

$monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
               7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

$monthLabel = $monthNames[$month] . ' ' . $year;

try {
    $stmt = $pdo->prepare("
        SELECT t.ticket_number, t.requester_name, u.name as divisi,
               t.category, t.subject, t.priority, t.sla,
               t.status, t.created_at, t.updated_at
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.status = 'Selesai'
          AND MONTH(t.updated_at) = ?
          AND YEAR(t.updated_at) = ?
        ORDER BY t.updated_at ASC
    ");
    $stmt->execute([$month, $year]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Set headers for Excel download
$filename = "Laporan_Tiket_Selesai_" . $monthLabel . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
    th { background: #1e293b; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
    td { padding: 7px; border: 1px solid #ddd; }
    .header-row { background: #f1f5f9; font-size: 14px; font-weight: bold; }
</style>
</head>
<body>
<table>
    <tr>
        <td colspan="10" class="header-row">Laporan Tiket IT Selesai — <?= htmlspecialchars($monthLabel) ?></td>
    </tr>
    <tr>
        <td colspan="10">Total Tiket: <?= count($tickets) ?></td>
    </tr>
    <tr></tr>
    <tr>
        <th>No.</th>
        <th>No. Ticket</th>
        <th>Nama Pengaju</th>
        <th>Divisi</th>
        <th>Kategori</th>
        <th>Subjek</th>
        <th>Prioritas</th>
        <th>SLA</th>
        <th>Tanggal Dibuat</th>
        <th>Tanggal Selesai</th>
    </tr>
    <?php $no = 1; foreach ($tickets as $t): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($t['ticket_number']) ?></td>
        <td><?= htmlspecialchars($t['requester_name']) ?></td>
        <td><?= htmlspecialchars($t['divisi'] ?? '-') ?></td>
        <td><?= htmlspecialchars(ucfirst($t['category'])) ?></td>
        <td><?= htmlspecialchars($t['subject']) ?></td>
        <td><?= htmlspecialchars($t['priority']) ?></td>
        <td><?= htmlspecialchars($t['sla'] ?? '-') ?></td>
        <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($tickets)): ?>
    <tr>
        <td colspan="10" style="text-align:center; color:#666;">Tidak ada tiket selesai pada bulan ini.</td>
    </tr>
    <?php endif; ?>
</table>
</body>
</html>
