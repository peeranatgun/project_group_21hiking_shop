<?php
// GET    /products/index.php            รายการสินค้า (?q=ค้นหา &category=)
// GET    /products/index.php?id=1       รายละเอียดสินค้า
// POST   /products/index.php            เพิ่มสินค้า   (admin)
// PUT    /products/index.php?id=1       แก้ไขสินค้า   (admin)
// DELETE /products/index.php?id=1       ลบสินค้า      (admin)
require_once __DIR__ . '/../core.php';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  if (isset($_GET['id'])) {
    $s = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $s->execute([(int)$_GET['id']]);
    $p = $s->fetch();
    if (!$p) json_out(['error' => 'ไม่พบสินค้า'], 404);
    json_out($p);
  }
  $sql = "SELECT * FROM products WHERE 1";
  $params = [];
  if (!empty($_GET['q']))        { $sql .= " AND name LIKE ?";  $params[] = '%' . $_GET['q'] . '%'; }
  if (!empty($_GET['category'])) { $sql .= " AND category=?";   $params[] = $_GET['category']; }
  $s = $pdo->prepare($sql . " ORDER BY id DESC");
  $s->execute($params);
  json_out($s->fetchAll());
}

require_admin($pdo);
$in = get_input();

if ($method === 'POST' || $method === 'PUT') {
  $name = trim($in['name'] ?? '');
  $price = $in['price'] ?? null;
  if ($name === '' || !is_numeric($price) || $price < 0)
    json_out(['error' => 'กรุณากรอกชื่อและราคาให้ถูกต้อง'], 422);

  $vals = [$name, $in['description'] ?? '', $price, (int)($in['stock'] ?? 0),
           $in['sizes'] ?? 'S,M,L,XL', $in['category'] ?? null, $in['image'] ?? null];

  if ($method === 'POST') {
    $pdo->prepare("INSERT INTO products (name,description,price,stock,sizes,category,image) VALUES (?,?,?,?,?,?,?)")
        ->execute($vals);
    json_out(['message' => 'เพิ่มสินค้าสำเร็จ', 'id' => $pdo->lastInsertId()], 201);
  }

  $id = (int)($_GET['id'] ?? 0);
  $vals[] = $id;
  $pdo->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,sizes=?,category=?,image=? WHERE id=?")
      ->execute($vals);
  json_out(['message' => 'แก้ไขสินค้าสำเร็จ']);
}

if ($method === 'DELETE') {
  try {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)($_GET['id'] ?? 0)]);
    json_out(['message' => 'ลบสินค้าสำเร็จ']);
  } catch (PDOException $e) {
    json_out(['error' => 'ลบไม่ได้ เพราะสินค้านี้มีอยู่ในคำสั่งซื้อ'], 409);
  }
}

json_out(['error' => 'Method not allowed'], 405);