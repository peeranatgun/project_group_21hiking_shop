<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="text-muted small">{{ products.length }} รายการ</span>
      <button class="btn btn-dark btn-sm px-3" @click="newProduct">+ เพิ่มสินค้า</button>
    </div>

    <div v-if="msg" class="alert alert-success py-2 small">{{ msg }}</div>
    <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

    <!-- ฟอร์มเพิ่ม/แก้ไข -->
    <form v-if="form" @submit.prevent="save" class="card p-4 mb-4">
      <h6 class="mb-3">{{ editingId ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' }}</h6>
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label small">ชื่อสินค้า</label>
          <input v-model.trim="form.name" class="form-control" required />
        </div>
        <div class="col-md-4">
          <label class="form-label small">หมวดหมู่</label>
          <input v-model.trim="form.category" class="form-control" />
        </div>
        <div class="col-12">
          <label class="form-label small">รายละเอียด</label>
          <textarea v-model.trim="form.description" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label small">ราคา (บาท)</label>
          <input v-model.number="form.price" type="number" min="0" step="0.01" class="form-control" required />
        </div>
        <div class="col-md-4">
          <label class="form-label small">สต็อก</label>
          <input v-model.number="form.stock" type="number" min="0" class="form-control" required />
        </div>
        <div class="col-md-4">
          <label class="form-label small">ไซส์ (คั่นด้วยคอมมา)</label>
          <input v-model.trim="form.sizes" class="form-control" placeholder="S,M,L,XL" />
        </div>
        <div class="col-12">
          <label class="form-label small">รูปสินค้า (JPG, PNG, WEBP ไม่เกิน 3MB)</label>
          <input type="file" accept="image/jpeg,image/png,image/webp" class="form-control" @change="onFile" />
          <div v-if="uploading" class="small text-muted mt-1">กำลังอัปโหลด...</div>
          <img v-if="img(form.image)" :src="img(form.image)" alt="" class="mt-2 rounded"
               style="height: 90px; width: 90px; object-fit: cover" />
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark px-4" :disabled="saving || uploading">{{ saving ? 'กำลังบันทึก...' : 'บันทึก' }}</button>
        <button type="button" class="btn btn-outline-dark px-4" @click="form = null">ยกเลิก</button>
      </div>
    </form>

    <div v-if="loading" class="text-center text-muted py-5">กำลังโหลด...</div>

    <div v-else class="card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="small text-muted">
            <tr><th></th><th>สินค้า</th><th class="text-end">ราคา</th><th class="text-end">สต็อก</th><th></th></tr>
          </thead>
          <tbody>
            <tr v-for="p in products" :key="p.id">
              <td style="width: 64px">
                <div class="bg-light rounded" style="width: 48px; height: 48px; overflow: hidden">
                  <img v-if="img(p.image)" :src="img(p.image)" alt="" class="w-100 h-100" style="object-fit: cover" />
                </div>
              </td>
              <td>
                <div class="fw-semibold">{{ p.name }}</div>
                <div class="text-muted small">{{ p.category }} &middot; {{ p.sizes }}</div>
              </td>
              <td class="text-end">{{ price(p.price) }}</td>
              <td class="text-end">{{ p.stock }}</td>
              <td class="text-end text-nowrap">
                <button class="btn btn-sm btn-outline-dark me-1" @click="edit(p)">แก้ไข</button>
                <button class="btn btn-sm btn-outline-danger" @click="remove(p)">ลบ</button>
              </td>
            </tr>
            <tr v-if="!products.length">
              <td colspan="5" class="text-center text-muted py-4">ยังไม่มีสินค้า</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../api'
import { formatPrice, imageUrl } from '../utils'

const empty = () => ({ name: '', description: '', price: '', stock: 0, sizes: 'S,M,L,XL', category: '', image: '' })

export default {
  name: 'AdminProducts',
  data() {
    return {
      products: [], form: null, editingId: null,
      loading: true, saving: false, uploading: false, error: '', msg: '',
    }
  },
  created() { this.load() },
  methods: {
    price: formatPrice,
    img: imageUrl,
    async load() {
      try {
        const { data } = await api.get('/products/index.php')
        this.products = data
      } catch (e) {
        this.error = 'โหลดสินค้าไม่สำเร็จ'
      } finally {
        this.loading = false
      }
    },
    newProduct() {
      this.editingId = null
      this.form = empty()
      this.error = this.msg = ''
    },
    edit(p) {
      this.editingId = p.id
      this.form = { ...p }
      this.error = this.msg = ''
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    async onFile(e) {
      const file = e.target.files[0]
      if (!file) return
      this.error = ''
      this.uploading = true
      try {
        const fd = new FormData()
        fd.append('image', file)
        const { data } = await api.post('/upload.php', fd)
        this.form.image = data.url
      } catch (err) {
        this.error = err.response?.data?.error || 'อัปโหลดรูปไม่สำเร็จ'
      } finally {
        this.uploading = false
      }
    },
    async save() {
      this.error = this.msg = ''
      this.saving = true
      try {
        if (this.editingId) {
          await api.put('/products/index.php', this.form, { params: { id: this.editingId } })
        } else {
          await api.post('/products/index.php', this.form)
        }
        this.msg = this.editingId ? 'แก้ไขสินค้าแล้ว' : 'เพิ่มสินค้าแล้ว'
        this.form = null
        await this.load()
      } catch (e) {
        this.error = e.response?.data?.error || 'บันทึกไม่สำเร็จ'
      } finally {
        this.saving = false
      }
    },
    async remove(p) {
      if (!confirm(`ลบสินค้า "${p.name}" ?`)) return
      this.error = this.msg = ''
      try {
        await api.delete('/products/index.php', { params: { id: p.id } })
        this.msg = 'ลบสินค้าแล้ว'
        await this.load()
      } catch (e) {
        this.error = e.response?.data?.error || 'ลบไม่สำเร็จ'
      }
    },
  },
}
</script>