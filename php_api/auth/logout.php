<?php
// POST /auth/logout.php — ยกเลิก token ฝั่งเซิร์ฟเวอร์
require_once __DIR__ . '/../core.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$user = require_auth($pdo);
$pdo->prepare("UPDATE users SET api_token=NULL WHERE id=?")->execute([$user['id']]);
json_out(['message' => 'ออกจากระบบแล้ว']);