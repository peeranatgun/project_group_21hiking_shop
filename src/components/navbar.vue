import api from '../api'
<template>
  <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
      <router-link class="navbar-brand brand" to="/">21 HIKING</router-link>

      <button
        class="navbar-toggler border-0 shadow-none"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-label="เมนู"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/products">สินค้า</router-link>
          </li>
          <li class="nav-item" v-if="isLoggedIn">
            <router-link class="nav-link" to="/orders"
              >คำสั่งซื้อของฉัน</router-link
            >
          </li>
          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link" to="/admin">จัดการร้าน</router-link>
          </li>
        </ul>

        <ul class="navbar-nav align-items-lg-center gap-lg-2">
          <li class="nav-item">
            <router-link class="nav-link" to="/cart">
              ตะกร้า<span v-if="cartCount" class="badge bg-dark ms-1">{{
                cartCount
              }}</span>
            </router-link>
          </li>
          <template v-if="!isLoggedIn">
            <li class="nav-item">
              <router-link class="nav-link" to="/login"
                >เข้าสู่ระบบ</router-link
              >
            </li>
            <li class="nav-item">
              <router-link class="btn btn-dark btn-sm px-3" to="/register"
                >สมัครสมาชิก</router-link
              >
            </li>
          </template>
          <template v-else>
            <li class="nav-item">
              <router-link class="nav-link small" to="/profile"
                >สวัสดี, {{ user?.name }}</router-link
              >
            </li>
            <li class="nav-item">
              <button class="btn btn-outline-dark btn-sm px-3" @click="logout">
                ออกจากระบบ
              </button>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: "NavBar",
  computed: {
    isLoggedIn() {
      return this.$store.getters.isLoggedIn;
    },
    cartCount() {
      return this.$store.getters.cartCount;
    },
    isAdmin() {
      return this.$store.getters.isAdmin;
    },
    user() {
      return this.$store.state.user;
    },
  },
  logout() {
  api.post('/auth/logout.php').catch(() => {})   // ยกเลิก token ฝั่งเซิร์ฟเวอร์
  this.$store.commit('logout')
  this.$router.push('/')

  },
};
</script>
