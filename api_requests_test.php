<?php
// ملف لمحاكاة بيانات الطلبات لاختبار النظام التفاعلي

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// بيانات تجريبية للطلبات
$sampleRequests = [
    [
        'id' => 1,
        'request_number' => 'REQ-2024-001',
        'user_name' => 'أحمد محمد علي',
        'phone' => '0123456789',
        'email' => 'ahmed@example.com',
        'department' => 'قسم تقنية المعلومات',
        'request_type' => 'صيانة أجهزة',
        'description' => 'صيانة جهاز كمبيوتر في مكتب المدير',
        'priority' => 'عالي',
        'status' => 'pending',
        'assigned_employee' => null,
        'notes' => 'طلب عاجل - جهاز المدير لا يعمل',
        'created_at' => '2024-01-15 09:30:00',
        'updated_at' => '2024-01-15 09:30:00',
        'completion_date' => null,
        'estimated_duration' => 2
    ],
    [
        'id' => 2,
        'request_number' => 'REQ-2024-002',
        'user_name' => 'فاطمة أحمد',
        'phone' => '0123456788',
        'email' => 'fatima@example.com',
        'department' => 'قسم المالية',
        'request_type' => 'تركيب برامج',
        'description' => 'تثبيت برنامج المحاسبة الجديد',
        'priority' => 'متوسط',
        'status' => 'in_progress',
        'assigned_employee' => 'خالد التقني',
        'notes' => 'تم البدء في التثبيت',
        'created_at' => '2024-01-14 14:20:00',
        'updated_at' => '2024-01-15 10:00:00',
        'completion_date' => null,
        'estimated_duration' => 3
    ],
    [
        'id' => 3,
        'request_number' => 'REQ-2024-003',
        'user_name' => 'سارة محمود',
        'phone' => '0123456787',
        'email' => 'sara@example.com',
        'department' => 'قسم الموارد البشرية',
        'request_type' => 'دعم تقني',
        'description' => 'مساعدة في استخدام نظام الحضور والانصراف',
        'priority' => 'منخفض',
        'status' => 'completed',
        'assigned_employee' => 'عمر الدعم',
        'notes' => 'تم شرح النظام بالكامل للموظف',
        'created_at' => '2024-01-13 11:15:00',
        'updated_at' => '2024-01-14 16:30:00',
        'completion_date' => '2024-01-14 16:30:00',
        'estimated_duration' => 1
    ],
    [
        'id' => 4,
        'request_number' => 'REQ-2024-004',
        'user_name' => 'محمد عبدالله',
        'phone' => '0123456786',
        'email' => 'mohammed@example.com',
        'department' => 'قسم المبيعات',
        'request_type' => 'تطوير نظام',
        'description' => 'تطوير تطبيق إدارة العملاء الجديد',
        'priority' => 'عالي',
        'status' => 'in_progress',
        'assigned_employee' => 'فريق التطوير',
        'notes' => 'العمل في المرحلة الثانية من التطوير',
        'created_at' => '2024-01-10 08:00:00',
        'updated_at' => '2024-01-15 14:20:00',
        'completion_date' => null,
        'estimated_duration' => 15
    ],
    [
        'id' => 5,
        'request_number' => 'REQ-2024-005',
        'user_name' => 'نورا حسن',
        'phone' => '0123456785',
        'email' => 'nora@example.com',
        'department' => 'قسم الجودة',
        'request_type' => 'صيانة أجهزة',
        'description' => 'صيانة طابعة الشبكة في قسم الجودة',
        'priority' => 'متوسط',
        'status' => 'pending',
        'assigned_employee' => null,
        'notes' => 'الطابعة لا تطبع الألوان بوضوح',
        'created_at' => '2024-01-15 13:45:00',
        'updated_at' => '2024-01-15 13:45:00',
        'completion_date' => null,
        'estimated_duration' => 1
    ],
    [
        'id' => 6,
        'request_number' => 'REQ-2024-006',
        'user_name' => 'عبدالرحمن يوسف',
        'phone' => '0123456784',
        'email' => 'abdulrahman@example.com',
        'department' => 'قسم التسويق',
        'request_type' => 'تركيب برامج',
        'description' => 'تثبيت برنامج التصميم الجرافيكي',
        'priority' => 'عالي',
        'status' => 'in_progress',
        'assigned_employee' => 'رامي التقني',
        'notes' => 'تم تثبيت البرنامج، جاري إعداد التراخيص',
        'created_at' => '2024-01-12 10:30:00',
        'updated_at' => '2024-01-15 11:15:00',
        'completion_date' => null,
        'estimated_duration' => 2
    ],
    [
        'id' => 7,
        'request_number' => 'REQ-2024-007',
        'user_name' => 'ليلى أحمد',
        'phone' => '0123456783',
        'email' => 'layla@example.com',
        'department' => 'قسم العلاقات العامة',
        'request_type' => 'دعم تقني',
        'description' => 'مساعدة في إعداد البريد الإلكتروني الجديد',
        'priority' => 'منخفض',
        'status' => 'completed',
        'assigned_employee' => 'علي الدعم',
        'notes' => 'تم إعداد البريد الإلكتروني وتدريب الموظف',
        'created_at' => '2024-01-11 09:00:00',
        'updated_at' => '2024-01-12 15:00:00',
        'completion_date' => '2024-01-12 15:00:00',
        'estimated_duration' => 1
    ],
    [
        'id' => 8,
        'request_number' => 'REQ-2024-008',
        'user_name' => 'حسام محمد',
        'phone' => '0123456782',
        'email' => 'hussam@example.com',
        'department' => 'قسم اللوجستيات',
        'request_type' => 'تطوير نظام',
        'description' => 'تطوير نظام إدارة المخازن',
        'priority' => 'عالي',
        'status' => 'in_progress',
        'assigned_employee' => 'فريق التطوير',
        'notes' => 'المرحلة الأولى من التطوير مكتملة',
        'created_at' => '2024-01-08 12:00:00',
        'updated_at' => '2024-01-15 16:00:00',
        'completion_date' => null,
        'estimated_duration' => 20
    ],
    [
        'id' => 9,
        'request_number' => 'REQ-2024-009',
        'user_name' => 'ريم عبدالله',
        'phone' => '0123456781',
        'email' => 'reem@example.com',
        'department' => 'قسم الشؤون الإدارية',
        'request_type' => 'صيانة أجهزة',
        'description' => 'صيانة جهاز الفاكس في الإدارة',
        'priority' => 'منخفض',
        'status' => 'pending',
        'assigned_employee' => null,
        'notes' => 'الجهاز لا يرسل الفاكسات',
        'created_at' => '2024-01-15 15:30:00',
        'updated_at' => '2024-01-15 15:30:00',
        'completion_date' => null,
        'estimated_duration' => 1
    ],
    [
        'id' => 10,
        'request_number' => 'REQ-2024-010',
        'user_name' => 'طارق حسن',
        'phone' => '0123456780',
        'email' => 'tarek@example.com',
        'department' => 'قسم الأمن',
        'request_type' => 'تركيب برامج',
        'description' => 'تثبيت نظام مراقبة الأمان الجديد',
        'priority' => 'عالي',
        'status' => 'completed',
        'assigned_employee' => 'فريق الأمان',
        'notes' => 'تم تثبيت النظام بالكامل وتدريب الفريق',
        'created_at' => '2024-01-05 07:00:00',
        'updated_at' => '2024-01-10 18:00:00',
        'completion_date' => '2024-01-10 18:00:00',
        'estimated_duration' => 8
    ]
];

