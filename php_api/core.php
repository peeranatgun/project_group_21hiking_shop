<?php
require_once __DIR__ . '/config.php';

// ---------- CORS + JSON ----------
header("Access-Control-Allow-Origin: " . ALLOWED_ORIGIN);
if (ALLOWED_ORIGIN !== '*') header("Vary: Origin");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ไม่แสดง error ของ PHP ให้ผู้ใช้เห็น (กัน path/โครงสร้างระบบรั่ว) แต่ยังบันทึกลง log
ini_set('display_errors', '0');
set_exception_handler(function ($e) {
  error_log($e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'เกิดข้อผิดพลาดในระบบ'], JSON_UNESCAPED_UNICODE);
});

// ---------- DB ----------
try {
  $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  error_log($e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'เชื่อมต่อฐานข้อมูลไม่ได้'], JSON_UNESCAPED_UNICODE);
  exit;
}

// ---------- helpers ----------
function json_out($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function get_input() {
  $d = json_decode(file_get_contents('php://input'), true);
  return is_array($d) ? $d : [];
}

function bearer_token() {
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
  if (!$h && function_exists('getallheaders')) {
    foreach (getallheaders() as $k => $v) if (strtolower($k) === 'authorization') $h = $v;
  }
  return preg_match('/Bearer\s+(\S+)/', $h, $m) ? $m[1] : null;
}

function require_auth($pdo) {
  $t = bearer_token();
  if (!$t) json_out(['error' => 'กรุณาเข้าสู่ระบบ'], 401);
  $s = $pdo->prepare("SELECT id,name,email,phone,address,role FROM users WHERE api_token=?");
  $s->execute([$t]);
  $u = $s->fetch();
  if (!$u) json_out(['error' => 'โทเคนไม่ถูกต้อง'], 401);
  return $u;
}

function require_admin($pdo) {
  $u = require_auth($pdo);
  if ($u['role'] !== 'admin') json_out(['error' => 'สำหรับแอดมินเท่านั้น'], 403);
  return $u;
}