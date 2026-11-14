<?php
// เปิดครั้งเดียวผ่านเบราว์เซอร์ เพื่อสร้างแอดมิน แล้ว "ลบไฟล์นี้ทิ้ง"
// http://localhost/php_api/make_admin.php
require_once __DIR__ . '/core.php';
$email = 'admin@gg.com';
$pass  = 'admin1234';   // เปลี่ยนหลังล็อกอินครั้งแรก

$s = $pdo->prepare("SELECT id FROM users WHERE email=?");
$s->execute([$email]);
if ($s->fetch()) json_out(['message' => 'มีแอดมินอยู่แล้ว']);

$pdo->prepare("INSERT INTO users (name,email,password,role) VALUES ('Admin',?,?,'admin')")
    ->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
json_out(['message' => 'สร้างแอดมินแล้ว', 'email' => $email, 'password' => $pass]);