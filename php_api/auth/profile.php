<?php
// GET /auth/profile.php   ดูข้อมูลตัวเอง
// PUT /auth/profile.php   แก้ไข {name, phone, address, current_password?, new_password?}
require_once __DIR__ . '/../core.php';
$user = require_auth($pdo);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') json_out($user);
if ($method !== 'PUT') json_out(['error' => 'Method not allowed'], 405);

$in = get_input();
$name = trim($in['name'] ?? '');
if ($name === '') json_out(['error' => 'กรุณากรอกชื่อ'], 422);

$pdo->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?")
    ->execute([$name, trim($in['phone'] ?? ''), trim($in['address'] ?? ''), $user['id']]);

// เปลี่ยนรหัสผ่าน (ถ้ามีการส่งมา)
if (!empty($in['new_password'])) {
  if (strlen($in['new_password']) < 6) json_out(['error' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร'], 422);
  $s = $pdo->prepare("SELECT password FROM users WHERE id=?");
  $s->execute([$user['id']]);
  if (!password_verify($in['current_password'] ?? '', $s->fetchColumn()))
    json_out(['error' => 'รหัสผ่านเดิมไม่ถูกต้อง'], 403);
  $pdo->prepare("UPDATE users SET password=? WHERE id=?")
      ->execute([password_hash($in['new_password'], PASSWORD_DEFAULT), $user['id']]);
}

$s = $pdo->prepare("SELECT id,name,email,phone,address,role FROM users WHERE id=?");
$s->execute([$user['id']]);
json_out(['message' => 'บันทึกข้อมูลแล้ว', 'user' => $s->fetch()]);