<template>
  <nav class="navbar navbar-expand-lg site-nav sticky-top">
    <div class="container position-relative" style="z-index: 2;">
      <router-link class="navbar-brand brand" to="/">21 HIKING</router-link>

      <button class="navbar-toggler border-0 shadow-none" type="button"
              data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="เมนู">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><router-link class="nav-link" to="/products">สินค้า</router-link></li>
          <li class="nav-item" v-if="isLoggedIn">
            <router-link class="nav-link" to="/orders">คำสั่งซื้อของฉัน</router-link>
          </li>
          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link" to="/admin">จัดการร้าน</router-link>
          </li>
        </ul>

        <ul class="navbar-nav align-items-lg-center gap-lg-2">
          <li class="nav-item">
            <router-link class="nav-link" to="/cart">
              <svg class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 7h12l-1 12H7L6 7z" />
                <path d="M9 7a3 3 0 0 1 6 0" />
              </svg>ตะกร้า<span v-if="cartCount" class="badge bg-dark ms-1">{{ cartCount }}</span>
            </router-link>
          </li>
          <template v-if="!isLoggedIn">
            <li class="nav-item"><router-link class="nav-link" to="/login">เข้าสู่ระบบ</router-link></li>
            <li class="nav-item">
              <router-link class="btn btn-dark btn-sm px-3" to="/register">สมัครสมาชิก</router-link>
            </li>
          </template>
          <template v-else>
            <li class="nav-item">
              <router-link class="nav-link small" to="/profile">สวัสดี, {{ user?.name }}</router-link>
            </li>
            <li class="nav-item">
              <button type="button" class="btn btn-outline-dark btn-sm px-3" @click="logout">ออกจากระบบ</button>
            </li>
          </template>
        </ul>
      </div>
    </div>

    <!-- ภูเขาและคนเดินป่าขนาดเล็ก ตรงกลางขอบล่าง Navbar -->
    <div class="center-hiker-decor" aria-hidden="true">
      <svg viewBox="0 0 160 44">
        <!-- ภูเขาลูกเล็กโทน #3F5B45 -->
        <polygon points="8,44 58,16 104,44" fill="#8ca391" opacity="0.45" />
        <polygon points="44,44 96,10 152,44" fill="#3F5B45" />

        <!-- ธงเล็กๆ บนยอดเขา (96, 10) -->
        <line x1="96" y1="10" x2="96" y2="2" stroke="#111111" stroke-width="1.1" />
        <polygon points="96,2 103,4 96,6" fill="#d9534f" />

        <!-- คนเดินป่าสีดำเดินขึ้นทางลาดซ้ายไปยอดเขา -->
        <g class="mini-hiker">
          <g transform="translate(0, -12)">
            <rect x="-3.8" y="3" width="3" height="5" rx="1" fill="#111111" />
            <circle cx="0" cy="1.5" r="1.7" fill="#111111" />
            <path d="M -0.8,3.5 L 1.2,3.5 L 0.8,8 L -1.2,8 Z" fill="#111111" />
            <line x1="2" y1="4.2" x2="3.5" y2="12" stroke="#111111" stroke-width="0.9" stroke-linecap="round" />
            <line class="leg-l" x1="-0.4" y1="7.8" x2="-1.8" y2="12" stroke="#111111" stroke-width="1.2" stroke-linecap="round" />
            <line class="leg-r" x1="0.4" y1="7.8" x2="1.8" y2="12" stroke="#111111" stroke-width="1.2" stroke-linecap="round" />
          </g>
        </g>
      </svg>
    </div>
  </nav>
</template>

<script>
import api from '../api'

export default {
  name: 'NavBar',
  computed: {
    isLoggedIn() { return this.$store.getters.isLoggedIn },
    isAdmin() { return this.$store.getters.isAdmin },
    cartCount() { return this.$store.getters.cartCount },
    user() { return this.$store.state.user },
  },
  methods: {
    logout() {
      const token = this.$store.state.token
      this.$store.commit('logout')   // ล้างข้อมูลล็อกอินในเครื่องก่อนเสมอ
      this.$router.push('/')
      // แจ้งเซิร์ฟเวอร์ให้ยกเลิก token (ถ้าล้มเหลวก็ไม่กระทบการออกจากระบบ)
      api.post('/auth/logout.php', null, {
        headers: { Authorization: `Bearer ${token}` },
      }).catch(() => {})
    },
  },
}
</script>

<style scoped>
.site-nav {
  position: sticky;
  top: 0;
  overflow: hidden;
}

/* จัดให้อยู่กึ่งกลางแนวนอน ชิดขอบล่างสุดของ Navbar */
.center-hiker-decor {
  position: absolute;
  left: 50%;
  bottom: 0;
  transform: translateX(-50%);
  width: 150px;
  height: 40px;
  pointer-events: none;
  z-index: 1;
}

.center-hiker-decor svg {
  width: 100%;
  height: 100%;
  display: block;
}

/* ไต่ขึ้นตามแนวสันเขาจาก (48, 41) ไปยอด (93, 12) */
.mini-hiker {
  animation: miniClimb 7s linear infinite;
}

.leg-l {
  transform-origin: -0.4px 7.8px;
  animation: stepL 0.55s ease-in-out infinite alternate;
}

.leg-r {
  transform-origin: 0.4px 7.8px;
  animation: stepR 0.55s ease-in-out infinite alternate;
}

@keyframes miniClimb {
  0% {
    transform: translate(48px, 41px) rotate(-33deg);
    opacity: 0;
  }
  12% {
    opacity: 1;
  }
  88% {
    transform: translate(91px, 13px) rotate(-33deg);
    opacity: 1;
  }
  100% {
    transform: translate(95px, 10.5px) rotate(0deg);
    opacity: 0;
  }
}

@keyframes stepL {
  0% { transform: rotate(-22deg); }
  100% { transform: rotate(22deg); }
}

@keyframes stepR {
  0% { transform: rotate(22deg); }
  100% { transform: rotate(-22deg); }
}
</style>