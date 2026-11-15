<template>
  <section class="container py-5">
    <router-link :to="isAdmin ? '/admin' : '/orders'" class="text-muted small text-decoration-none">
      &larr; {{ isAdmin ? 'กลับไปหน้าจัดการร้าน' : 'คำสั่งซื้อของฉัน' }}
    </router-link>

    <CheckoutSteps v-if="$route.query.success" :current="3" class="mt-4" />

    <div v-if="$route.query.success" class="alert alert-success py-2 small mt-3">
      สั่งซื้อสำเร็จ ขอบคุณที่สั่งซื้อกับเรา กรุณาชำระเงินและแนบสลิปด้านล่าง
    </div>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>
    <div v-else-if="error && !order.id" class="alert alert-danger mt-3">{{ error }}</div>

    <div v-else class="row g-4 mt-1">
      <div class="col-lg-8">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">คำสั่งซื้อ #{{ order.id }}</h5>
            <span class="badge" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
          </div>
          <div class="text-muted small mb-3">{{ date(order.created_at) }}</div>

          <OrderTimeline :status="order.status" />

          <div v-for="(item, index) in order.items" :key="item.id"
               class="d-flex justify-content-between py-2"
               :class="{ 'border-top': index > 0 }">
            <div>
              <div>{{ item.name }}</div>
              <div class="text-muted small">ไซส์ {{ item.size }} &middot; {{ price(item.price) }} x {{ item.quantity }}</div>
            </div>
            <div>{{ price(item.price * item.quantity) }}</div>
          </div>

          <hr />
          <div class="d-flex justify-content-between">
            <strong>ยอดรวม</strong>
            <strong>{{ price(order.total) }}</strong>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card p-3 small mb-3">
          <h6>ข้อมูลจัดส่ง</h6>
          <div>{{ order.shipping_name }}</div>
          <div>{{ order.shipping_phone }}</div>
          <div class="text-muted">{{ order.shipping_address }}</div>
        </div>

        <div class="card p-3 small">
          <h6>หลักฐานการชำระเงิน</h6>

          <a v-if="order.slip_image" :href="order.slip_image" target="_blank" rel="noopener">
            <img :src="order.slip_image" alt="สลิปโอนเงิน" class="img-fluid rounded border mb-2" />
          </a>
          <div v-else class="text-muted mb-2">ยังไม่มีสลิป</div>

          <div v-if="order.status === 'pending' && isOwner">
            <div class="p-2 bg-light rounded mb-3">
              <div class="fw-semibold mb-1">ช่องทางชำระเงิน</div>
              <div>{{ payment.bank }}</div>
              <div>เลขบัญชี {{ payment.accountNo }}</div>
              <div class="text-muted">ชื่อบัญชี {{ payment.accountName }}</div>
            </div>
            <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
            <div v-if="msg" class="alert alert-success py-2">{{ msg }}</div>
            <label class="form-label">{{ order.slip_image ? 'แนบสลิปใหม่' : 'แนบสลิปโอนเงิน' }}</label>
            <input type="file" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm"
                   :disabled="uploading" @change="uploadSlip" />
            <div v-if="uploading" class="text-muted mt-1">กำลังอัปโหลด...</div>
            <div class="text-muted mt-2">ร้านจะตรวจสอบและเปลี่ยนสถานะเป็น "ชำระเงินแล้ว"</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import CheckoutSteps from '../components/CheckoutSteps.vue'
import OrderTimeline from '../components/OrderTimeline.vue'
import api from '../api'
import { formatPrice, formatDate, statusLabel, statusClass } from '../utils'
import { payment } from '../config'

export default {
  name: 'OrderDetailView',
  components: { CheckoutSteps, OrderTimeline },
  data() {
    return { order: {}, payment, loading: true, uploading: false, error: '', msg: '' }
  },
  computed: {
    isAdmin() { return this.$store.getters.isAdmin },
    isOwner() { return this.order.user_id == this.$store.state.user?.id },
  },
  created() { this.load() },
  methods: {
    price: formatPrice,
    date: formatDate,
    statusLabel,
    statusClass,
    async load() {
      try {
        const { data } = await api.get('/orders/index.php', { params: { id: this.$route.params.id } })
        this.order = data
      } catch (e) {
        this.error = e.response?.data?.error || 'โหลดคำสั่งซื้อไม่สำเร็จ'
      } finally {
        this.loading = false
      }
    },
    async uploadSlip(e) {
      const file = e.target.files[0]
      if (!file) return
      this.error = this.msg = ''
      this.uploading = true
      try {
        const fd = new FormData()
        fd.append('order_id', this.order.id)
        fd.append('slip', file)
        const { data } = await api.post('/orders/slip.php', fd)
        this.order.slip_image = data.url
        this.msg = 'แนบสลิปแล้ว รอร้านตรวจสอบ'
      } catch (err) {
        this.error = err.response?.data?.error || 'อัปโหลดสลิปไม่สำเร็จ'
      } finally {
        this.uploading = false
        e.target.value = ''
      }
    },
  },
}
</script>