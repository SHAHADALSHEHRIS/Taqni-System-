/**
 * ملف JavaScript محدث للتعامل مع قاعدة البيانات
 * Updated Database Integration Script
 */

class DatabaseAPI {
    constructor() {
        this.baseURL = 'http://localhost/projeect';
        this.authAPI = `${this.baseURL}/api/auth.php`;
        this.requestsAPI = `${this.baseURL}/api/requests.php`;
        console.log('🔧 تم تهيئة DatabaseAPI:', this.baseURL);
        console.log('📡 مسار API:', this.requestsAPI);
    }

    /**
     * إرسال طلب HTTP محدث
     */
    async makeRequest(url, data) {
        try {
            console.log('📤 إرسال طلب إلى:', url, data);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            // التحقق من صحة الاستجابة
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('📥 استجابة الخادم (نص):', text.substring(0, 200));
            
            // التحقق من أن الاستجابة JSON صحيحة
            try {
                const result = JSON.parse(text);
                console.log('✅ JSON parsed successfully:', result);
                return result;
            } catch (jsonError) {
                console.error('❌ خطأ في تحليل JSON:', jsonError);
                console.error('📄 النص الكامل:', text);
                return { success: false, message: 'خطأ في استجابة الخادم - البيانات ليست JSON صحيح' };
            }
            
        } catch (error) {
            console.error('❌ خطأ في الطلب:', error);
            return { success: false, message: 'خطأ في الشبكة: ' + error.message };
        }
    }

    /**
     * الحصول على الإحصائيات (للإدارة)
     */
    async getStatistics() {
        try {
            const url = `${this.requestsAPI}?action=get_statistics`;
            console.log('📊 جلب الإحصائيات من:', url);
            
            const response = await fetch(url);
            const text = await response.text();
            console.log('📥 رد الإحصائيات:', text);
            
            return JSON.parse(text);
        } catch (error) {
            console.error('❌ خطأ في جلب الإحصائيات:', error);
            return { success: false, message: 'خطأ في جلب الإحصائيات' };
        }
    }

    /**
     * الحصول على التقييمات
     */
    async getRatings() {
        try {
            const url = `${this.requestsAPI}?action=get_ratings`;
            console.log('⭐ جلب التقييمات من:', url);
            
            const response = await fetch(url);
            const text = await response.text();
            console.log('📥 رد التقييمات:', text);
            
            return JSON.parse(text);
        } catch (error) {
            console.error('❌ خطأ في جلب التقييمات:', error);
            return { success: false, message: 'خطأ في جلب التقييمات' };
        }
    }

    /**
     * الحصول على جميع الطلبات (للإدارة)
     */
    async getAllRequests(status = null) {
        try {
            let url = `${this.requestsAPI}?action=get_all`;
            if (status) {
                url += `&status=${status}`;
            }
            console.log('📋 جلب الطلبات من:', url);
            
            const response = await fetch(url);
            const text = await response.text();
            console.log('📥 رد الطلبات:', text);
            
            return JSON.parse(text);
        } catch (error) {
            console.error('❌ خطأ في جلب الطلبات:', error);
            return { success: false, message: 'خطأ في جلب الطلبات' };
        }
    }

    /**
     * إنشاء طلب جديد
     */
    async createRequest(requestData) {
        const userData = JSON.parse(localStorage.getItem('userData') || '{"id": 2}');
        
        return await this.makeRequest(this.requestsAPI, {
            action: 'create',
            user_id: userData.id,
            request_type: requestData.type,
            subject: requestData.subject,
            description: requestData.description,
            priority: requestData.priority || 'medium'
        });
    }

    /**
     * تحديث حالة الطلب
     */
    async updateRequestStatus(requestId, status, adminNotes = '') {
        const userData = JSON.parse(localStorage.getItem('userData') || '{"id": 1}');
        
        return await this.makeRequest(this.requestsAPI, {
            action: 'update_status',
            request_id: requestId,
            status: status,
            admin_notes: adminNotes,
            admin_id: userData.id
        });
    }

    /**
     * حفظ تقييم الطلب في قاعدة البيانات
     */
    async rateRequest(requestId, qualityRate, speedRate) {
        const userData = JSON.parse(localStorage.getItem('userData') || '{"id": 2}');
        return await this.makeRequest(this.requestsAPI, {
            action: 'rate_request',
            request_id: requestId,
            user_id: userData.id,
            quality_rate: qualityRate,
            speed_rate: speedRate
        });
    }

    /**
     * تسجيل الدخول
     */
    async login(username, password) {
        const result = await this.makeRequest(this.authAPI, {
            action: 'login',
            username: username,
            password: password
        });
        
        if (result.success) {
            // حفظ بيانات المستخدم والجلسة
            localStorage.setItem('sessionToken', result.session_token);
            localStorage.setItem('userData', JSON.stringify(result.user));
            sessionStorage.setItem('loggedIn', 'true');
        }
        
        return result;
    }

    /**
     * تسجيل الخروج
     */
    async logout() {
        const sessionToken = localStorage.getItem('sessionToken');
        if (sessionToken) {
            await this.makeRequest(this.authAPI, {
                action: 'logout',
                session_token: sessionToken
            });
        }
        
        // مسح البيانات المحلية
        localStorage.removeItem('sessionToken');
        localStorage.removeItem('userData');
        sessionStorage.removeItem('loggedIn');
        
        // إعادة توجيه إلى صفحة تسجيل الدخول
        window.location.href = 'login.html';
    }

    /**
     * التحقق من صحة تسجيل الدخول
     */
    isLoggedIn() {
        return sessionStorage.getItem('loggedIn') === 'true' && 
               localStorage.getItem('sessionToken') !== null;
    }

    /**
     * الحصول على بيانات المستخدم الحالي
     */
    getCurrentUser() {
        return JSON.parse(localStorage.getItem('userData') || '{}');
    }

    /**
     * تحويل حالة الطلب إلى نص عربي
     */
    getStatusText(status) {
        const statuses = {
            'pending': 'قيد الانتظار',
            'in_progress': 'قيد المعالجة',
            'completed': 'مكتمل',
            'rejected': 'مرفوض'
        };
        
        return statuses[status] || status;
    }

    /**
     * تحويل أولوية الطلب إلى نص عربي
     */
    getPriorityText(priority) {
        const priorities = {
            'low': 'منخفضة',
            'medium': 'متوسطة',
            'high': 'عالية',
            'urgent': 'عاجلة'
        };
        
        return priorities[priority] || priority;
    }

    /**
     * تنسيق التاريخ
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-SA', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// إنشاء مثيل عام من API
const dbAPI = new DatabaseAPI();

// إضافة وظيفة تسجيل الخروج للقائمة العامة
function globalLogout() {
    dbAPI.logout();
}

// تشغيل اختبار تلقائي
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 تم تحميل DatabaseAPI الجديد');
    
    // اختبار سريع للـ API
    if (window.location.pathname.includes('admin.html') || window.location.pathname.includes('track.html')) {
        setTimeout(async () => {
            console.log('🧪 اختبار سريع للـ API...');
            const testResult = await dbAPI.getStatistics();
            console.log('📊 نتيجة اختبار الإحصائيات:', testResult);
        }, 1000);
    }
});