<?php
/**
 * دالة حساب مدة الطلبات
 * يمكن استخدامها في أي ملف PHP
 */

/**
 * حساب مدة الطلب بالأيام
 * @param string $created_date تاريخ إنشاء الطلب
 * @param string $status حالة الطلب
 * @param string $updated_date تاريخ آخر تحديث (اختياري)
 * @return array معلومات المدة
 */
function calculateRequestDuration($created_date, $status, $updated_date = null) {
    try {
        $created = new DateTime($created_date);
        
        // إذا كانت الحالة مكتملة ويوجد تاريخ تحديث، استخدمه
        if ($status === 'completed' && !empty($updated_date) && $updated_date !== '0000-00-00 00:00:00') {
            $end_date = new DateTime($updated_date);
        } else {
            // إذا لم تكن مكتملة أو لا يوجد تاريخ تحديث، استخدم التاريخ الحالي
            $end_date = new DateTime();
        }
        
        $diff = $created->diff($end_date);
        $days = $diff->days;
        
        // تحديد النص المناسب حسب عدد الأيام
        if ($days == 0) {
            $duration_text = "اليوم";
        } elseif ($days == 1) {
            $duration_text = "يوم واحد";
        } elseif ($days == 2) {
            $duration_text = "يومان";
        } elseif ($days >= 3 && $days <= 10) {
            $duration_text = $days . " أيام";
        } else {
            $duration_text = $days . " يوم";
        }
        
        // تحديد لون المدة حسب الحالة والمدة
        $color = '#666';
        if ($status === 'completed') {
            if ($days <= 3) {
                $color = '#27ae60'; // أخضر للسرعة
            } elseif ($days <= 7) {
                $color = '#f39c12'; // برتقالي للمتوسط
            } else {
                $color = '#e74c3c'; // أحمر للبطء
            }
        } else {
            if ($days <= 7) {
                $color = '#3498db'; // أزرق للطبيعي
            } elseif ($days <= 14) {
                $color = '#f39c12'; // برتقالي للتأخير
            } else {
                $color = '#e74c3c'; // أحمر للتأخير الشديد
            }
        }
        
        return [
            'days' => $days,
            'text' => $duration_text,
            'html' => "<i class='fa fa-clock' style='margin-left: 5px;'></i> " . $duration_text,
            'colored_html' => "<span style='color: {$color}; font-weight: bold;'><i class='fa fa-clock' style='margin-left: 5px;'></i> " . $duration_text . "</span>",
            'color' => $color,
            'created_date' => $created_date,
            'end_date' => ($status === 'completed' && !empty($updated_date)) ? $updated_date : date('Y-m-d H:i:s'),
            'is_completed' => $status === 'completed',
            'status' => $status
        ];
        
    } catch (Exception $e) {
        return [
            'days' => 0,
            'text' => 'غير محدد',
            'html' => "<i class='fa fa-clock'></i> غير محدد",
            'colored_html' => "<span style='color: #999;'><i class='fa fa-clock'></i> غير محدد</span>",
            'color' => '#999',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * حساب المدة لمجموعة من الطلبات
 * @param array $requests مصفوفة الطلبات
 * @return array الطلبات مع إضافة معلومات المدة
 */
function addDurationToRequests($requests) {
    foreach ($requests as &$request) {
        $request['duration_info'] = calculateRequestDuration(
            $request['created_at'] ?? $request['create_date'] ?? '',
            $request['status'] ?? 'pending',
            $request['updated_at'] ?? $request['update_date'] ?? null
        );
    }
    return $requests;
}

/**
 * اختبار دالة حساب المدة
 */
function testDurationCalculation() {
    echo "<h3>🧪 اختبار حساب مدة الطلبات</h3>";
    
    $test_cases = [
        [
            'created_at' => '2025-10-25 10:00:00',
            'status' => 'pending',
            'updated_at' => null,
            'description' => 'طلب قيد الانتظار منذ 8 أيام'
        ],
        [
            'created_at' => '2025-11-01 14:30:00',
            'status' => 'in_progress',
            'updated_at' => null,
            'description' => 'طلب قيد التنفيذ منذ يوم واحد'
        ],
        [
            'created_at' => '2025-10-28 09:15:00',
            'status' => 'completed',
            'updated_at' => '2025-10-30 16:45:00',
            'description' => 'طلب مكتمل في يومين'
        ],
        [
            'created_at' => '2025-10-20 12:00:00',
            'status' => 'completed',
            'updated_at' => '2025-10-27 18:30:00',
            'description' => 'طلب مكتمل في أسبوع'
        ]
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f8ff;'>";
    echo "<th>الوصف</th><th>تاريخ الإنشاء</th><th>الحالة</th><th>تاريخ الإكمال</th><th>المدة المحسوبة</th><th>المدة الملونة</th>";
    echo "</tr>";
    
    foreach ($test_cases as $test) {
        $duration = calculateRequestDuration($test['created_at'], $test['status'], $test['updated_at']);
        
        echo "<tr>";
        echo "<td>{$test['description']}</td>";
        echo "<td>" . date('Y-m-d', strtotime($test['created_at'])) . "</td>";
        echo "<td>{$test['status']}</td>";
        echo "<td>" . ($test['updated_at'] ? date('Y-m-d', strtotime($test['updated_at'])) : 'لم يكتمل') . "</td>";
        echo "<td>{$duration['html']}</td>";
        echo "<td>{$duration['colored_html']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// إذا تم استدعاء الملف مباشرة، عرض الاختبار
if (basename($_SERVER['PHP_SELF']) === 'duration_calculator.php') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>📊 حاسبة مدة الطلبات</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>📝 كيفية الاستخدام:</h3>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 5px;'>";
    echo "// تضمين الملف
include 'duration_calculator.php';

// حساب مدة طلب واحد
\$duration = calculateRequestDuration('2025-10-25 10:00:00', 'pending');
echo \$duration['colored_html']; // عرض المدة بالألوان

// حساب مدة لمجموعة طلبات
\$requests = addDurationToRequests(\$requests_array);
foreach (\$requests as \$request) {
    echo \$request['duration_info']['colored_html'];
}";
    echo "</pre>";
    echo "</div>";
    
    testDurationCalculation();
    
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>✅ المزايا:</h3>";
    echo "<ul>";
    echo "<li>حساب دقيق للمدة بالأيام</li>";
    echo "<li>تمييز الطلبات المكتملة من غير المكتملة</li>";
    echo "<li>ألوان مختلفة حسب سرعة الإنجاز</li>";
    echo "<li>نصوص عربية صحيحة (يوم، يومان، أيام)</li>";
    echo "<li>معالجة الأخطاء والحالات الاستثنائية</li>";
    echo "</ul>";
    echo "</div>";
}
?>