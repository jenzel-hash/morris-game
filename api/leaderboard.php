<?php
require __DIR__ . '/db.php';

$default_img = 'assets/whale.jpg';

// Get only top 20 players with points (descending = highest first)
$stmt = $pdo->query('SELECT id, name, stars, profile_img FROM players WHERE stars > 0 ORDER BY stars DESC, name ASC LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$leaders = [];
foreach ($rows as $r) {
    $points = (int)$r['stars'];
    $player_id = (int)$r['id'];
    // Count games played (as p1 or p2 in finished rooms)
    $gamesStmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE (p1_id = ? OR p2_id = ?) AND status = "finished"');
    $gamesStmt->execute([$player_id, $player_id]);
    $games = (int)$gamesStmt->fetchColumn();
    // Count wins
    $winsStmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE winner_id = ? AND status = "finished"');
    $winsStmt->execute([$player_id]);
    $wins = (int)$winsStmt->fetchColumn();
    $leaders[] = [
        'name' => $r['name'],
        'points' => $points,
        'games' => $games,
        'wins' => $wins,
        'profile_img' => $r['profile_img'] ?: $default_img
    ];
}
json_out(['ok' => true, 'leaders' => $leaders]);
?>
