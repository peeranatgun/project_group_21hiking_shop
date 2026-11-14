<template>
  <section class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <h3 class="fw-semibold text-center mb-4">โปรไฟล์ของฉัน</h3>

        <div v-if="msg" class="alert alert-success py-2 small">{{ msg }}</div>
        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

        <form @submit.prevent="save" class="card p-4">
          <div class="mb-3">
            <label class="form-label small">อีเมล</label>
            <input :value="user.email" class="form-control" disabled />
          </div>
          <div class="mb-3">
            <label class="form-label small">ชื่อ-นามสกุล</label>
            <input v-model.trim="form.name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label small">เบอร์โทร</label>
            <input v-model.trim="form.phone" class="form-control" />
          </div>
          <div class="mb-4">
            <label class="form-label small">ที่อยู่จัดส่ง</label>
            <textarea v-model.trim="form.address" class="form-control" rows="3"></textarea>
          </div>

          <h6 class="mb-3">เปลี่ยนรหัสผ่าน <span class="text-muted small">(ไม่บังคับ)</span></h6>
          <div class="mb-3">
            <label class="form-label small">รหัสผ่านเดิม</label>
            <input v-model="pw.current" type="password" class="form-control" autocomplete="current-password" />
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small">รหัสผ่านใหม่</label>
              <input v-model="pw.next" type="password" class="form-control" autocomplete="new-password" />
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label small">ยืนยันรหัสผ่านใหม่</label>
              <input v-model="pw.confirm" type="password" class="form-control" autocomplete="new-password" />
            </div>
          </div>

          <button class="btn btn-dark w-100" :disabled="saving">{{ saving ? 'กำลังบันทึก...' : 'บันทึก' }}</button>
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../api'

export default {
  name: 'ProfileView',
  data() {
    const u = this.$store.state.user || {}
    return {
      form: { name: u.name || '', phone: u.phone || '', address: u.address || '' },
      pw: { current: '', next: '', confirm: '' },
      saving: false, msg: '', error: '',
    }
  },
  computed: {
    user() { return this.$store.state.user || {} },
  },
  methods: {
    async save() {
      this.msg = this.error = ''
      if (this.pw.next && this.pw.next !== this.pw.confirm) {
        this.error = 'รหัสผ่านใหม่ไม่ตรงกัน'
        return
      }
      this.saving = true
      try {
        const payload = { ...this.form }
        if (this.pw.next) {
          payload.current_password = this.pw.current
          payload.new_password = this.pw.next
        }
        const { data } = await api.put('http://localhost/project-21hiking-shop/php_api/auth/profile.php', payload)
        this.$store.commit('setUser', data.user)
        this.pw = { current: '', next: '', confirm: '' }
        this.msg = 'บันทึกข้อมูลแล้ว'
      } catch (e) {
        this.error = e.response?.data?.error || 'บันทึกไม่สำเร็จ'
      } finally {
        this.saving = false
      }
    },
  },
}
</script>