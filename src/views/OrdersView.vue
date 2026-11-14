<template>
  <section class="container py-5">
    <h3 class="fw-semibold mb-4">คำสั่งซื้อของฉัน</h3>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else-if="!orders.length" class="text-center text-muted py-5">
      <p>ยังไม่มีคำสั่งซื้อ</p>
      <router-link to="/products" class="btn btn-dark px-4">เลือกซื้อสินค้า</router-link>
    </div>

    <div v-else class="card">
      <router-link v-for="(o, index) in orders" :key="o.id" :to="`/orders/${o.id}`"
                   class="d-flex justify-content-between align-items-center p-3 text-decoration-none text-dark"
                   :class="{ 'border-top': index > 0 }">
        <div>
          <div class="fw-semibold">คำสั่งซื้อ #{{ o.id }}</div>
          <div class="text-muted small">{{ date(o.created_at) }}</div>
        </div>
        <div class="text-end">
          <div>{{ price(o.total) }}</div>
          <span class="badge" :class="statusClass(o.status)">{{ statusLabel(o.status) }}</span>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script>
import api from '../api'
import { formatPrice, formatDate, statusLabel, statusClass } from '../utils'

export default {
  name: 'OrdersView',
  data() {
    return { orders: [], loading: true, error: '' }
  },
  async created() {
    try {
      const { data } = await api.get('http://localhost/project-21hiking-shop/php_api/orders/')
      this.orders = data
    } catch (e) {
      this.error = e.response?.data?.error || 'โหลดคำสั่งซื้อไม่สำเร็จ'
    } finally {
      this.loading = false
    }
  },
  methods: { price: formatPrice, date: formatDate, statusLabel, statusClass },
}
</script>