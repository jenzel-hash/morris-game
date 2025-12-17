<?php
require __DIR__ . '/db.php';
$player_id = ensure_player();

$data = json_decode(file_get_contents('php://input'), true);
$stone_img = $data['stone_img'] ?? null;

// Allow null to clear the stone image

$stmt = $pdo->prepare('UPDATE players SET stone_img = ? WHERE id = ?');
$stmt->execute([$stone_img, $player_id]);

json_out(['ok' => true]);
?>
