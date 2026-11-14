<?php
// GET  /orders/index.php          admin = ทุกออเดอร์, user = ของตัวเอง
// GET  /orders/index.php?id=1     รายละเอียดออเดอร์ + รายการสินค้า
// POST /orders/index.php          สั่งซื้อ  body: {shipping_name, shipping_phone, shipping_address, items:[{product_id,size,quantity}]}
// PUT  /orders/index.php?id=1     เปลี่ยนสถานะ (admin) body: {status}
require_once __DIR__ . '/../core.php';
$user = require_auth($pdo);
$isAdmin = $user['role'] === 'admin';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  if (isset($_GET['id'])) {
    $s = $pdo->prepare("SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?");
    $s->execute([(int)$_GET['id']]);
    $o = $s->fetch();
    if (!$o || (!$isAdmin && $o['user_id'] != $user['id'])) json_out(['error' => 'ไม่พบคำสั่งซื้อ'], 404);
    $i = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
    $i->execute([$o['id']]);
    $o['items'] = $i->fetchAll();
    json_out($o);
  }
  $sql = "SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id=o.user_id";
  $params = [];
  if (!$isAdmin) { $sql .= " WHERE o.user_id=?"; $params[] = $user['id']; }
  $s = $pdo->prepare($sql . " ORDER BY o.id DESC");
  $s->execute($params);
  json_out($s->fetchAll());
}

if ($method === 'POST') {
  $in = get_input();
  $items = $in['items'] ?? [];
  foreach (['shipping_name', 'shipping_phone', 'shipping_address'] as $f)
    if (trim($in[$f] ?? '') === '') json_out(['error' => 'กรุณากรอกข้อมูลจัดส่งให้ครบ'], 422);
  if (!is_array($items) || !count($items)) json_out(['error' => 'ตะกร้าว่าง'], 422);

  try {
    $pdo->beginTransaction();
    $total = 0;
    $rows = [];
    foreach ($items as $it) {
      $qty = (int)($it['quantity'] ?? 0);
      if ($qty < 1) throw new Exception('จำนวนสินค้าไม่ถูกต้อง');
      $s = $pdo->prepare("SELECT * FROM products WHERE id=? FOR UPDATE");
      $s->execute([(int)($it['product_id'] ?? 0)]);
      $p = $s->fetch();
      if (!$p) throw new Exception('ไม่พบสินค้าบางรายการ');
      if ($p['stock'] < $qty) throw new Exception("สินค้า {$p['name']} มีไม่พอ");
      $total += $p['price'] * $qty;
      $rows[] = [$p['id'], $it['size'] ?? '', $qty, $p['price']];  // ราคาใช้จาก DB เสมอ
    }
    $pdo->prepare("INSERT INTO orders (user_id,total,shipping_name,shipping_phone,shipping_address) VALUES (?,?,?,?,?)")
        ->execute([$user['id'], $total, $in['shipping_name'], $in['shipping_phone'], $in['shipping_address']]);
    $oid = $pdo->lastInsertId();
    $ins = $pdo->prepare("INSERT INTO order_items (order_id,product_id,size,quantity,price) VALUES (?,?,?,?,?)");
    $dec = $pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?");
    foreach ($rows as $r) {
      $ins->execute([$oid, $r[0], $r[1], $r[2], $r[3]]);
      $dec->execute([$r[2], $r[0]]);
    }
    $pdo->commit();
    json_out(['message' => 'สั่งซื้อสำเร็จ', 'order_id' => $oid, 'total' => $total], 201);
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_out(['error' => $e->getMessage()], 400);
  }
}

if ($method === 'PUT') {
  if (!$isAdmin) json_out(['error' => 'สำหรับแอดมินเท่านั้น'], 403);
  $id = (int)($_GET['id'] ?? 0);
  $status = get_input()['status'] ?? '';
  if (!in_array($status, ['pending', 'paid', 'shipped', 'completed', 'cancelled'], true))
    json_out(['error' => 'สถานะไม่ถูกต้อง'], 422);

  $s = $pdo->prepare("SELECT status FROM orders WHERE id=?");
  $s->execute([$id]);
  $old = $s->fetchColumn();
  if (!$old) json_out(['error' => 'ไม่พบคำสั่งซื้อ'], 404);

  $pdo->beginTransaction();
  $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $id]);
  if ($status === 'cancelled' && $old !== 'cancelled') {   // ยกเลิก = คืนสต็อก
    $pdo->prepare("UPDATE products p JOIN order_items oi ON oi.product_id=p.id
                   SET p.stock=p.stock+oi.quantity WHERE oi.order_id=?")->execute([$id]);
  }
  $pdo->commit();
  json_out(['message' => 'อัปเดตสถานะแล้ว']);
}

json_out(['error' => 'Method not allowed'], 405);