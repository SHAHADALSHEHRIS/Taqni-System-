<?php
/**
 * إضافة جدول التقييمات إلى قاعدة البيانات
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    // إنشاء جدول التقييمات
    $sql = "CREATE TABLE IF NOT EXISTS ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
        UNIQUE KEY unique_rating (request_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✅ تم إنشاء جدول التقييمات بنجاح\n";
    
} catch (PDOException $e) {
    echo "❌ خطأ في إنشاء جدول التقييمات: " . $e->getMessage() . "\n";
}
?>