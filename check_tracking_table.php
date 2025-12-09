<?php
$pdo = new PDO('mysql:host=localhost;dbname=shahad_clean_db;charset=utf8', 'root', '');

echo "هيكل جدول request_tracking:\n";
$stmt = $pdo->query('DESCRIBE request_tracking');
$columns = $stmt->fetchAll();
foreach($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}
?>