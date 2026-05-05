<?php
$userRole = $_SESSION['user']['role'] ?? 'user';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="logo" style="justify-content: center; padding: 0;">
        <img src="assets/fasremit.png" alt="Logo" style="width: 150px; height: auto; object-fit: contain;">
    </div>
    
    <nav class="sidebar-nav">
        <?php if ($userRole === 'admin' || $userRole === 'master' || $userRole === 'agent'): ?>
            <a href="Admin.php" class="nav-item <?php echo $currentPage == 'Admin.php' ? 'active' : ''; ?>">
                <i class="ri-dashboard-line"></i>
                <span>Ringkasan</span>
            </a>
            <a href="tickets.php" class="nav-item <?php echo $currentPage == 'tickets.php' ? 'active' : ''; ?>">
                <i class="ri-ticket-2-line"></i>
                <span>Semua Tiket</span>
            </a>
            <a href="assigned.php" class="nav-item <?php echo $currentPage == 'assigned.php' ? 'active' : ''; ?>">
                <i class="ri-user-follow-line"></i>
                <span>Tugas Saya</span>
            </a>
        <?php else: ?>
            <!-- Regular Staff / User -->
            <?php
            $ticketingLink = 'Operasional.php';
            if (strtolower($user['name'] ?? '') === 'finance') $ticketingLink = 'Finance.php';
            elseif (strtolower($user['name'] ?? '') === 'accounting') $ticketingLink = 'Accounting.php';
            ?>
            <a href="<?= $ticketingLink ?>" class="nav-item <?php echo in_array($currentPage, ['Operasional.php', 'Finance.php', 'Accounting.php']) ? 'active' : ''; ?>">
                <i class="ri-ticket-2-line"></i>
                <span>Ticketing</span>
            </a>
            <a href="track_ticket.php" class="nav-item <?php echo $currentPage == 'track_ticket.php' ? 'active' : ''; ?>">
                <i class="ri-search-eye-line"></i>
                <span>Cek Status Tiket</span>
            </a>
            <a href="asset_request.php" class="nav-item <?php echo $currentPage == 'asset_request.php' ? 'active' : ''; ?>">
                <i class="ri-computer-line"></i>
                <span>Pengambilan Aset</span>
            </a>
        <?php endif; ?>





        <?php if ($userRole === 'admin' || $userRole === 'master'): ?>
            <a href="settings.php" class="nav-item <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
                <i class="ri-settings-4-line"></i>
                <span>Pengaturan IT</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="user-profile">
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
        <div class="user-info">
            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>
</aside>
