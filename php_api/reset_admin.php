<?php
// รันครั้งเดียวผ่านเบราว์เซอร์ แล้วลบไฟล์นี้ทิ้งทันที
require_once __DIR__ . '/core.php';

$email = 'admin@21hiking.com';
$pass  = 'admin1234';
$hash  = password_hash($pass, PASSWORD_DEFAULT);

$s = $pdo->prepare("SELECT id FROM users WHERE email=?");
$s->execute([$email]);

if ($s->fetch()) {
  $pdo->prepare("UPDATE users SET password=?, role='admin', name='Admin' WHERE email=?")
      ->execute([$hash, $email]);
  $action = 'อัปเดตแอดมินที่มีอยู่แล้ว';
} else {
  $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES ('Admin',?,?,'admin')")
      ->execute([$email, $hash]);
  $action = 'สร้างแอดมินใหม่';
}

// ตรวจผล
$s = $pdo->prepare("SELECT password, role FROM users WHERE email=?");
$s->execute([$email]);
$row = $s->fetch();

json_out([
  'action'      => $action,
  'email'       => $email,
  'role'        => $row['role'],
  'hash_length' => strlen($row['password']),                // ควรเป็น 60
  'verify_ok'   => password_verify($pass, $row['password']), // ควรเป็น true
  'database'    => DB_NAME,
]);