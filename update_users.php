<?php
require_once 'db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password), role=VALUES(role)");
    
    // Admin (menuju ke admin.php karena role = admin)
    $stmt->execute(['Admin', 'admin@fasremit.com', '123', 'admin']);
    
    // Operasional (menuju ke Operasional.php karena role = user & name = Operasional)
    $stmt->execute(['Operasional', 'operasional@fasremit.com', '345', 'user']);
    
    // Accounting (menuju ke Accounting.php karena role = user & name = Accounting)
    $stmt->execute(['Accounting', 'accounting@fasremit.com', '678', 'user']);
    
    // Finance (menuju ke Finance.php karena role = user & name = Finance)
    $stmt->execute(['Finance', 'finance@fasremit.com', '901', 'user']);
    
    echo "Sukses: 4 User berhasil dibuat!\n";
    echo "- Admin PIN: 123\n";
    echo "- Operasional PIN: 345\n";
    echo "- Accounting PIN: 678\n";
    echo "- Finance PIN: 901\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
