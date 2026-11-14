<template>
  <section class="container py-5">
    <h3 class="fw-semibold mb-4">จัดการร้าน</h3>

    <ul class="nav nav-pills mb-4 gap-2">
      <li class="nav-item">
        <button class="btn btn-sm px-3" :class="tab === 'orders' ? 'btn-dark' : 'btn-outline-dark'"
                @click="tab = 'orders'">คำสั่งซื้อ</button>
      </li>
      <li class="nav-item">
        <button class="btn btn-sm px-3" :class="tab === 'products' ? 'btn-dark' : 'btn-outline-dark'"
                @click="tab = 'products'">สินค้า</button>
      </li>
    </ul>

    <AdminOrders v-if="tab === 'orders'" />
    <AdminProducts v-else />
  </section>
</template>

<script>
import AdminOrders from '../components/AdminOrders.vue'
import AdminProducts from '../components/AdminProducts.vue'

export default {
  name: 'AdminView',
  components: { AdminOrders, AdminProducts },
  data() {
    return { tab: 'orders' }
  },
}
</script>