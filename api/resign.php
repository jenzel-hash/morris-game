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

// If already finished, just say ok to avoid UX dead-ends
if ($room['status'] === 'finished') {
  json_out(['ok' => true, 'note' => 'Already finished']);
}

// Only a participant can resign
$is_p1 = ((int)$room['p1_id'] === $player_id);
$is_p2 = ((int)$room['p2_id'] === $player_id);
if (!$is_p1 && !$is_p2) {
  json_out(['ok' => false, 'error' => 'Not a participant'], 403);
}

// Determine winner (the other participant, if any)
$winner_id = null;
if ($is_p1) $winner_id = $room['p2_id'] ? (int)$room['p2_id'] : null;
if ($is_p2) $winner_id = $room['p1_id'] ? (int)$room['p1_id'] : null;

$pdo->beginTransaction();
try {
  // Finish the room; winner_id may be NULL if no opponent yet
  $pdo->prepare('UPDATE rooms SET status = "finished", winner_id = ? WHERE id = ?')
      ->execute([$winner_id, $room['id']]);

  if (!is_null($winner_id)) {
    $pdo->prepare('UPDATE players SET stars = stars + 1 WHERE id = ?')->execute([$winner_id]);
  }

  // Update state_json to include resignation info and winner
  $state = $room['state_json'] ? json_decode($room['state_json'], true) : [];
  $state['resigned'] = true;
  $state['resigned_by'] = $player_id;
  // Set winner symbol for frontend detection
  if ($winner_id) {
    if ((int)$room['p1_id'] === $winner_id) {
      $state['winner'] = 'W';
    } else if ((int)$room['p2_id'] === $winner_id) {
      $state['winner'] = 'B';
    }
  }
  $upd = $pdo->prepare('UPDATE rooms SET state_json = ? WHERE id = ?');
  $upd->execute([json_encode($state), $room['id']]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  json_out(['ok' => false, 'error' => 'Resign failed: ' . $e->getMessage()], 500);
}

json_out(['ok' => true]);
