<template>
  <footer class="site-footer mt-5">
    <div class="container py-4">
      <div class="row gy-3 align-items-center">
        <div class="col-md-4">
          <span class="brand">21 HIKING</span>
          <p class="text-muted small mb-0">เสื้อเดินป่าคุณภาพ เบา แห้งเร็ว ใส่สบาย</p>
        </div>
        <div class="col-md-4 text-md-center">
          <router-link class="footer-link me-3" to="/products">สินค้า</router-link>
          <router-link class="footer-link" to="/orders">คำสั่งซื้อ</router-link>
        </div>
        <div class="col-md-4 text-md-end text-muted small">
          &copy; {{ year }} 21 Hiking Shop
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'AppFooter',
  data() {
    return { year: new Date().getFullYear() }
  },
}
</script>