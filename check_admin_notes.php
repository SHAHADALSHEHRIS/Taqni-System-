<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<h2>فحص وتحديث جدول الطلبات</h2>';

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo '<p style="color: red;">فشل الاتصال بقاعدة البيانات</p>';
        exit;
    }
    
    // فحص بنية جدول requests
    echo '<h3>بنية جدول requests:</h3>';
    $structure = $conn->query("DESCRIBE requests");
    echo '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
    echo '<tr><th style="padding: 8px; background: #f8f9fa;">العمود</th><th style="padding: 8px; background: #f8f9fa;">النوع</th><th style="padding: 8px; background: #f8f9fa;">Null</th><th style="padding: 8px; background: #f8f9fa;">Key</th><th style="padding: 8px; background: #f8f9fa;">Default</th></tr>';
    
    $hasAdminNotes = false;
    while ($row = $structure->fetch()) {
        if ($row['Field'] === 'admin_notes') {
            $hasAdminNotes = true;
        }
        echo '<tr>';
        echo '<td style="padding: 8px;">' . $row['Field'] . '</td>';
        echo '<td style="padding: 8px;">' . $row['Type'] . '</td>';
        echo '<td style="padding: 8px;">' . $row['Null'] . '</td>';
        echo '<td style="padding: 8px;">' . $row['Key'] . '</td>';
        echo '<td style="padding: 8px;">' . ($row['Default'] ?? 'NULL') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // إضافة عمود admin_notes إذا لم يكن موجوداً
    if (!$hasAdminNotes) {
        echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404; margin: 10px 0;">';
        echo '<h3>⚠️ عمود admin_notes غير موجود</h3>';
        echo '<p>جاري إضافة العمود...</p>';
        echo '</div>';
        
        $alter_sql = "ALTER TABLE requests ADD COLUMN admin_notes TEXT NULL AFTER description";
        if ($conn->exec($alter_sql)) {
            echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0;">';
            echo '<h3>✅ تم إضافة عمود admin_notes بنجاح</h3>';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin: 10px 0;">';
            echo '<h3>❌ فشل في إضافة عمود admin_notes</h3>';
            echo '</div>';
        }
    } else {
        echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0;">';
        echo '<h3>✅ عمود admin_notes موجود</h3>';
        echo '</div>';
    }
    
    // إضافة عمود user_name للعرض
    echo '<h3>إضافة بيانات العرض:</h3>';
    $view_sql = "
        SELECT r.*, u.full_name as user_name, u.username, u.email
        FROM requests r
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ";
    
    $view_stmt = $conn->query($view_sql);
    if ($view_stmt) {
        echo '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
        echo '<tr>';
        echo '<th style="padding: 8px; background: #667eea; color: white;">ID</th>';
        echo '<th style="padding: 8px; background: #667eea; color: white;">الموضوع</th>';
        echo '<th style="padding: 8px; background: #667eea; color: white;">العميل</th>';
        echo '<th style="padding: 8px; background: #667eea; color: white;">الحالة</th>';
        echo '<th style="padding: 8px; background: #667eea; color: white;">الملاحظات الإدارية</th>';
        echo '</tr>';
        
        while ($row = $view_stmt->fetch()) {
            echo '<tr>';
            echo '<td style="padding: 8px; text-align: center;">' . $row['id'] . '</td>';
            echo '<td style="padding: 8px;">' . $row['subject'] . '</td>';
            echo '<td style="padding: 8px;">' . ($row['user_name'] ?? $row['username'] ?? 'غير محدد') . '</td>';
            echo '<td style="padding: 8px; text-align: center;">';
            
            $status_colors = [
                'pending' => '#ffc107',
                'in_progress' => '#17a2b8',
                'completed' => '#28a745',
                'rejected' => '#dc3545'
            ];
            $status_text = [
                'pending' => 'قيد الانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل',
                'rejected' => 'مرفوض'
            ];
            
            $color = $status_colors[$row['status']] ?? '#6c757d';
            $text = $status_text[$row['status']] ?? $row['status'];
            
            echo '<span style="background: ' . $color . '; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">';
            echo $text;
            echo '</span>';
            echo '</td>';
            echo '<td style="padding: 8px; max-width: 200px; font-size: 0.9rem;">' . ($row['admin_notes'] ?? '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
} catch (Exception $e) {
    echo '<p style="color: red;">خطأ: ' . $e->getMessage() . '</p>';
}
?>

<div style="background: #e7f3ff; padding: 15px; border-radius: 5px; color: #004085; margin: 20px 0;">
    <h3>📋 الآن يمكنك:</h3>
    <ol>
        <li><a href="admin.html" target="_blank">فتح صفحة الإدارة المحدثة</a></li>
        <li>الانتقال إلى قسم "الطلبات" من القائمة الجانبية</li>
        <li>تعديل حالة الطلبات وإضافة الملاحظات</li>
        <li>مشاهدة الجدول المحسن بنفس تصميم موقعك</li>
    </ol>
</div>