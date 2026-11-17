<template>
  <div ref="containerRef" class="jacket-3d-wrapper">
    <div v-if="isLoading" class="loading-badge">กำลังโหลดโมเดลเสื้อ 3D...</div>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';

const containerRef = ref(null);
const isLoading = ref(true);

let scene, camera, renderer, controls, animationFrameId, pmremGenerator;
let jacketModel = null;

onMounted(() => {
  initScene();
  animate();
  window.addEventListener('resize', onWindowResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', onWindowResize);
  cancelAnimationFrame(animationFrameId);
  if (pmremGenerator) pmremGenerator.dispose();
  if (renderer) renderer.dispose();
});

function initScene() {
  const container = containerRef.value;
  const width = container.clientWidth || 500;
  const height = container.clientHeight || 450;

  // 1. สร้าง Scene พื้นหลังโปร่งใส
  scene = new THREE.Scene();

  // 2. ตั้งค่ากล้อง
  camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
  camera.position.set(0, 0.2, 4.5);

  // 3. ตั้งค่า Renderer ให้รองรับแสง PBR สมจริง
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setClearColor(0x000000, 0);
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.1;
  container.appendChild(renderer.domElement);

  // 4. จำลองแสงสตูดิโอรอบทิศทาง (ช่วยให้โมเดล .glb สว่างและเห็นรายละเอียดเนื้อผ้าชัด)
  pmremGenerator = new THREE.PMREMGenerator(renderer);
  scene.environment = pmremGenerator.fromScene(new RoomEnvironment(), 0.04).texture;

  const dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
  dirLight.position.set(5, 8, 5);
  scene.add(dirLight);

  // 5. ระบบควบคุมด้วยเมาส์ (หมุน 360 องศา)
  controls = new OrbitControls(camera, renderer.domElement);
  controls.target.set(0, 0, 0);
  controls.enableDamping = true;
  controls.dampingFactor = 0.05;
  controls.enablePan = false;
  controls.minDistance = 2.5;
  controls.maxDistance = 8;

  // 6. โหลดโมเดลเสื้อ .glb จาก public/models/jacket.glb
  const loader = new GLTFLoader();
  loader.load(
    '/models/jacket.glb',
    (gltf) => {
      jacketModel = gltf.scene;

      // จัดกึ่งกลางและปรับขนาดโมเดลให้อัตโนมัติ (ไม่ว่าไฟล์จาก AI จะเล็กหรือใหญ่แค่ไหน)
      const box = new THREE.Box3().setFromObject(jacketModel);
      const center = box.getCenter(new THREE.Vector3());
      const size = box.getSize(new THREE.Vector3());
      const maxDim = Math.max(size.x, size.y, size.z);
      const scale = 2.6 / maxDim; // ปรับความสูงเสื้อให้พอดีเฟรม

      jacketModel.scale.setScalar(scale);
      jacketModel.position.sub(center.multiplyScalar(scale));

      scene.add(jacketModel);
      isLoading.value = false;
    },
    undefined,
    (error) => {
      console.error('โหลดไฟล์โมเดลไม่สำเร็จ:', error);
      isLoading.value = false;
    }
  );
}

function animate() {
  animationFrameId = requestAnimationFrame(animate);

  // หมุนโชว์ตัวเสื้อช้าๆ แบบ 360 องศา
  if (jacketModel) {
    jacketModel.rotation.y += 0.008;
  }

  controls.update();
  renderer.render(scene, camera);
}

function onWindowResize() {
  if (!containerRef.value) return;
  const width = containerRef.value.clientWidth;
  const height = containerRef.value.clientHeight;
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
  renderer.setSize(width, height);
}
</script>

<style scoped>
.jacket-3d-wrapper {
  position: relative;
  display: block;
  width: 100%;
  height: 450px;
  cursor: grab;
}

.jacket-3d-wrapper:active {
  cursor: grabbing;
}

.hint-badge,
.loading-badge {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(63, 91, 69, 0.85);
  color: #ffffff;
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  white-space: nowrap;
  pointer-events: none;
  backdrop-filter: blur(4px);
}
</style>