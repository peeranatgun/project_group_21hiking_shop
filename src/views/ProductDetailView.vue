<template>
  <section class="container py-5">
    <nav class="small text-muted mb-4">
      <router-link to="/products" class="text-muted text-decoration-none">สินค้า</router-link>
      <span v-if="product.category"> / {{ product.category }}</span>
    </nav>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else class="row g-5">
      <div class="col-md-6">
        <div class="ratio ratio-1x1 bg-sand rounded-3 overflow-hidden">
          <img v-if="img(product.image)" :src="img(product.image)" :alt="product.name" class="object-fit-cover" />
          <div v-else class="d-flex align-items-center justify-content-center text-muted">ไม่มีรูป</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="sticky-col">
          <p class="eyebrow mb-2">{{ product.category || 'Hiking Apparel' }}</p>
          <h2 class="fw-semibold mb-2">{{ product.name }}</h2>
          <h4 class="mb-3">{{ price(product.price) }}</h4>
          <p class="text-muted">{{ product.description }}</p>

          <div class="mb-4">
            <div class="small mb-2">ไซส์ <span class="text-muted">{{ size }}</span></div>
            <div class="d-flex gap-2 flex-wrap">
              <button v-for="s in sizes" :key="s" type="button" class="size-btn"
                      :class="{ active: size === s }" @click="size = s">{{ s }}</button>
            </div>
          </div>

          <div class="mb-4">
            <div class="small mb-2">
              จำนวน
              <span v-if="product.stock > 0 && product.stock <= 5" class="text-danger ms-1">เหลือเพียง {{ product.stock }} ชิ้น</span>
              <span v-else class="text-muted ms-1">(คงเหลือ {{ product.stock }})</span>
            </div>
            <div class="input-group" style="width: 140px">
              <button class="btn btn-outline-dark" type="button" @click="qty = Math.max(1, qty - 1)">-</button>
              <input :value="qty" class="form-control text-center" readonly />
              <button class="btn btn-outline-dark" type="button" @click="qty = Math.min(product.stock, qty + 1)">+</button>
            </div>
          </div>

          <button class="btn btn-dark w-100 py-2 mb-3" :disabled="product.stock <= 0" @click="add">
            {{ product.stock <= 0 ? 'สินค้าหมด' : 'เพิ่มลงตะกร้า' }}
          </button>

          <div v-if="added" class="alert alert-success py-2 small">
            เพิ่มลงตะกร้าแล้ว
            <router-link to="/cart" class="ms-1">ดูตะกร้า</router-link>
          </div>

          <!-- ตารางไซส์ (พับเก็บ) : ค่าด้านล่างเป็นตัวอย่าง แก้เป็นขนาดจริงของร้านใน data() -->
          <details class="fold mt-4">
            <summary>ตารางไซส์</summary>
            <div class="table-responsive pb-3">
              <table class="table table-sm mb-1">
                <thead>
                  <tr><th v-for="h in sizeChart.headers" :key="h">{{ h }}</th></tr>
                </thead>
                <tbody>
                  <tr v-for="row in sizeChart.rows" :key="row[0]">
                    <td v-for="(cell, i) in row" :key="i">{{ cell }}</td>
                  </tr>
                </tbody>
              </table>
              <div class="text-muted small">ขนาดวัดจากตัวเสื้อ อาจคลาดเคลื่อนได้เล็กน้อย</div>
            </div>
          </details>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'
import { formatPrice, imageUrl } from '../utils'

export default {
  name: 'ProductDetailView',
  data() {
    return {
      product: {},
      size: '',
      qty: 1,
      added: false,
      loading: true,
      error: '',
      // ตัวอย่างตารางไซส์ — แก้เป็นค่าจริงของร้านก่อนใช้งาน
      sizeChart: {
        headers: ['ไซส์', 'รอบอก (นิ้ว)', 'ความยาว (นิ้ว)'],
        rows: [
          ['S', '36', '26'],
          ['M', '38', '27'],
          ['L', '40', '28'],
          ['XL', '42', '29'],
        ],
      },
    }
  },
  computed: {
    sizes() {
      return (this.product.sizes || '').split(',').map((s) => s.trim()).filter(Boolean)
    },
  },
  async created() {
    try {
      const { data } = await api.get('/products/index.php', { params: { id: this.$route.params.id } })
      this.product = data
      this.size = this.sizes[0] || ''
    } catch (e) {
      this.error = e.response?.data?.error || 'โหลดสินค้าไม่สำเร็จ'
    } finally {
      this.loading = false
    }
  },
  methods: {
    price: formatPrice,
    img: imageUrl,
    add() {
      this.$store.commit('addToCart', {
        product_id: this.product.id,
        name: this.product.name,
        price: Number(this.product.price),
        image: this.product.image,
        size: this.size,
        quantity: this.qty,
        stock: this.product.stock,
      })
      this.added = true
    },
  },
}
</script>