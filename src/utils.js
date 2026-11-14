// แสดงราคาเป็นบาท เช่น 1,290 ฿
export function formatPrice(n) {
  return Number(n).toLocaleString('th-TH', { maximumFractionDigits: 2 }) + ' ฿'
}

// ถ้าใน DB เก็บเป็นลิงก์เต็ม (http...) จะใช้รูปนั้น ถ้าไม่มีจะคืนค่า null เพื่อแสดงกล่องว่างแทน
export function imageUrl(image) {
  return image && /^https?:\/\//.test(image) ? image : null
}

// สถานะคำสั่งซื้อ
const STATUS = {
  pending: { label: 'รอชำระเงิน', cls: 'bg-warning text-dark' },
  paid: { label: 'ชำระเงินแล้ว', cls: 'bg-info text-dark' },
  shipped: { label: 'จัดส่งแล้ว', cls: 'bg-primary' },
  completed: { label: 'สำเร็จ', cls: 'bg-success' },
  cancelled: { label: 'ยกเลิก', cls: 'bg-secondary' },
}
export const statusList = Object.keys(STATUS)
export const statusLabel = (s) => STATUS[s]?.label || s
export const statusClass = (s) => STATUS[s]?.cls || 'bg-secondary'

// วันที่จาก MySQL (2026-11-14 13:00:00) -> วันที่แบบไทย
export function formatDate(d) {
  if (!d) return ''
  return new Date(d.replace(' ', 'T')).toLocaleString('th-TH', { dateStyle: 'medium', timeStyle: 'short' })
}