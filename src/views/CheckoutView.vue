<template>
  <section class="container py-5">
    <CheckoutSteps :current="2" />
    <h3 class="fw-semibold mb-4">ยืนยันคำสั่งซื้อ</h3>

    <div v-if="!items.length" class="text-center text-muted py-5">
      <p>ตะกร้าของคุณยังว่างอยู่</p>
      <router-link to="/products" class="btn btn-dark px-4">เลือกซื้อสินค้า</router-link>
    </div>

    <div v-else class="row g-4">
      <div class="col-lg-7">
        <form @submit.prevent="submit" class="card p-4">
          <h6 class="mb-3">ข้อมูลจัดส่ง</h6>

          <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

          <div class="mb-3">
            <label class="form-label small">ชื่อผู้รับ</label>
            <input v-model.trim="form.shipping_name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label small">เบอร์โทร</label>
            <input v-model.trim="form.shipping_phone" class="form-control" required />
          </div>
          <div class="mb-4">
            <label class="form-label small">ที่อยู่จัดส่ง</label>
            <textarea v-model.trim="form.shipping_address" class="form-control" rows="3" required></textarea>
          </div>

          <button class="btn btn-dark w-100" :disabled="loading">
            {{ loading ? 'กำลังสั่งซื้อ...' : 'ยืนยันสั่งซื้อ' }}
          </button>
          <p class="text-muted small mt-3 mb-0">หลังสั่งซื้อ สถานะจะเป็น "รอชำระเงิน" ร้านจะอัปเดตสถานะให้เมื่อได้รับการชำระเงิน</p>
        </form>
      </div>

      <div class="col-lg-5">
        <div class="card p-4">
          <h6 class="mb-3">สรุปรายการ</h6>
          <div v-for="item in items" :key="item.product_id + item.size"
               class="d-flex justify-content-between small mb-2">
            <span>{{ item.name }} ({{ item.size }}) x {{ item.quantity }}</span>
            <span>{{ price(item.price * item.quantity) }}</span>
          </div>
          <hr />
          <div class="d-flex justify-content-between">
            <strong>ยอดรวม</strong>
            <strong>{{ price(total) }}</strong>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import CheckoutSteps from '../components/CheckoutSteps.vue'
import api from '../api'
import { formatPrice } from '../utils'

export default {
  name: 'CheckoutView',
  components: { CheckoutSteps },
  data() {
    const u = this.$store.state.user || {}
    return {
      form: {
        shipping_name: u.name || '',
        shipping_phone: u.phone || '',
        shipping_address: u.address || '',
      },
      error: '',
      loading: false,
    }
  },
  computed: {
    items() { return this.$store.state.cart },
    total() { return this.$store.getters.cartTotal },
  },
  methods: {
    price: formatPrice,
    async submit() {
      this.error = ''
      this.loading = true
      try {
        const { data } = await api.post('/orders/index.php', {
          ...this.form,
          items: this.items.map((i) => ({
            product_id: i.product_id,
            size: i.size,
            quantity: i.quantity,
          })),
        })
        this.$store.commit('clearCart')
        this.$router.push({ path: `/orders/${data.order_id}`, query: { success: 1 } })
      } catch (e) {
        this.error = e.response?.data?.error || 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>