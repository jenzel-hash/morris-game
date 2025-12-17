<?php
require __DIR__ . '/db.php';
$player_id = ensure_player();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$code = strtoupper(trim($input['code'] ?? ''));
if ($code === '') json_out(['ok' => false, 'error' => 'Room code required'], 400);

$stmt = $pdo->prepare('SELECT * FROM rooms WHERE code = ?');
$stmt->execute([$code]);
$room = $stmt->fetch();
if (!$room) json_out(['ok' => false, 'error' => 'Room not found'], 404);

if ($room['status'] === 'finished') json_out(['ok' => false, 'error' => 'Room already finished'], 400);

if ((int)$room['p1_id'] === $player_id || (int)$room['p2_id'] === $player_id) {
  json_out(['ok' => true, 'joined' => true, 'code' => $code]);
}

if ($room['p2_id'] === null) {
  $upd = $pdo->prepare('UPDATE rooms SET p2_id = ?, status = "playing" WHERE id = ?');
  $upd->execute([$player_id, $room['id']]);
  json_out(['ok' => true, 'joined' => true, 'code' => $code]);
}

json_out(['ok' => false, 'error' => 'Room full'], 400);
?>
