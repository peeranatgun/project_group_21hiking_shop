<template>
  <!-- Hero -->
  <section class="hero">
    <div class="container py-5">
      <div class="row align-items-center g-5 py-lg-4">
        <div class="col-lg-6">
          <p class="eyebrow mb-3">Hiking Apparel</p>
          <h1 class="display-5 fw-semibold mb-3">เสื้อเดินป่า<br />เบา แห้งเร็ว ใส่สบาย</h1>
          <p class="text-muted mb-4">เลือกเสื้อที่เหมาะกับทุกเส้นทางของคุณ ตั้งแต่ทริปวันเดียวจนถึงเดินยาวหลายวัน</p>
          <div class="d-flex gap-2 flex-wrap">
            <router-link class="btn btn-dark px-4 py-2" to="/products">ดูสินค้าทั้งหมด</router-link>
            <router-link v-if="!isLoggedIn" class="btn btn-outline-dark px-4 py-2" to="/register">สมัครสมาชิก</router-link>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="hero-art">
            <img src="/src/img/hero.jpg" alt="เสื้อเดินป่า" />
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- สินค้ามาใหม่ -->
  <section class="container section pb-0" v-if="latest.length">
    <div class="d-flex justify-content-between align-items-baseline mb-4">
      <h4 class="fw-semibold mb-0">สินค้ามาใหม่</h4>
      <router-link to="/products" class="small">ดูทั้งหมด &rarr;</router-link>
    </div>
    <div class="row g-4">
      <div class="col-6 col-md-3" v-for="p in latest" :key="p.id">
        <router-link :to="`/products/${p.id}`" class="text-decoration-none text-dark">
          <div class="card product-card h-100">
            <div class="ratio ratio-1x1">
              <img v-if="img(p.image)" :src="img(p.image)" :alt="p.name" class="object-fit-cover" />
              <div v-else class="d-flex align-items-center justify-content-center text-muted small">ไม่มีรูป</div>
            </div>
            <div class="pt-3">
              <div class="fw-semibold">{{ p.name }}</div>
              <div class="text-muted">{{ price(p.price) }}</div>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </section>

  <!-- จุดเด่น -->
  <section class="container section">
    <div class="row g-4">
      <div class="col-md-4" v-for="(f, i) in features" :key="f.title">
        <div class="feature-num mb-2">0{{ i + 1 }}</div>
        <h5 class="fw-semibold">{{ f.title }}</h5>
        <p class="text-muted small mb-0">{{ f.text }}</p>
      </div>
    </div>
  </section>

  <!-- แบนเนอร์ท้ายหน้า -->
  <section class="container pb-4">
    <div class="brand-banner p-5 text-center">
      <p class="eyebrow mb-2">Ready to go</p>
      <h3 class="fw-semibold mb-3">พร้อมออกเดินทางแล้วหรือยัง</h3>
      <router-link to="/products" class="btn btn-light px-4">เลือกเสื้อสำหรับทริปหน้า</router-link>
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
  computed: {
    isLoggedIn() { return this.$store.getters.isLoggedIn },
  },
  async created() {
    try {
      const { data } = await api.get('/products/index.php')
      this.latest = data.slice(0, 4)   // API เรียงจากใหม่ไปเก่าอยู่แล้ว
    } catch (e) {
      this.latest = []                 // โหลดไม่ได้ก็ซ่อนส่วนนี้ไป
    }
  },
  methods: { price: formatPrice, img: imageUrl },
}
</script>