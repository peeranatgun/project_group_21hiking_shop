<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <select v-model="filter" class="form-select w-auto">
        <option value="">ทุกสถานะ</option>
        <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
      </select>
      <span class="text-muted small">{{ filtered.length }} รายการ</span>
    </div>

    <div v-if="msg" class="alert alert-success py-2 small">{{ msg }}</div>
    <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>

    <div v-else class="card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="small text-muted">
            <tr>
              <th>#</th><th>ลูกค้า</th><th>วันที่</th><th class="text-end">ยอดรวม</th><th>สถานะ</th><th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in filtered" :key="o.id">
              <td>{{ o.id }}</td>
              <td>
                {{ o.customer }}
                <span v-if="o.slip_image && o.status === 'pending'" class="badge bg-info text-dark ms-1">มีสลิป</span>
              </td>
              <td class="small">{{ date(o.created_at) }}</td>
              <td class="text-end">{{ price(o.total) }}</td>
              <td>
                <select class="form-select form-select-sm" style="min-width: 140px"
                        :value="o.status" @change="changeStatus(o, $event)">
                  <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                </select>
              </td>
              <td class="text-end">
                <router-link :to="`/orders/${o.id}`" class="btn btn-sm btn-outline-dark">ดูรายละเอียด</router-link>
              </td>
            </tr>
            <tr v-if="!filtered.length">
              <td colspan="6" class="text-center text-muted py-4">ไม่มีคำสั่งซื้อ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../api'
import { formatPrice, formatDate, statusLabel, statusList } from '../utils'

export default {
  name: 'AdminOrders',
  data() {
    return { orders: [], filter: '', statuses: statusList, loading: true, error: '', msg: '' }
  },
  computed: {
    filtered() {
      return this.filter ? this.orders.filter((o) => o.status === this.filter) : this.orders
    },
  },
  created() { this.load() },
  methods: {
    price: formatPrice,
    date: formatDate,
    statusLabel,
    async load() {
      try {
        const { data } = await api.get('/orders/index.php')
        this.orders = data
      } catch (e) {
        this.error = e.response?.data?.error || 'โหลดคำสั่งซื้อไม่สำเร็จ'
      } finally {
        this.loading = false
      }
    },
    async changeStatus(order, ev) {
      const status = ev.target.value
      this.error = ''
      this.msg = ''
      if (status === 'cancelled' && !confirm(`ยกเลิกคำสั่งซื้อ #${order.id}? สต็อกสินค้าจะถูกคืน`)) {
        ev.target.value = order.status
        return
      }
      try {
        await api.put('/orders/index.php', { status }, { params: { id: order.id } })
        order.status = status
        this.msg = `อัปเดตคำสั่งซื้อ #${order.id} แล้ว`
      } catch (e) {
        ev.target.value = order.status
        this.error = e.response?.data?.error || 'อัปเดตสถานะไม่สำเร็จ'
      }
    },
  },
}
</script>