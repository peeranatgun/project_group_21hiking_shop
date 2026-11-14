<?php
// ตั้งค่าที่ต้องเปลี่ยนเมื่อขึ้นเว็บจริง (อย่าอัปโหลดไฟล์นี้ขึ้น Git)
define('DB_HOST', 'localhost');
define('DB_NAME', 'hiking_shop');
define('DB_USER', 'root');   // เว็บจริง: ใช้ user เฉพาะของฐานข้อมูลนี้ ไม่ใช้ root
define('DB_PASS', '');       // เว็บจริง: ตั้งรหัสผ่านที่เดายาก

// โดเมนของหน้าเว็บที่อนุญาตให้เรียก API
// ตอนพัฒนาใช้ '*' ได้ เว็บจริงให้ใส่โดเมนจริง เช่น 'https://www.example.com'
define('ALLOWED_ORIGIN', '*');