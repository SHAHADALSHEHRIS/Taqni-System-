<?php
/**
 * إضافة بيانات تجريبية لاختبار النظام
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = new PDO("mysql:host=localhost;dbname=shahad_clean_db;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // إضافة طلبات تجريبية
    $requests = [
        [
            'user_id' => 2,
            'request_type' => 'technical',
            'subject' => 'صيانة الجهاز',
            'description' => 'طلب صيانة للجهاز الرئيسي في القسم',
            'priority' => 'high',
            'status' => 'completed'
        ],
        [
            'user_id' => 2,
            'request_type' => 'administrative',
            'subject' => 'طلب إجازة',
            'description' => 'طلب إجازة اعتيادية لمدة 3 أيام',
            'priority' => 'medium',
            'status' => 'pending'
        ],
        [
            'user_id' => 2,
            'request_type' => 'financial',
            'subject' => 'طلب سلفة',
            'description' => 'طلب سلفة مالية لضرورة عاجلة',
            'priority' => 'high',
            'status' => 'in_progress'
        ],
        [
            'user_id' => 2,
            'request_type' => 'other',
            'subject' => 'طلب نقل',
            'description' => 'طلب نقل إلى فرع آخر',
            'priority' => 'low',
            'status' => 'rejected'
        ],
        [
            'user_id' => 2,
            'request_type' => 'technical',
            'subject' => 'تحديث النظام',
            'description' => 'طلب تحديث نظام الحاسوب',
            'priority' => 'medium',
            'status' => 'completed'
        ]
    ];

    $insertedIds = [];
    $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, description, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($requests as $request) {
        $stmt->execute([
            $request['user_id'],
            $request['request_type'],
            $request['subject'],
            $request['description'],
            $request['priority'],
            $request['status']
        ]);
        $insertedIds[] = $conn->lastInsertId();
    }

    // إضافة تقييمات تجريبية للطلبات المكتملة
    $ratings = [
        [
            'request_id' => $insertedIds[0],
            'user_id' => 2,
            'quality_rate' => 5,
            'speed_rate' => 4,
            'comments' => 'خدمة ممتازة وسريعة'
        ],
        [
            'request_id' => $insertedIds[4],
            'user_id' => 2,
            'quality_rate' => 4,
            'speed_rate' => 5,
            'comments' => 'تحديث سريع ومفيد'
        ],
        [
            'request_id' => $insertedIds[1],
            'user_id' => 2,
            'quality_rate' => 3,
            'speed_rate' => 3,
            'comments' => 'خدمة جيدة'
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO request_ratings (request_id, user_id, quality_rate, speed_rate, comments, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    
    foreach ($ratings as $rating) {
        $stmt->execute([
            $rating['request_id'],
            $rating['user_id'],
            $rating['quality_rate'],
            $rating['speed_rate'],
            $rating['comments']
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'تم إضافة البيانات التجريبية بنجاح',
        'inserted_requests' => count($insertedIds),
        'inserted_ratings' => count($ratings),
        'request_ids' => $insertedIds
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ]);
}
?>