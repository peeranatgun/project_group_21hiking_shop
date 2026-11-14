<template>
  <section class="container py-5 my-lg-5 text-center">
    <p class="text-uppercase text-muted small letter">Hiking Apparel</p>
    <h1 class="display-5 fw-semibold mb-3">เสื้อเดินป่า<br />เบา แห้งเร็ว ใส่สบาย</h1>
    <p class="text-muted mb-4">เลือกเสื้อที่เหมาะกับทุกเส้นทางของคุณ</p>
    <router-link class="btn btn-dark px-4 py-2" to="/products">ดูสินค้าทั้งหมด</router-link>
  </section>

  <section class="container pb-5" v-if="latest.length">
    <div class="d-flex justify-content-between align-items-baseline mb-3">
      <h5 class="fw-semibold mb-0">สินค้ามาใหม่</h5>
      <router-link to="/products" class="small text-dark">ดูทั้งหมด</router-link>
    </div>
    <div class="row g-4">
      <div class="col-6 col-md-3" v-for="p in latest" :key="p.id">
        <router-link :to="`/products/${p.id}`" class="text-decoration-none text-dark">
          <div class="card h-100 overflow-hidden">
            <div class="ratio ratio-1x1 bg-light">
              <img v-if="img(p.image)" :src="img(p.image)" :alt="p.name" class="object-fit-cover" />
              <div v-else class="d-flex align-items-center justify-content-center text-muted small">ไม่มีรูป</div>
            </div>
            <div class="p-3">
              <div class="fw-semibold">{{ p.name }}</div>
              <div>{{ price(p.price) }}</div>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </section>

  <section class="container pb-5">
    <div class="row g-4 text-center">
      <div class="col-md-4" v-for="f in features" :key="f.title">
        <div class="border rounded-3 p-4 h-100">
          <h5 class="fw-semibold">{{ f.title }}</h5>
          <p class="text-muted small mb-0">{{ f.text }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'
import { formatPrice, imageUrl } from '../utils'

export default {
  name: 'HomeView',
  data() {
    return {
      latest: [],
      features: [
        { title: 'แห้งเร็ว', text: 'ผ้าระบายอากาศดี เหมาะกับการเดินทางไกล' },
        { title: 'น้ำหนักเบา', text: 'พับเก็บง่าย ไม่เป็นภาระในเป้' },
        { title: 'กัน UV', text: 'ปกป้องผิวจากแดดตลอดเส้นทาง' },
      ],
    }
  },
  async created() {
    try {
      const { data } = await api.get('http://localhost/project-21hiking-shop/php_api/products/')
      this.latest = data.slice(0, 4)   // API เรียงจากใหม่ไปเก่าอยู่แล้ว
    } catch (e) {
      this.latest = []                 // โหลดไม่ได้ก็ซ่อนส่วนนี้ไป
    }
  },
  methods: { price: formatPrice, img: imageUrl },
}
</script>