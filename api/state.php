<?php
require __DIR__ . '/db.php';
$player_id = ensure_player();

// --- GET: Fetch current room + state ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $code = strtoupper(trim($_GET['code'] ?? ''));
  if ($code === '')
    json_out(['ok' => false, 'error' => 'Room code required'], 400);

  $stmt = $pdo->prepare('SELECT r.*, p1.name AS p1_name, p1.profile_img AS p1_img, p1.stone_img AS p1_stone, p2.name AS p2_name, p2.profile_img AS p2_img, p2.stone_img AS p2_stone
    FROM rooms r
    LEFT JOIN players p1 ON p1.id = r.p1_id
    LEFT JOIN players p2 ON p2.id = r.p2_id
    WHERE r.code = ?');
  $stmt->execute([$code]);
  $room = $stmt->fetch();
  // Debug log: note requests for room state
  @error_log("[state] GET request for code={$code} player_id={$player_id} found=" . ($room ? '1' : '0') . "\n", 3, __DIR__ . '/../state_requests.log');
  if (!$room)
    json_out(['ok' => false, 'error' => 'Room not found'], 404);

  $you = null;
  if ($room['p1_id'] && (int) $room['p1_id'] === $player_id)
    $you = 'W';
  if ($room['p2_id'] && (int) $room['p2_id'] === $player_id)
    $you = 'B';

  $default_img = 'assets/whale.jpg';
  $default_stone_p1 = 'ISU.png';
  $default_stone_p2 = 'ccsict.png';
  json_out([
    'ok' => true,
    'status' => $room['status'],
    'code' => $room['code'],
    'players' => [
      'W' => [
        'name' => $room['p1_name'],
        'profile_img' => $room['p1_img'] ?: $default_img,
        'stone_img' => $room['p1_stone'] ?: $default_stone_p1
      ],
      'B' => [
        'name' => $room['p2_name'],
        'profile_img' => $room['p2_img'] ?: $default_img,
        'stone_img' => $room['p2_stone'] ?: $default_stone_p2
      ],
    ],
    'you' => $you,
    'state' => $room['state_json'] ? json_decode($room['state_json'], true) : null,
    'winner_id' => $room['winner_id']
  ]);
}

// --- POST: Update game state ---
$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$code = strtoupper(trim($payload['code'] ?? ''));
$state = $payload['state'] ?? null;

if ($code === '' || !$state)
  json_out(['ok' => false, 'error' => 'Invalid payload'], 400);

$stmt = $pdo->prepare('SELECT * FROM rooms WHERE code = ?');
$stmt->execute([$code]);
$room = $stmt->fetch();
if (!$room)
  json_out(['ok' => false, 'error' => 'Room not found'], 404);

if (!in_array($player_id, [(int) $room['p1_id'], (int) $room['p2_id']], true)) {
  json_out(['ok' => false, 'error' => 'Not a participant'], 403);
}

$winnerSymbol = $state['winner'] ?? null;
$pdo->beginTransaction();
try {
  $upd = $pdo->prepare('UPDATE rooms SET state_json = ?, updated_at = NOW() WHERE id = ?');
  $upd->execute([json_encode($state), $room['id']]);

  // If there's a winner, finish the game
  if ($winnerSymbol && $room['status'] !== 'finished') {
    $winner_id = ($winnerSymbol === 'W') ? (int) $room['p1_id'] : (int) $room['p2_id'];
    if ($winner_id) {
      $updRoom = $pdo->prepare('UPDATE rooms SET status = "finished", winner_id = ? WHERE id = ?');
      $updRoom->execute([$winner_id, $room['id']]);
      $updStar = $pdo->prepare('UPDATE players SET stars = stars + 1 WHERE id = ?');
      $updStar->execute([$winner_id]);
    }
  }
  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  json_out(['ok' => false, 'error' => 'Save failed: ' . $e->getMessage()], 500);
}

json_out(['ok' => true]);
?>