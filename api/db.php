<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Only set JSON header if running as API (not included from HTML page)
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
  header('Content-Type: application/json');
}

$DB_HOST = '127.0.0.1:3309';
$DB_NAME = 'morris_db';
$DB_USER = 'root';
$DB_PASS = '';    

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
  exit;
}

// Helper: send JSON response
function json_out($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

// Helper: ensure user is registered
function ensure_player() {
  if (!isset($_SESSION['player_id'])) {
    json_out(['ok' => false, 'error' => 'Not registered'], 401);
  }
  return (int)$_SESSION['player_id'];
}

// Helper: random room code
function random_code($length = 6) {
  $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $res = '';
  for ($i = 0; $i < $length; $i++) {
    $res .= $chars[random_int(0, strlen($chars) - 1)];
  }
  return $res;
}

// Helper: initial game state
function initial_state() {
  $positions = [
    'a1','a4','a7','b2','b4','b6','c3','c4','c5',
    'd1','d2','d3','d5','d6','d7','e3','e4','e5',
    'f2','f4','f6','g1','g4','g7'
  ];
  $board = [];
  foreach ($positions as $p) $board[$p] = null;

  return [
    'board' => $board,
    'turn' => 'W',
    'phase' => 'placing',
    'piecesPlaced' => ['W' => 0, 'B' => 0],
    'piecesOnBoard' => ['W' => 0, 'B' => 0],
    'removalPending' => false,
    'winner' => null,
    'lastAction' => null
  ];
}
// Keep name cached in session
if (isset($_SESSION['player_id']) && !isset($_SESSION['player_name'])) {
  $st = $pdo->prepare('SELECT name FROM players WHERE id = ?');
  $st->execute([$_SESSION['player_id']]);
  if ($row = $st->fetch()) $_SESSION['player_name'] = $row['name'];
}

?>
