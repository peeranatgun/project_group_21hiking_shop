<?php
require_once __DIR__ . '/../core.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$in = get_input();
$s = $pdo->prepare("SELECT * FROM users WHERE email=?");
$s->execute([trim($in['email'] ?? '')]);
$u = $s->fetch();

if (!$u || !password_verify($in['password'] ?? '', $u['password']))
  json_out(['error' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'], 401);

$token = bin2hex(random_bytes(32));
$pdo->prepare("UPDATE users SET api_token=? WHERE id=?")->execute([$token, $u['id']]);

json_out([
  'token' => $token,
  'user' => ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'],
             'phone' => $u['phone'], 'address' => $u['address'], 'role' => $u['role']],
]);