// التعامل مع طلبات GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $department = isset($_GET['department']) ? $_GET['department'] : null;
    $priority = isset($_GET['priority']) ? $_GET['priority'] : null;
    
    $filteredRequests = $sampleRequests;
    
    // فلترة حسب الحالة
    if ($status && $status !== 'all') {
        $filteredRequests = array_filter($filteredRequests, function($request) use ($status) {
            return $request['status'] === $status;
        });
    }
    
    // فلترة حسب البحث
    if ($search) {
        $filteredRequests = array_filter($filteredRequests, function($request) use ($search) {
            return stripos($request['user_name'], $search) !== false ||
                   stripos($request['description'], $search) !== false ||
                   stripos($request['request_number'], $search) !== false ||
                   stripos($request['department'], $search) !== false;
        });
    }
    
    // فلترة حسب القسم
    if ($department && $department !== 'all') {
        $filteredRequests = array_filter($filteredRequests, function($request) use ($department) {
            return stripos($request['department'], $department) !== false;
        });
    }
    
    // فلترة حسب الأولوية
    if ($priority && $priority !== 'all') {
        $filteredRequests = array_filter($filteredRequests, function($request) use ($priority) {
            return $request['priority'] === $priority;
        });
    }
    
    // إعادة ترقيم المصفوفة
    $filteredRequests = array_values($filteredRequests);
    
    // حساب الإحصائيات
    $stats = [
        'total' => count($sampleRequests),
        'pending' => count(array_filter($sampleRequests, function($r) { return $r['status'] === 'pending'; })),
        'in_progress' => count(array_filter($sampleRequests, function($r) { return $r['status'] === 'in_progress'; })),
        'completed' => count(array_filter($sampleRequests, function($r) { return $r['status'] === 'completed'; }))
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $filteredRequests,
        'stats' => $stats,
        'filtered_count' => count($filteredRequests),
        'total_count' => count($sampleRequests),
        'filters_applied' => [
            'status' => $status,
            'search' => $search,
            'department' => $department,
            'priority' => $priority
        ]
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>