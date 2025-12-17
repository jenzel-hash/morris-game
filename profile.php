<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['player_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/api/db.php';
$player_id = $_SESSION['player_id'];

// Fetch user info
$stmt = $pdo->prepare('SELECT name, profile_img FROM players WHERE id = ?');
$stmt->execute([$player_id]);
$user = $stmt->fetch();

// Fetch stats
$stats = $pdo->prepare('SELECT 
    SUM(CASE WHEN winner_id = ? THEN 1 ELSE 0 END) AS wins,
    SUM(CASE WHEN winner_id != ? AND (p1_id = ? OR p2_id = ?) AND winner_id IS NOT NULL THEN 1 ELSE 0 END) AS losses,
    COUNT(*) AS games
FROM rooms WHERE (p1_id = ? OR p2_id = ?) AND status = "finished"');
$stats->execute([$player_id, $player_id, $player_id, $player_id, $player_id, $player_id]);
$stat = $stats->fetch();
$winrate = ($stat['games'] > 0) ? round($stat['wins'] / $stat['games'] * 100) : 0;

// Fetch match history
$history = $pdo->prepare('SELECT code, created_at, winner_id, p1_id, p2_id, 
    (SELECT name FROM players WHERE id = rooms.p1_id) AS p1_name,
    (SELECT name FROM players WHERE id = rooms.p2_id) AS p2_name
FROM rooms WHERE (p1_id = ? OR p2_id = ?) AND status = "finished" ORDER BY created_at DESC LIMIT 30');
$history->execute([$player_id, $player_id]);
$matches = $history->fetchAll();

// Rank logic (example)
$rank = 'Beginner';
$rank_icon = '<i class="fa fa-star"></i>';
if ($stat['wins'] >= 20) { $rank = 'Legend'; $rank_icon = '<i class="fa fa-trophy" style="color:gold"></i>'; }
else if ($stat['wins'] >= 10) { $rank = 'Pro'; $rank_icon = '<i class="fa fa-gem" style="color:deepskyblue"></i>'; }
else if ($stat['wins'] >= 5) { $rank = 'Intermediate'; $rank_icon = '<i class="fa fa-medal" style="color:#cd7f32"></i>'; }

// 8 preset profile images
// 8 preset profile images
$profile_imgs = [
  'assets/whale.jpg',
  'assets/ray.jpg',
  'assets/star.jpg',
  'assets/jelly.jpg',
  'assets/clam.jpg',
  'assets/crab.jpg',
  'assets/sea.jpg',
  'assets/octo.jpg',
  'assets/pof.jpg',
  'assets/shark.jpg',
  'assets/nimo.jpg',
  'assets/cute.jpg',
];
$selected_img = $user['profile_img'] ?? $profile_imgs[0];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
  $new_name = trim($_POST['name'] ?? '');
  $new_img = $_POST['profile_img'] ?? $profile_imgs[0];
  // Validate name
  if ($new_name !== '' && mb_strlen($new_name) <= 32) {
    // Only allow selection from preset images
    if (in_array($new_img, $profile_imgs)) {
      $stmt = $pdo->prepare('UPDATE players SET name = ?, profile_img = ? WHERE id = ?');
      $stmt->execute([$new_name, $new_img, $player_id]);
      // Update session and local variables
      $user['name'] = $new_name;
      $user['profile_img'] = $new_img;
      $selected_img = $new_img;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Morris Game</title>
  <link rel="stylesheet" href="assets/style.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .profile-pics { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; margin: 12px 0 18px 0; }
    .profile-pic-choice { border: 2.5px solid var(--accent); border-radius: 50%; width: 64px; height: 64px; cursor: pointer; transition: border 0.2s, box-shadow 0.2s; object-fit:cover; }
    .profile-pic-choice.selected { border: 3.5px solid var(--neon); box-shadow: 0 0 12px var(--neon); }
    .profile-main { display: flex; flex-direction: column; align-items: center; margin-bottom: 18px; }
    .profile-main img { border-radius: 50%; width: 90px; height: 90px; border: 3.5px solid var(--accent-glow); box-shadow: 0 0 16px var(--accent-glow); object-fit:cover; }
    .profile-rank { font-size: 1.2em; margin-top: 6px; color: var(--accent); font-weight: 700; }
    .profile-form { margin-bottom: 18px; }
    .profile-form input[type=text] { width: 220px; text-align: center; }
    .profile-stats { color:black;display: flex; gap: 18px; justify-content: center; margin-bottom: 18px; }
    .profile-stats .stat { background: var(--panel); border-radius: 10px; padding: 10px 18px; font-size: 1.1em; color: var(--neon); font-weight: 700; box-shadow: 0 1px 6px var(--accent-glow); }
    .profile-history { margin-top: 18px; }
    .profile-history table { width: 100%; border-radius: 10px; overflow: hidden; background: var(--panel); }
    .profile-history th, .profile-history td { padding: 8px 10px; text-align: center; }
    .profile-history th { background: var(--accent); color: #fff; }
    .profile-history td.win { color: var(--neon); font-weight: 700; }
    .profile-history td.lose { color: var(--danger); font-weight: 700; }
	
	body {
  margin: 0;
  min-height: 100vh;
  font-family: "Segoe UI", system-ui, -apple-system, Roboto, sans-serif;
  color: #d2dbeb;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: url('BackG.jpg') center/cover no-repeat fixed;
}

body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: radial-gradient(circle at 20% 0%, rgba(210,219,235,0.12), rgba(1,22,43,0.9));
  z-index: -1;
}

.container {
  max-width: 720px;
  width: 100%;
  padding: 32px 16px 40px;
  margin-top:5px;
}

/* reuse .topbar from style.css; just tweak spacing */
.topbar h2 {
  margin: 0;
  color: #d2dbeb;
}

/* Glass profile card */
.card {
  margin-top: 18px;
  border-radius: 22px;
  padding: 22px 22px 24px;
  background: linear-gradient(145deg, rgba(1,22,43,0.93), rgba(0,56,90,0.9));
  border: 1px solid rgba(148,162,191,0.7);
  box-shadow:
    0 18px 40px rgba(0,0,0,0.85),
    0 0 30px rgba(106,144,180,0.6);
  color: #d2dbeb;
}

.profile-main {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 18px;
}

.profile-main img {
  border-radius: 50%;
  width: 96px;
  height: 96px;
  border: 3px solid #6a90b4;
  box-shadow: 0 0 18px rgba(148,162,191,0.9);
  object-fit: cover;
}

.profile-rank {
  font-size: 1.05rem;
  margin-top: 8px;
  color: #d2dbeb;
  font-weight: 600;
}

.profile-form {
  margin-bottom: 18px;
  text-align: center;
}

.profile-form label {
  color: #94a2bf;
  font-weight: 600;
}

.profile-form input[type=text] {
  width: 230px;
  text-align: center;
  padding: 8px 10px;
  border-radius: 12px;
  border: 1px solid #6a90b4;
  background: rgba(1,22,43,0.85);
  color: #d2dbeb;
}

.profile-pics {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin: 12px 0 18px 0;
}

.profile-pic-choice {
  border: 2px solid #6a90b4;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  cursor: pointer;
  transition: border 0.2s, box-shadow 0.2s, transform 0.15s;
  object-fit: cover;
}

.profile-pic-choice.selected {
  border: 3px solid #d2dbeb;
  box-shadow: 0 0 12px rgba(210,219,235,0.8);
  transform: translateY(-2px);
}

.profile-stats {
  display: flex;
  gap: 14px;
  justify-content: center;
  margin-bottom: 18px;
}

.profile-stats .stat {
  min-width: 90px;
  background: rgba(0,56,90,0.9);
  border-radius: 14px;
  padding: 10px 14px;
  font-size: 0.95rem;
  color: #d2dbeb;
  font-weight: 600;
  text-align: center;
  box-shadow: 0 1px 6px rgba(0,0,0,0.6);
}

.profile-history {
  margin-top: 12px;
}

.profile-history h3 {
  margin-bottom: 8px;
  color: #d2dbeb;
}

.profile-history table {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  background: rgba(0,56,90,0.9);
  border: 1px solid rgba(148,162,191,0.6);
}

.profile-history th,
.profile-history td {
  padding: 8px 10px;
  text-align: center;
  font-size: 0.9rem;
}

.profile-history th {
  background: #00385a;
  color: #d2dbeb;
}

.profile-history td {
  color: #d2dbeb;
}

.profile-history td.win {
  color: #d2dbeb;
  font-weight: 700;
}

.profile-history td.lose {
  color: #ff8f8f;
  font-weight: 700;
}

.btn-primary {
  width: 100%;
  padding: 10px 14px;
  border-radius: 16px;
  border: none;
  background: linear-gradient(135deg, #00385a, #6a90b4);
  color: #f5f7ff;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(0,0,0,0.8);
}
.btn-primary:hover {
  filter: brightness(1.05);
}

.topbar .back {
  color: #000000;
}
.topbar h1 {
  color: #000000;
}





    /* ===== OCEAN LOBBY BACKGROUND (palette-based) ===== */

    /* main gradient */
    .lobby-bg {
      position: fixed;
      inset: 0;
      z-index: -4;
      background: radial-gradient(circle at 20% 0%, #d2dbeb 0, #6a90b4 45%, #00385a 75%, #01162b 100%);
      background-size: 220% 220%;
      animation: lobbyOceanGradient 28s ease-in-out infinite alternate;
    }

    /* wave bands */
    .lobby-waves {
      position: fixed;
      inset: 0;
      z-index: -3;
      pointer-events: none;
    }

    .lobby-wave {
      position: absolute;
      left: -10%;
      width: 120%;
      height: 18vh;
      border-radius: 50%;
      filter: blur(14px);
      opacity: 0.55;
      background: linear-gradient(to right,
          rgba(0, 56, 90, 0.0),
          rgba(0, 56, 90, 0.75),
          rgba(106, 144, 180, 0.9),
          rgba(148, 162, 191, 0.0));
      animation: lobbyWaveDrift 30s ease-in-out infinite;
    }

    .lobby-wave.mid {
      height: 22vh;
      background: linear-gradient(to right,
          rgba(1, 22, 43, 0.0),
          rgba(1, 22, 43, 0.85),
          rgba(0, 56, 90, 0.8),
          rgba(106, 144, 180, 0.0));
      animation-duration: 38s;
    }

    .lobby-wave.top {
      height: 16vh;
      background: linear-gradient(to right,
          rgba(210, 219, 235, 0.0),
          rgba(148, 162, 191, 0.55),
          rgba(210, 219, 235, 0.0));
      animation-duration: 46s;
      opacity: 0.45;
    }

    /* bubbles */
    .lobby-bubbles {
      position: fixed;
      inset: 0;
      z-index: -2;
      pointer-events: none;
    }

    .lobby-bubble,
    .auth-bubble {
      position: absolute;
      width: var(--size, 32px);
      height: var(--size, 32px);
      border-radius: 50%;
      overflow: hidden;
      /* important to clip the image in circle */
      border: 1px solid rgba(210, 219, 235, 0.7);
      background: transparent;
      /* no gradient now */
      box-shadow: 0 0 18px rgba(210, 219, 235, 0.75), 0 0 40px rgba(148, 162, 191, 0.5);
      opacity: 0;
      animation: lobbyBubbleRise 26s linear infinite;
      /* or bubbleRise */
    }

    .bubble-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }


    .lobby-bubble.sm {
      width: 20px;
      height: 20px;
      animation-duration: 22s;
    }

    .lobby-bubble.lg {
      width: 46px;
      height: 46px;
      animation-duration: 30s;
    }

    /* seaweed */
    .lobby-seaweed-layer {
      position: fixed;
      left: 0;
      right: 0;
      bottom: -10vh;
      height: 35vh;
      z-index: -1;
      pointer-events: none;
      display: flex;
      justify-content: space-around;
      opacity: 0.7;
    }

    .lobby-seaweed {
      width: 18px;
      height: 100%;
      background: linear-gradient(to top, #00385a, #6a90b4);
      border-radius: 50% 50% 0 0;
      transform-origin: bottom center;
      animation: lobbySeaweedSway 18s ease-in-out infinite;
      filter: blur(1px);
    }

    .lobby-seaweed:nth-child(2) {
      height: 80%;
      animation-duration: 22s;
    }

    .lobby-seaweed:nth-child(3) {
      height: 90%;
      animation-duration: 20s;
    }

    /* keyframes */
    @keyframes lobbyOceanGradient {
      0% {
        background-position: 0% 0%;
      }

      50% {
        background-position: 50% 80%;
      }

      100% {
        background-position: 100% 20%;
      }
    }

    @keyframes lobbyWaveDrift {
      0% {
        transform: translate3d(-8%, 0, 0);
      }

      50% {
        transform: translate3d(10%, -8%, 0);
      }

      100% {
        transform: translate3d(-6%, 4%, 0);
      }
    }

    @keyframes lobbyBubbleRise {
      0% {
        transform: translate3d(var(--x-start), 105vh, 0) scale(0.85);
        opacity: 0;
      }

      15% {
        opacity: 0.7;
      }

      60% {
        opacity: 0.6;
      }

      100% {
        transform: translate3d(var(--x-end), -20vh, 0) scale(1.15);
        opacity: 0;
      }
    }

    @keyframes lobbyFishSwim {
      0% {
        transform: translateX(-15vw) translateY(var(--y)) scaleX(1);
      }

      50% {
        transform: translateX(55vw) translateY(calc(var(--y) - 3vh)) scaleX(1.02);
      }

      100% {
        transform: translateX(115vw) translateY(var(--y)) scaleX(1);
      }
    }

    @keyframes lobbySeaweedSway {
      0% {
        transform: rotate(-4deg);
      }

      50% {
        transform: rotate(5deg);
      }

      100% {
        transform: rotate(-4deg);
      }
    }

    /* Image base background */
    .lobby-bg-img {
      position: fixed;
      inset: 0;
      z-index: -4;
      background: url('BackG.jpg') center/cover no-repeat;
    }

    /* Optional dark blue overlay so text is readable */
    .lobby-bg-overlay {
      position: fixed;
      inset: 0;
      z-index: -3;
      background: radial-gradient(circle at 20% 0%, rgba(0, 0, 0, 0.25), rgba(1, 22, 43, 0.85));
    }
  </style>
</head>
<body>

	<audio id="lobby-music" loop>
	  <source src="bgm.mp3" type="audio/mpeg">
	</audio>


  <!-- OCEAN BACKGROUND -->
  <div class="lobby-bg-img"></div>
  <div class="lobby-bg-overlay"></div>

  <div class="lobby-bubbles">
    <!-- Small bubble, moves left to right -->
    <div class="lobby-bubble" style="--x-start: 5vw; --x-end: 25vw; --size: 34px; animation-delay: 0s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble" style="--x-start: 5vw; --x-end: 25vw; --size: 34px; animation-delay: 0s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>

    <!-- Medium bubble, slower, different path -->
    <div class="lobby-bubble"
      style="--x-start: 60vw; --x-end: 40vw; --size: 36px; animation-delay: 2s; animation-duration: 32s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 50vw; --x-end: 80vw; --size: 36px; animation-delay: 4s; animation-duration: 32s;">
      <img src="ISU.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 60vw; --x-end: 70vw; --size: 26px; animation-delay: 3s; animation-duration: 32s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 60vw; --x-end: 50vw; --size: 26px; animation-delay: 4s; animation-duration: 32s;">
      <img src="ISU.png" alt="" class="bubble-img">
    </div>

    <!-- Large bubble, long diagonal path -->
    <div class="lobby-bubble"
      style="--x-start: 20vw; --x-end: 80vw; --size: 42px; animation-delay: 7s; animation-duration: 40s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 20vw; --x-end: 30vw; --size: 36px; animation-delay: 4s; animation-duration: 32s;">
      <img src="ISU.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 30vw; --x-end: 20vw; --size: 42px; animation-delay: 10s; animation-duration: 40s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 60vw; --x-end: 50vw; --size: 36px; animation-delay: 8s; animation-duration: 32s;">
      <img src="ISU.png" alt="" class="bubble-img">
    </div>
    <div class="lobby-bubble"
      style="--x-start: 10vw; --x-end: 40vw; --size: 26px; animation-delay: 9s; animation-duration: 32s;">
      <img src="ccsict.png" alt="" class="bubble-img">
    </div>

  </div>

  <div class="container">
    <div class="topbar">
      <a href="lobby.php" class="back">← Lobby</a>
      <h2>Profile</h2>
    </div>
    <div class="card">
      <div class="profile-main">
        <img id="profileImgDisplay" src="<?php echo htmlspecialchars($selected_img); ?>" alt="Profile Picture">
        <div class="profile-rank" style="color:white;">Rank: <?php echo $rank_icon . ' ' . $rank; ?></div>
      </div>
      <form class="profile-form" action="profile.php" method="post">
        <input type="hidden" name="action" value="update">
        <label for="name" style="color:var(--neon); font-weight:700;">Display Name:</label><br>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" maxlength="32" required><br>
        <input type="hidden" name="profile_img" id="profileImgInput" value="<?php echo htmlspecialchars($selected_img); ?>">
        <div class="profile-pics">
          <?php foreach ($profile_imgs as $img): ?>
            <img src="<?php echo htmlspecialchars($img); ?>" class="profile-pic-choice<?php if ($img === $selected_img) echo ' selected'; ?>" data-img="<?php echo htmlspecialchars($img); ?>">
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn-primary">Save Changes</button>
      </form>
      <div class="profile-stats">
        <div class="stat"style="color:white;">Winrate<br><?php echo $winrate; ?>%</div>
        <div class="stat" style="color:white;">Wins<br><?php echo $stat['wins']; ?></div>
        <div class="stat" style="color:white;">Losses<br><?php echo $stat['losses']; ?></div>
      </div>
      <div class="profile-history">
        <h3 style="margin-bottom:8px; color:var(--accent);">Match History</h3>
        <table>
          <thead>
            <tr><th>Date</th><th>Opponent</th><th>Result</th></tr>
          </thead>
          <tbody>
            <?php foreach ($matches as $m):
              $isWin = $m['winner_id'] == $player_id;
              $opponent = ($m['p1_id'] == $player_id) ? $m['p2_name'] : $m['p1_name'];
              $date = date('Y-m-d H:i', strtotime($m['created_at']));
            ?>
            <tr>
              <td><?php echo $date; ?></td>
              <td><?php echo htmlspecialchars($opponent); ?></td>
              <td class="<?php echo $isWin ? 'win' : 'lose'; ?>"><?php echo $isWin ? 'Win' : 'Lose'; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <script>
    // Profile picture selection
    document.querySelectorAll('.profile-pic-choice').forEach(img => {
      img.onclick = function() {
        document.querySelectorAll('.profile-pic-choice').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('profileImgInput').value = this.getAttribute('data-img');
        document.getElementById('profileImgDisplay').src = this.getAttribute('data-img');
      };
    });
  </script>  
  <script src="music.js"></script>
</body>
</html>
