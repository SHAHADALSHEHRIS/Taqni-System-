<?php
$pdo = new PDO('mysql:host=localhost;dbname=shahad_clean_db;charset=utf8', 'root', '');

// فحص الطلبات المكتملة
$stmt = $pdo->query('SELECT id, subject, status FROM requests WHERE status="completed" LIMIT 5');
$completed = $stmt->fetchAll();
echo "الطلبات المكتملة:\n";
foreach($completed as $req) {
    echo "- ID: {$req['id']}, الموضوع: {$req['subject']}, الحالة: {$req['status']}\n";
}

// فحص التقييمات الموجودة
echo "\nالتقييمات الموجودة:\n";
$stmt = $pdo->query('SELECT r.*, req.subject FROM ratings r JOIN requests req ON r.request_id = req.id');
$ratings = $stmt->fetchAll();
foreach($ratings as $rating) {
    echo "- طلب #{$rating['request_id']}: {$rating['subject']} - تقييم: {$rating['rating']}/5 - تعليق: {$rating['comment']}\n";
}
?>