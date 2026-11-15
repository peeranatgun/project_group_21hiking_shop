<template>
  <div class="steps">
    <template v-for="(s, i) in steps" :key="s">
      <div class="step" :class="{ active: current === i + 1, done: current > i + 1 }">
        <span class="step-dot">{{ current > i + 1 ? '✓' : i + 1 }}</span>
        <span>{{ s }}</span>
      </div>
      <span v-if="i < steps.length - 1" class="step-line"></span>
    </template>
  </div>
</template>

<script>
export default {
  name: 'CheckoutSteps',
  props: {
    current: { type: Number, default: 1 },   // 1 = ตะกร้า, 2 = ข้อมูลจัดส่ง, 3 = สำเร็จ
  },
  data() {
    return { steps: ['ตะกร้า', 'ข้อมูลจัดส่ง', 'สั่งซื้อสำเร็จ'] }
  },
}
</script>