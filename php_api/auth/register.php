<?php
require_once __DIR__ . '/../core.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$in = get_input();
$name = trim($in['name'] ?? '');
$email = trim($in['email'] ?? '');
$pass = $in['password'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6)
  json_out(['error' => 'กรอกข้อมูลให้ครบ และรหัสผ่านอย่างน้อย 6 ตัวอักษร'], 422);

$s = $pdo->prepare("SELECT id FROM users WHERE email=?");
$s->execute([$email]);
if ($s->fetch()) json_out(['error' => 'อีเมลนี้ถูกใช้แล้ว'], 409);

$pdo->prepare("INSERT INTO users (name,email,password,phone,address) VALUES (?,?,?,?,?)")
    ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $in['phone'] ?? null, $in['address'] ?? null]);

json_out(['message' => 'สมัครสมาชิกสำเร็จ'], 201);