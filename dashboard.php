<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['player_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Morris Game</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
        <div class="container" style="max-width:600px; margin-top: 36px;">
            <div class="topbar">
                <div style="font-size:1.5em; color:var(--accent); font-family:'Orbitron',Arial,sans-serif; text-shadow:0 0 10px var(--accent-glow);">MORRIS</div>
                <div>
                    <a href="logout.php" class="btn danger" style="padding:7px 16px; font-size:0.98em;">Logout</a>
                </div>
            </div>
            <div class="card" style="padding: 28px 20px 20px 20px; text-align:center;">
                <h2 style="margin-bottom:8px;">Welcome, <?php echo htmlspecialchars($_SESSION['player_name']); ?>!</h2>
                <div class="muted" style="margin-bottom:18px;">You are now logged in.</div>
                <div style="display:flex; flex-direction:column; gap:12px; align-items:center; margin-bottom:18px;">
                    <button id="playGameBtn" class="btn" style="width:80%;max-width:260px;">🎮 Play Game</button>
                    <a href="leaderboard.php" class="btn" style="width:80%;max-width:260px; background:var(--neon); color:#1d1d1d;">🏆 Leaderboard</a>
                </div>
                <div style="margin-top:18px; color:var(--muted); font-size:0.98em;">Ready to play? Join or create a room to start a match!</div>
            </div>
        </div>
<script>
async function post(url, data) {
    const res = await fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify(data)
    });
    return res.json();
}
document.getElementById('playGameBtn').onclick = async () => {
    const btn = document.getElementById('playGameBtn');
    btn.disabled = true;
    btn.textContent = 'Creating Room...';
    try {
        const r = await post('api/create_room.php', {});
        if (r.ok) window.location.href = 'game.php?code=' + r.code;
        else {
            alert(r.error || 'Failed to create room.');
            btn.disabled = false;
            btn.textContent = '🎮 Play Game';
        }
    } catch (e) {
        alert('Network error.');
        btn.disabled = false;
        btn.textContent = '🎮 Play Game';
    }
};
</script>
</body>
</html>
