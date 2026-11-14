<template>
  <section class="container py-5">
    <h3 class="fw-semibold mb-4">ตะกร้าสินค้า</h3>

    <div v-if="!items.length" class="text-center text-muted py-5">
      <p>ตะกร้าของคุณยังว่างอยู่</p>
      <router-link to="/products" class="btn btn-dark px-4">เลือกซื้อสินค้า</router-link>
    </div>

    <div v-else class="row g-4">
      <div class="col-lg-8">
        <div class="card p-3">
          <div v-for="(item, index) in items" :key="item.product_id + item.size"
               class="d-flex align-items-center gap-3 py-3"
               :class="{ 'border-top': index > 0 }">
            <div class="bg-light rounded-2 flex-shrink-0" style="width: 72px; height: 72px; overflow: hidden">
              <img v-if="img(item.image)" :src="img(item.image)" :alt="item.name"
                   class="w-100 h-100 object-fit-cover" />
            </div>

            <div class="flex-grow-1">
              <div class="fw-semibold">{{ item.name }}</div>
              <div class="text-muted small">ไซส์ {{ item.size }} &middot; {{ price(item.price) }}</div>
            </div>

            <div class="input-group input-group-sm" style="width: 110px">
              <button class="btn btn-outline-dark" @click="setQty(index, item.quantity - 1)">-</button>
              <input :value="item.quantity" class="form-control text-center" readonly />
              <button class="btn btn-outline-dark" @click="setQty(index, item.quantity + 1)">+</button>
            </div>

            <div class="text-end" style="width: 90px">{{ price(item.price * item.quantity) }}</div>

            <button class="btn btn-sm btn-link text-muted text-decoration-none" @click="remove(index)">ลบ</button>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card p-4">
          <div class="d-flex justify-content-between mb-3">
            <span>ยอดรวม</span>
            <strong>{{ price(total) }}</strong>
          </div>
          <router-link to="/checkout" class="btn btn-dark w-100">ดำเนินการสั่งซื้อ</router-link>
          <router-link to="/products" class="btn btn-link text-muted w-100 mt-2">เลือกสินค้าเพิ่ม</router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { formatPrice, imageUrl } from '../utils'

export default {
  name: 'CartView',
  computed: {
    items() { return this.$store.state.cart },
    total() { return this.$store.getters.cartTotal },
  },
  methods: {
    price: formatPrice,
    img: imageUrl,
    setQty(index, quantity) { this.$store.commit('setCartQty', { index, quantity }) },
    remove(index) { this.$store.commit('removeFromCart', index) },
  },
}
</script>