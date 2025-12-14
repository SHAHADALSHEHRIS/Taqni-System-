/* Lightweight client API wrapper for backend PHP endpoints */
const dbAPI = (() => {
  const base = '';
  const jsonHeaders = { 'Content-Type': 'application/json' };

  // helpers
  const post = (url, body) => fetch(base + url, {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify(body || {})
  }).then(r => r.json());

  // auth
  const login = (username, password) => post('api/auth.php', { action: 'login', username, password })
    .then(res => { if (res.success) localStorage.setItem('session_token', res.session_token); return res; });

  const register = (username, email, password, full_name) => post('api/auth.php', { action: 'register', username, email, password, full_name });

  const validate = () => post('api/auth.php', { action: 'validate', session_token: localStorage.getItem('session_token') || '' });

  const logout = () => post('api/auth.php', { action: 'logout', session_token: localStorage.getItem('session_token') || '' })
    .then(res => { localStorage.removeItem('session_token'); return res; });

  const isLoggedIn = () => !!localStorage.getItem('session_token');
  const getCurrentUser = async () => { const v = await validate(); return v.success ? v.user : null; };

  // requests
  const createRequest = (data) => post('api/requests.php', {
    action: 'create',
    user_id: data.user_id,
    request_type: data.request_type,
    subject: data.subject,
    description: data.description,
    priority: data.priority || 'medium'
  });

  const getAllRequests = (status = null) => post('api/requests.php', { action: 'get_all', status });
  const getUserRequests = () => validate().then(v => v.success ? post('api/requests.php', { action: 'get_user_requests', user_id: v.user.id }) : Promise.resolve({ success:false, message:'Not logged in' }));
  const updateRequestStatus = (request_id, status, admin_notes = '') => post('api/requests.php', { action: 'update_status', request_id, status, admin_notes, admin_id: 0 });
  const getStatistics = () => post('api/requests.php', { action: 'get_statistics' });

  // formatting helpers (previously used by pages)
  const getStatusText = (status) => ({
    pending: 'قيد الانتظار',
    in_progress: 'قيد التنفيذ',
    completed: 'تم التنفيذ',
    rejected: 'مرفوض'
  }[status] || status);

  const getPriorityText = (p) => ({ low: 'منخفض', medium: 'متوسط', high: 'عالٍ', urgent: 'عاجل' }[p] || p);

  const formatDate = (iso) => {
    try { return new Date(iso).toLocaleString(); } catch { return iso; }
  };

  return { login, register, validate, logout, isLoggedIn, getCurrentUser, createRequest, getAllRequests, getUserRequests, updateRequestStatus, getStatistics, getStatusText, getPriorityText, formatDate };
})();
