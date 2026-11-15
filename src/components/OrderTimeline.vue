<template>
  <div v-if="status === 'cancelled'" class="alert alert-secondary py-2 small mb-3">
    คำสั่งซื้อนี้ถูกยกเลิก
  </div>

  <div v-else class="timeline">
    <div v-for="(s, i) in steps" :key="s.key" class="tl-item"
         :class="{ done: i <= index, current: i === index }">
      <div class="tl-dot">{{ i < index || (i === index && status === 'completed') ? '✓' : i + 1 }}</div>
      <div>{{ s.label }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrderTimeline',
  props: {
    status: { type: String, default: 'pending' },
  },
  data() {
    return {
      steps: [
        { key: 'pending', label: 'รอชำระเงิน' },
        { key: 'paid', label: 'ชำระเงินแล้ว' },
        { key: 'shipped', label: 'จัดส่งแล้ว' },
        { key: 'completed', label: 'สำเร็จ' },
      ],
    }
  },
  computed: {
    index() { return this.steps.findIndex((s) => s.key === this.status) },
  },
}
</script>