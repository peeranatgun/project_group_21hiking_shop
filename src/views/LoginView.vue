<template>
  <section class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <h3 class="fw-semibold text-center mb-4">เข้าสู่ระบบ</h3>

        <div v-if="$route.query.registered" class="alert alert-success py-2 small">
          สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ
        </div>
        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

        <form @submit.prevent="submit" class="card p-4">
          <div class="mb-3">
            <label class="form-label small">อีเมล</label>
            <input v-model.trim="email" type="email" class="form-control" required autofocus />
          </div>
          <div class="mb-4">
            <label class="form-label small">รหัสผ่าน</label>
            <input v-model="password" type="password" class="form-control" required />
          </div>
          <button class="btn btn-dark w-100" :disabled="loading">
            {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
          </button>
        </form>

        <p class="text-center small text-muted mt-3">
          ยังไม่มีบัญชี?
          <router-link to="/register" class="text-dark">สมัครสมาชิก</router-link>
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'

export default {
  name: 'LoginView',
  data() {
    return { email: '', password: '', error: '', loading: false }
  },
  methods: {
    async submit() {
      this.error = ''
      this.loading = true
      try {
        const { data } = await api.post('http://localhost/project-21hiking-shop/php_api/auth/login.php', {
          email: this.email,
          password: this.password,
        })
        this.$store.commit('setAuth', { token: data.token, user: data.user })
        this.$router.push(data.user.role === 'admin' ? '/admin' : '/')
      } catch (e) {
        this.error = e.response?.data?.error || 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>