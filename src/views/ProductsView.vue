<template>
  <section class="container py-5">
    <h3 class="fw-semibold mb-4">สินค้าทั้งหมด</h3>

    <div class="row g-2 mb-4">
      <div class="col-md-6">
        <input v-model.trim="q" class="form-control" placeholder="ค้นหาสินค้า..." />
      </div>
      <div class="col-md-3">
        <select v-model="category" class="form-select">
          <option value="">ทุกหมวดหมู่</option>
          <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else-if="!filtered.length" class="text-center text-muted py-5">ไม่พบสินค้า</div>

    <div v-else class="row g-4">
      <div class="col-6 col-md-4 col-lg-3" v-for="p in filtered" :key="p.id">
        <router-link :to="`/products/${p.id}`" class="text-decoration-none text-dark">
          <div class="card h-100 overflow-hidden">
            <div class="ratio ratio-1x1 bg-light">
              <img v-if="img(p.image)" :src="img(p.image)" :alt="p.name" class="object-fit-cover" />
              <div v-else class="d-flex align-items-center justify-content-center text-muted small">
                ไม่มีรูป
              </div>
            </div>
            <div class="p-3">
              <div class="text-muted small">{{ p.category }}</div>
              <div class="fw-semibold">{{ p.name }}</div>
              <div class="mt-1">
                {{ price(p.price) }}
                <span v-if="p.stock <= 0" class="badge bg-secondary ms-1">หมด</span>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'
import { formatPrice, imageUrl } from '../utils'

export default {
  name: 'ProductsView',
  data() {
    return { products: [], q: '', category: '', loading: true, error: '' }
  },
  computed: {
    categories() {
      return [...new Set(this.products.map((p) => p.category).filter(Boolean))]
    },
    filtered() {
      const q = this.q.toLowerCase()
      return this.products.filter(
        (p) =>
          (!this.category || p.category === this.category) &&
          (!q || p.name.toLowerCase().includes(q))
      )
    },
  },
  async created() {
    try {
      const { data } = await api.get('http://localhost/project-21hiking-shop/php_api/products/')
      this.products = data
    } catch (e) {
      this.error = 'โหลดสินค้าไม่สำเร็จ กรุณาตรวจสอบว่า Apache/MySQL เปิดอยู่'
    } finally {
      this.loading = false
    }
  },
  methods: { price: formatPrice, img: imageUrl },
}
</script>