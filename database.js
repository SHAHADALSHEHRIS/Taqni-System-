/**
 * ملف JavaScript للتعامل مع قاعدة البيانات
 * Database Integration Script
 */

class DatabaseAPI {
    /**
     * حفظ تقييم الطلب في قاعدة البيانات
     */
    async rateRequest(requestId, qualityRate, speedRate) {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        return await this.makeRequest(this.requestsAPI, {
            action: 'rate_request',
            request_id: requestId,
            user_id: userData.id,
            quality_rate: qualityRate,
            speed_rate: speedRate
        });
    }
    constructor() {
        this.baseURL = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
        this.authAPI = `${this.baseURL}/api/auth.php`;
        this.requestsAPI = `${this.baseURL}/api/requests.php`;
    }

    /**
     * إرسال طلب HTTP
     */
    async makeRequest(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            return await response.json();
        } catch (error) {
            console.error('خطأ في الطلب:', error);
            return { success: false, message: 'خطأ في الشبكة' };
        }
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
     * تسجيل مستخدم جديد
     */
    async register(userData) {
        return await this.makeRequest(this.authAPI, {
            action: 'register',
            username: userData.username,
            email: userData.email,
            password: userData.password,
            full_name: userData.full_name
        });
    }

    /**
     * التحقق من صحة الجلسة
     */
    async validateSession() {
        const sessionToken = localStorage.getItem('sessionToken');
        if (!sessionToken) {
            return { success: false, message: 'لا توجد جلسة' };
        }

        return await this.makeRequest(this.authAPI, {
            action: 'validate',
            session_token: sessionToken
        });
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
     * إنشاء طلب جديد
     */
    async createRequest(requestData) {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        
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
     * الحصول على طلبات المستخدم
     */
    async getUserRequests() {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        
        return await this.makeRequest(this.requestsAPI, {
            action: 'get_user_requests',
            user_id: userData.id
        });
    }

    /**
     * الحصول على جميع الطلبات (للإدارة)
     */
    async getAllRequests(status = null) {
        return await this.makeRequest(this.requestsAPI, {
            action: 'get_all',
            status: status
        });
    }

    /**
     * تحديث حالة الطلب
     */
    async updateRequestStatus(requestId, status, adminNotes = '') {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        
        return await this.makeRequest(this.requestsAPI, {
            action: 'update_status',
            request_id: requestId,
            status: status,
            admin_notes: adminNotes,
            admin_id: userData.id
        });
    }

    /**
     * الحصول على تفاصيل طلب محدد
     */
    async getRequestDetails(requestId) {
        return await this.makeRequest(this.requestsAPI, {
            action: 'get_details',
            request_id: requestId
        });
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

// التحقق من صحة الجلسة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', async function() {
    // إذا كان المستخدم في صفحة تسجيل الدخول، لا نحتاج للتحقق
    if (window.location.pathname.includes('login.html')) {
        return;
    }
    
    // التحقق من صحة الجلسة
    const sessionCheck = await dbAPI.validateSession();
    if (!sessionCheck.success) {
        // إذا كانت الجلسة غير صحيحة، إعادة توجيه إلى تسجيل الدخول
        localStorage.removeItem('sessionToken');
        localStorage.removeItem('userData');
        sessionStorage.removeItem('loggedIn');
        window.location.href = 'login.html';
    }
});

// إضافة وظيفة تسجيل الخروج للقائمة العامة
function globalLogout() {
    dbAPI.logout();
}