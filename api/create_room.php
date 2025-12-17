<?php
require __DIR__ . '/db.php';

// Ensure we always respond with JSON for this endpoint
header('Content-Type: application/json; charset=utf-8');

$player_id = ensure_player();

// Try to create a unique room code and insert the room.
// Use repeated attempts and handle duplicate-key race conditions gracefully.
$attempts = 0;
$maxAttempts = 10;
$inserted = false;
$code = '';
$state = json_encode(initial_state());

while (!$inserted && $attempts < $maxAttempts) {
  $attempts++;
  $code = random_code(6);
  try {
    $stmt = $pdo->prepare("INSERT INTO rooms (code, status, p1_id, state_json) VALUES (?, 'waiting', ?, ?)");
    $stmt->execute([$code, $player_id, $state]);
    $inserted = true;
    break;
  } catch (PDOException $e) {
    // If code already exists, try again. For other errors, stop and return generic message.
    $sqlState = $e->errorInfo[1] ?? null;
    // MySQL duplicate key error code is 1062
    if ($e->getCode() === '23000' || $sqlState === 1062) {
      // duplicate code, loop to try another code
      continue;
    }
    // Unexpected DB error; do not leak details to client
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create room (database error)']);
    exit;
  }
}

if (!$inserted) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Failed to create room (try again)']);
  exit;
}

// Success
// Log success for debugging (non-sensitive)
@error_log("[create_room] success: player_id={$player_id} code={$code}\n", 3, __DIR__ . '/../create_room.log');
echo json_encode(['ok' => true, 'code' => $code]);
exit;
?>