<?php
require __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = trim($input['name'] ?? '');
if ($name === '') json_out(['ok' => false, 'error' => 'Name required'], 400);

// Check if player already exists
$stmt = $pdo->prepare('SELECT id FROM players WHERE name = ?');
$stmt->execute([$name]);
$row = $stmt->fetch();

if (!$row) {
  $ins = $pdo->prepare('INSERT INTO players (name) VALUES (?)');
  $ins->execute([$name]);
  $player_id = (int)$pdo->lastInsertId();
} else {
  $player_id = (int)$row['id'];
}

// Store name + id in session
$_SESSION['player_id'] = $player_id;
$_SESSION['player_name'] = $name;

json_out(['ok' => true, 'player_id' => $player_id, 'name' => $name]);
?>
