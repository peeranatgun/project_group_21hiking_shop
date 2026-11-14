<?php
// POST /orders/slip.php  (multipart/form-data: order_id, slip)
// ลูกค้าแนบสลิปโอนเงินของออเดอร์ตัวเอง (เฉพาะออเดอร์ที่ยังรอชำระเงิน)
require_once __DIR__ . '/../core.php';
$user = require_auth($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$orderId = (int)($_POST['order_id'] ?? 0);
$s = $pdo->prepare("SELECT user_id, status FROM orders WHERE id=?");
$s->execute([$orderId]);
$order = $s->fetch();
if (!$order || $order['user_id'] != $user['id']) json_out(['error' => 'ไม่พบคำสั่งซื้อ'], 404);
if ($order['status'] !== 'pending') json_out(['error' => 'คำสั่งซื้อนี้ไม่อยู่ในสถานะรอชำระเงิน'], 409);

if (empty($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK)
  json_out(['error' => 'อัปโหลดไม่สำเร็จ'], 400);

$f = $_FILES['slip'];
if ($f['size'] > 3 * 1024 * 1024) json_out(['error' => 'ไฟล์ใหญ่เกิน 3MB'], 422);

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
$ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
if (!$ext) json_out(['error' => 'รองรับเฉพาะไฟล์ JPG, PNG, WEBP'], 422);

$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$name = 'slip_' . $orderId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) json_out(['error' => 'บันทึกไฟล์ไม่สำเร็จ'], 500);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$root = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $root . "/uploads/$name";

$pdo->prepare("UPDATE orders SET slip_image=? WHERE id=?")->execute([$url, $orderId]);
json_out(['message' => 'แนบสลิปแล้ว', 'url' => $url], 201);