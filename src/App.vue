<template>
  <div class="d-flex flex-column min-vh-100">
    <!-- แสดงเมนู -->
    <NavBar />

    <!-- แสดงหน้าเพจ -->
    <main class="flex-grow-1">
      <router-view />
    </main>

    <!-- แสดง Footer -->
    <AppFooter />
  </div>
</template>

<script>
import NavBar from './components/navbar.vue'
import AppFooter from './components/footer.vue'

export default {
  name: 'App',
  components: { NavBar, AppFooter },
}
</script>