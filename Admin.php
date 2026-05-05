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

// Fetch all tickets for Admin
try {
    $stmt = $pdo->query("SELECT * FROM tickets ORDER BY created_at DESC");
    $tickets = $stmt->fetchAll();
    
    // Calculate stats
    $totalTickets = count($tickets);
    $bukaCount = 0;
    $tertundaCount = 0;
    foreach ($tickets as $t) {
        if ($t['status'] === 'Buka') $bukaCount++;
        if (strtolower($t['status']) === 'proses') $tertundaCount++;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
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

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                
                <!-- Last Tasks Table -->
                <!-- Tiket Terbaru removed as per request -->

                <!-- Report & Chart Card -->
                <div class="bottom-row">
                    <div class="card chart-card" style="width: 100%;">
                        <div class="card-header chart-header" style="flex-wrap: wrap; gap: 12px;">
                            <div>
                                <h3>Laporan Tiket Selesai</h3>
                                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">Grafik tiket yang sudah diselesaikan per bulan</p>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <!-- Month Selector -->
                                <select id="reportMonth" onchange="loadReport()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; color: #334155; background: #f8fafc; outline: none; cursor: pointer;">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5" selected>Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <!-- Year Selector -->
                                <select id="reportYear" onchange="loadReport()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; color: #334155; background: #f8fafc; outline: none; cursor: pointer;">
                                    <?php
                                    $currentYear = intval(date('Y'));
                                    for ($y = $currentYear; $y >= $currentYear - 3; $y--) {
                                        $sel = $y === $currentYear ? 'selected' : '';
                                        echo "<option value=\"$y\" $sel>$y</option>";
                                    }
                                    ?>
                                </select>
                                <!-- Export Button -->
                                <button id="exportBtn" onclick="exportReport()" style="display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; border: none; background: #16a34a; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                    <i class="ri-file-excel-2-line"></i> Export Excel
                                </button>
                            </div>
                        </div>

                        <!-- Summary Stats for selected month -->
                        <div id="reportStats" style="display: flex; gap: 16px; padding: 0 20px 16px; flex-wrap: wrap;">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 20px; flex: 1; min-width: 140px;">
                                <div style="font-size: 11px; color: #16a34a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Selesai</div>
                                <div id="statTotal" style="font-size: 26px; font-weight: 700; color: #15803d; margin-top: 4px;">0</div>
                            </div>
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 20px; flex: 1; min-width: 140px;">
                                <div style="font-size: 11px; color: #2563eb; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Divisi Terbanyak</div>
                                <div id="statTopDiv" style="font-size: 18px; font-weight: 700; color: #1d4ed8; margin-top: 4px;">-</div>
                            </div>
                            <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 12px 20px; flex: 1; min-width: 140px;">
                                <div style="font-size: 11px; color: #7c3aed; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Rata-rata/Hari</div>
                                <div id="statAvg" style="font-size: 26px; font-weight: 700; color: #6d28d9; margin-top: 4px;">0</div>
                            </div>
                        </div>

                        <!-- Chart Tabs -->
                        <div style="display: flex; gap: 0; padding: 0 20px 12px;">
                            <button id="tabDaily" onclick="switchTab('daily')" style="padding: 6px 16px; border-radius: 6px 0 0 6px; border: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; cursor: pointer; background: #1e293b; color: white;">Harian</button>
                            <button id="tabMonthly" onclick="switchTab('monthly')" style="padding: 6px 16px; border-radius: 0 6px 6px 0; border: 1px solid #e2e8f0; border-left: none; font-size: 12px; font-weight: 600; cursor: pointer; background: #f8fafc; color: #64748b;">Bulanan</button>
                        </div>

                        <div class="chart-container" style="padding: 0 20px 20px; height: 280px;">
                            <canvas id="reportChart"></canvas>
                        </div>

                        <!-- Division Breakdown -->
                        <div id="divisionBreakdown" style="padding: 0 20px 20px;">
                            <div style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 10px;">Rincian per Divisi</div>
                            <div id="divisionList" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // --- Report Chart Logic ---
        let reportChart = null;
        let currentTab  = 'daily';
        let lastData    = null;

        // Auto-set current month in the select
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('reportMonth').value = '<?= intval(date('m')) ?>';
            document.getElementById('reportYear').value  = '<?= intval(date('Y')) ?>';
            loadReport();

            // Flatpickr for topbar date
            flatpickr("#datePicker", {
                dateFormat: "l, jS F",
                defaultDate: "today",
                onChange: function(d, dateStr) {
                    document.getElementById('currentDate').textContent = dateStr;
                }
            });
            document.getElementById('currentDate').textContent =
                new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
        });

        function loadReport() {
            const month = document.getElementById('reportMonth').value;
            const year  = document.getElementById('reportYear').value;

            fetch(`get_report_data.php?month=${month}&year=${year}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) { console.error(data.message); return; }
                    lastData = data;
                    updateStats(data);
                    renderChart(data, currentTab);
                    renderDivisions(data.division);
                })
                .catch(e => console.error(e));
        }

        function updateStats(data) {
            const total   = data.daily.data.reduce((a, b) => a + b, 0);
            const topDiv  = data.division.length > 0 ? data.division[0].divisi : '-';
            const avg     = data.daysInMonth > 0 ? (total / data.daysInMonth).toFixed(1) : '0';
            document.getElementById('statTotal').textContent  = total;
            document.getElementById('statTopDiv').textContent = topDiv;
            document.getElementById('statAvg').textContent    = avg;
        }

        function renderChart(data, tab) {
            const ctx = document.getElementById('reportChart').getContext('2d');

            const labels = tab === 'daily'   ? data.daily.labels   : data.monthly.labels;
            const values = tab === 'daily'   ? data.daily.data     : data.monthly.data;

            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(22, 163, 74, 0.35)');
            gradient.addColorStop(1, 'rgba(22, 163, 74, 0.03)');

            if (reportChart) { reportChart.destroy(); }

            reportChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tiket Selesai',
                        data: values,
                        backgroundColor: gradient,
                        borderColor: '#16a34a',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                title: ctx => tab === 'daily'
                                    ? `Tanggal ${ctx[0].label}`
                                    : ctx[0].label,
                                label: ctx => `${ctx.raw} tiket selesai`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#94a3b8',
                                font: { family: "'Inter', sans-serif", size: 11 }
                            },
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                font: { family: "'Inter', sans-serif", size: 11 },
                                maxRotation: 0
                            },
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        }

        function renderDivisions(divisions) {
            const list = document.getElementById('divisionList');
            list.innerHTML = '';
            if (!divisions || divisions.length === 0) {
                list.innerHTML = '<span style="font-size:13px;color:#94a3b8;">Tidak ada data untuk bulan ini.</span>';
                return;
            }
            const maxVal = Math.max(...divisions.map(d => parseInt(d.total)));
            const colors = ['#16a34a', '#2563eb', '#7c3aed', '#ea580c', '#0891b2'];
            divisions.forEach((div, i) => {
                const pct  = maxVal > 0 ? Math.round((parseInt(div.total) / maxVal) * 100) : 0;
                const color = colors[i % colors.length];
                list.innerHTML += `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="min-width:120px; font-size:13px; font-weight:500; color:#334155;">${div.divisi || 'Umum'}</div>
                        <div style="flex:1; background:#f1f5f9; border-radius:99px; height:8px; overflow:hidden;">
                            <div style="width:${pct}%; background:${color}; height:100%; border-radius:99px; transition: width 0.5s ease;"></div>
                        </div>
                        <div style="min-width:30px; font-size:13px; font-weight:700; color:${color};">${div.total}</div>
                    </div>`;
            });
        }

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tabDaily').style.background   = tab === 'daily'   ? '#1e293b' : '#f8fafc';
            document.getElementById('tabDaily').style.color        = tab === 'daily'   ? 'white'   : '#64748b';
            document.getElementById('tabMonthly').style.background = tab === 'monthly' ? '#1e293b' : '#f8fafc';
            document.getElementById('tabMonthly').style.color      = tab === 'monthly' ? 'white'   : '#64748b';
            if (lastData) renderChart(lastData, tab);
        }

        function exportReport() {
            const month = document.getElementById('reportMonth').value;
            const year  = document.getElementById('reportYear').value;
            window.location.href = `export_report.php?month=${month}&year=${year}`;
        }
    </script>
    <script src="script.js"></script>
</body>
</html>
