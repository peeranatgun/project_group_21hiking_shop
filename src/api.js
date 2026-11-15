import axios from 'axios'

// ค่าอ่านจากไฟล์ .env.development / .env.production ที่รากโปรเจกต์
const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost/project_group_21hiking_shop/php_api',
  timeout: 15000,
})

// แนบ token ไปกับทุก request อัตโนมัติ
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api