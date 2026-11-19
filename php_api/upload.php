<?php
require_once __DIR__ . '/core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ตรวจสอบไฟล์ที่ส่งมาจาก FormData key ชื่อ 'image'
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'ไม่พบไฟล์รูปภาพหรืออัปโหลดผิดพลาด']);
    exit;
}

$file = $_FILES['image'];

// ตรวจสอบขนาดไฟล์ (ไม่เกิน 3MB ตามที่ระบุในหน้า AdminProducts.vue)
if ($file['size'] > 3 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'ขนาดไฟล์ต้องไม่เกิน 3MB']);
    exit;
}

// ตรวจสอบประเภทไฟล์จริง (MIME Type)
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);
    echo json_encode(['error' => 'รองรับเฉพาะไฟล์ JPG, PNG, WEBP เท่านั้น']);
    exit;
}

// เตรียมโฟลเดอร์ php_api/uploads/
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ตั้งชื่อไฟล์ใหม่ให้เป็นรูปแบบเดียวกับ slip_... ในโฟลเดอร์ uploads
$ext = $allowedTypes[$mimeType];
$fileName = 'product_' . uniqid() . '.' . $ext;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $target)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $fullUrl = "{$protocol}://{$host}{$basePath}/uploads/{$filename}";

    echo json_encode([
        'url' => $fullUrl
    ]);

} else {
    http_response_code(500);
    echo json_encode(['error' => 'บันทึกไฟล์ลงโฟลเดอร์ uploads ไม่สำเร็จ']);
}