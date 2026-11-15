<template>
  <section class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <h3 class="fw-semibold text-center mb-4">สมัครสมาชิก</h3>

        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

        <form @submit.prevent="submit" class="card p-4">
          <div class="mb-3">
            <label class="form-label small">ชื่อ-นามสกุล</label>
            <input v-model.trim="form.name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label small">อีเมล</label>
            <input v-model.trim="form.email" type="email" class="form-control" required />
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small">รหัสผ่าน (อย่างน้อย 6 ตัว)</label>
              <input v-model="form.password" type="password" class="form-control" minlength="6" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small">ยืนยันรหัสผ่าน</label>
              <input v-model="confirm" type="password" class="form-control" required />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small">เบอร์โทร (ไม่บังคับ)</label>
            <input v-model.trim="form.phone" class="form-control" />
          </div>
          <div class="mb-4">
            <label class="form-label small">ที่อยู่จัดส่ง (ไม่บังคับ)</label>
            <textarea v-model.trim="form.address" class="form-control" rows="2"></textarea>
          </div>
          <button class="btn btn-dark w-100" :disabled="loading">
            {{ loading ? 'กำลังสมัคร...' : 'สมัครสมาชิก' }}
          </button>
        </form>

        <p class="text-center small text-muted mt-3">
          มีบัญชีอยู่แล้ว?
          <router-link to="/login" class="text-dark">เข้าสู่ระบบ</router-link>
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'

export default {
  name: 'RegisterView',
  data() {
    return {
      form: { name: '', email: '', password: '', phone: '', address: '' },
      confirm: '',
      error: '',
      loading: false,
    }
  },
  methods: {
    async submit() {
      this.error = ''
      if (this.form.password !== this.confirm) {
        this.error = 'รหัสผ่านไม่ตรงกัน'
        return
      }
      this.loading = true
      try {
        await api.post('/auth/register.php', this.form)
        this.$router.push({ path: '/login', query: { registered: 1 } })
      } catch (e) {
        this.error = e.response?.data?.error || 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>