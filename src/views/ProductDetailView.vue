<template>
  <section class="container py-5">
    <router-link to="/products" class="text-muted small text-decoration-none">&larr; กลับไปหน้าสินค้า</router-link>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>
    <div v-else-if="error" class="alert alert-danger mt-3">{{ error }}</div>

    <div v-else class="row g-5 mt-1">
      <div class="col-md-6">
        <div class="ratio ratio-1x1 bg-light rounded-3 overflow-hidden">
          <img v-if="img(product.image)" :src="img(product.image)" :alt="product.name" class="object-fit-cover" />
          <div v-else class="d-flex align-items-center justify-content-center text-muted">ไม่มีรูป</div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="text-muted small">{{ product.category }}</div>
        <h3 class="fw-semibold">{{ product.name }}</h3>
        <h4 class="mb-3">{{ price(product.price) }}</h4>
        <p class="text-muted">{{ product.description }}</p>

        <div class="mb-3">
          <div class="small mb-2">ไซส์</div>
          <div class="d-flex gap-2 flex-wrap">
            <button v-for="s in sizes" :key="s" type="button"
                    class="btn btn-sm px-3"
                    :class="size === s ? 'btn-dark' : 'btn-outline-dark'"
                    @click="size = s">{{ s }}</button>
          </div>
        </div>

        <div class="mb-4">
          <div class="small mb-2">จำนวน <span class="text-muted">(คงเหลือ {{ product.stock }})</span></div>
          <div class="input-group" style="width: 140px">
            <button class="btn btn-outline-dark" type="button" @click="qty = Math.max(1, qty - 1)">-</button>
            <input :value="qty" class="form-control text-center" readonly />
            <button class="btn btn-outline-dark" type="button" @click="qty = Math.min(product.stock, qty + 1)">+</button>
          </div>
        </div>

        <button class="btn btn-dark px-4" :disabled="product.stock <= 0" @click="add">
          {{ product.stock <= 0 ? 'สินค้าหมด' : 'เพิ่มลงตะกร้า' }}
        </button>

        <div v-if="added" class="alert alert-success py-2 small mt-3">
          เพิ่มลงตะกร้าแล้ว
          <router-link to="/cart" class="text-dark ms-1">ดูตะกร้า</router-link>
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
    return { product: {}, size: '', qty: 1, added: false, loading: true, error: '' }
  },
  computed: {
    sizes() {
      return (this.product.sizes || '').split(',').map((s) => s.trim()).filter(Boolean)
    },
  },
  async created() {
    try {
      const { data } = await api.get('http://localhost/project-21hiking-shop/php_api/products/', { params: { id: this.$route.params.id } })
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