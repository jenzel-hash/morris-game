<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// If the user is not logged in, redirect to login
if (!isset($_SESSION['player_id'])) {
  header("Location: login.php");
  exit();
}

$code = strtoupper(trim($_GET['code'] ?? ''));
if ($code === '' || !preg_match('/^[A-Z0-9]{1,10}$/', $code)) {  // Basic validation: alphanumeric, 1-10 chars
  header('Location: index.php');
  exit;
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Morris Game Room <?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></title>

  <link rel="stylesheet" href="assets/style.css" />


  <style>
    /* ========= PAGE BACKGROUND ========= */
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
      background: url('BackG.jpg') center/cover no-repeat fixed;
      position: relative;
      color: #E1F4FF;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: radial-gradient(circle at 20% 0%,
          rgba(210, 219, 235, 0.25),
          rgba(1, 22, 43, 0.94));
      z-index: -1;
    }

    /* ========= TOP BAR ========= */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      margin: 16px auto 20px;
      max-width: 960px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.97), rgba(0, 56, 90, 0.95));
      border-radius: 18px;
      border: 1px solid rgba(148, 162, 191, 0.85);
      box-shadow:
        0 18px 40px rgba(0, 0, 0, 0.9),
        0 0 24px rgba(106, 144, 180, 0.7);
    }

    .back {
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 999px;
      border: 1px solid rgba(117, 226, 224, 0.9);
      background: rgba(2, 77, 96, 0.95);
      color: #D9F5F0;
      font-weight: 600;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.7);
    }

    .back:hover {
      filter: brightness(1.08);
    }

    .room-title {
      font-family: 'Orbitron', sans-serif;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      font-size: 0.9rem;
      color: #F5F7FF;
      text-shadow: 0 0 10px rgba(117, 226, 224, 0.7);
    }

    .room-title strong {
      color: #75E2E0;
      border-radius: 999px;
      padding: 4px 12px;
      background: rgba(2, 77, 96, 0.98);
      border: 1px solid rgba(117, 226, 224, 0.95);
      box-shadow: 0 0 10px rgba(117, 226, 224, 0.95);
    }

    #resign {
      border-radius: 999px;
      padding: 8px 18px;
      border: 1px solid rgba(255, 190, 190, 0.9) !important;
      background: linear-gradient(135deg, #e66a5a, #b3362d);
      color: #FFF5F5;
      font-family: 'Orbitron', sans-serif;
      font-size: 0.8rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.8);
      cursor: pointer;
    }

    /* ========= MAIN LAYOUT ========= */
    .container {
      max-width: 960px;
      margin: 0 auto 32px;
      padding: 0 16px 24px;
    }

    .grid {
      display: grid;
      grid-template-columns: 1.4fr 0.9fr;
      gap: 16px;
      align-items: flex-start;
    }

    @media (max-width: 900px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }


    .card {
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.97), rgba(0, 56, 90, 0.95));
      border-radius: 22px;
      padding: 18px 18px 20px;
      border: 1px solid rgba(148, 162, 191, 0.9);
      box-shadow:
        0 18px 40px rgba(0, 0, 0, 0.9),
        0 0 24px rgba(106, 144, 180, 0.7);
      color: #E1F4FF;
    }

    h2 {
      margin: 0 0 10px;
      font-family: 'Orbitron', sans-serif;
      font-size: 1.1rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: #F5F7FF;
    }

    /* ========= BOARD AREA ========= */
    #boardWrap {
      margin-top: 6px;
      width: 100%;
      min-height: 340px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #boardWrap svg {
      max-width: 420px;
      width: 100%;
    }

    /* ========= STATUS AREA ========= */
    .status {
      margin-bottom: 10px;
      font-size: 0.82rem;
    }

    #players .badge,
    #turn .badge,
    #phase .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      background: rgba(2, 77, 96, 0.98);
      color: #F5F7FF;
      font-size: 0.75rem;
      margin-right: 6px;
    }

    #hint,
    #stoneInfo {
      margin-top: 4px;
      color: #C1D4FF;
      font-size: 0.8rem;
    }

    /* ========= END GAME MODAL ========= */
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 200;
    }

    .modal.hidden {
      display: none;
    }

    .modal-content {
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.97), rgba(0, 56, 90, 0.96));
      border-radius: 22px;
      padding: 24px 24px 22px;
      border: 1px solid rgba(148, 162, 191, 0.9);
      box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.9),
        0 0 30px rgba(106, 144, 180, 0.7);
      text-align: center;
      color: #F5F7FF;
      max-width: 360px;
      width: 90%;
    }

    .modal-content h2 {
      margin-bottom: 8px;
    }

    #leaveBtn {
      margin-top: 12px;
      width: 100%;
      padding: 9px 14px;
      border-radius: 14px;
      border: none !important;
      background: linear-gradient(135deg, #00385a, #6a90b4);
      color: #F5F7FF;
      font-family: 'Orbitron', sans-serif;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.8);
    }

    /* ========= LOADING TEXT ========= */
    #loading {
      text-align: center;
      color: #F5F7FF;
      font-size: 0.95rem;
      margin-top: 18px;
      text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
    }



    .status-card {
      max-width: 320px;
    }

    .status-bar {
      margin-top: 14px;
      padding: 8px 10px;
      border-radius: 999px;
      background: rgba(2, 77, 96, 0.98);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.7);
    }

    .status-players {
      display: flex;
      align-items: center;
      gap: 5px;
      margin: 4px 0 10px;
    }

    /* make the two player badges sit in one row */
    /* bigger player status chips */
    #players {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #players .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 14px;
      border-radius: 999px;
      background: rgba(0, 56, 90, 0.96);
      color: #F5F7FF;
      font-size: 12px;
      /* bigger text */
      font-weight: 600;
      box-shadow: 0 5px 14px rgba(0, 0, 0, 0.7);
    }

    .status-info {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px;
      margin-left: 13px;
    }

    /* generic badge look reused for turn + phase */
    #turn .badge,
    #phase .badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 11px;
      border-radius: 999px;
      font-size: 0.98rem;
      font-weight: 600;
      letter-spacing: 0.07em;
      text-transform: uppercase;
      color: #FDFEFF;
      background: rgba(0, 56, 90, 0.96);
      border: 1px solid rgba(117, 226, 224, 0.85);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.7);
    }

    /* optional subtle color difference for whose turn it is */
    #turn .turnW {
      background: linear-gradient(135deg, #1b7fb5, #75e2e0);
    }

    #turn .turnB {
      background: linear-gradient(135deg, #b3362d, #e66a5a);
    }


    #players .badge,
    #turn .badge,
    #phase .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 9px;
      border-radius: 999px;
      background: rgba(2, 77, 96, 0.98);
      color: #F5F7FF;
      font-size: 0.89rem;
    }

    .status-text {
      margin-top: 18px;
      padding: 8px 10px;
      border-radius: 14px;
      background: rgba(1, 22, 43, 0.9);
      border: 1px solid rgba(148, 162, 191, 0.7);
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.65);
    }

    #hint {
      font-size: 0.9rem;
      font-weight: 600;
      color: #F5F7FF;
      margin-bottom: 10px;
    }

    #stoneInfo {
      font-size: 0.8rem;
      color: #C1D4FF;
      opacity: 0.98;
    }

    /* BOARD AREA */
    .Wrap {
      margin-top: 10px;
      width: 100%;
      min-height: 360px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #boardWrap svg {
      max-width: 420px;
      width: 100%;
      border-radius: 24px;
      border: 1px solid rgba(253, 230, 166, 0.9);
      padding: 10px;
      background: radial-gradient(circle at 20% 0%,
          rgba(226, 245, 245, 0.18),
          rgba(0, 56, 90, 0.96));
      box-shadow:
        0 18px 40px rgba(0, 0, 0, 0.9),
        0 0 24px rgba(106, 144, 180, 0.7);
    }
.sea-sound-toggle {
  position: relative;
  display: inline-block;
  cursor: pointer;
  user-select: none;
  font-family: Charm;
}

/* hide checkbox */
.sea-sound-toggle input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.sea-sound-toggle .toggle-shell {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  padding: 4px 10px;
  width: 110px;
  height: 36px;
  border-radius: 999px;
  background: radial-gradient(circle at 20% 0%, #38bdf8 0%, #0b1220 45%, #020617 90%);
  box-shadow:
    0 0 0 1px rgba(56, 189, 248, 0.35),
    0 10px 25px rgba(15, 23, 42, 0.8);
  overflow: hidden;
}

.sea-sound-toggle .toggle-bubble {
  position: absolute;
  left: 4px;
  top: 4px;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: radial-gradient(circle at 30% 20%, #e0f2fe 0%, #38bdf8 45%, #0369a1 100%);
  box-shadow:
    0 0 12px rgba(56, 189, 248, 0.6),
    0 0 30px rgba(56, 189, 248, 0.25);
  transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1),
              box-shadow 0.3s ease;
}

/* labels */
.sea-sound-toggle .toggle-label {
  position: relative;
  z-index: 1;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #cbd5f5;
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.sea-sound-toggle .toggle-label.on {
  font-weight: 600;
}

.sea-sound-toggle .toggle-label.off {
  opacity: 0.45;
}

/* checked state (Muted) */
.sea-sound-toggle input:checked + .toggle-shell .toggle-bubble {
  transform: translateX(68px);
  box-shadow:
    0 0 8px rgba(15, 23, 42, 0.9),
    0 0 20px rgba(15, 23, 42, 0.6);
}

.sea-sound-toggle input:checked + .toggle-shell .toggle-label.on {
  opacity: 0.3;
  transform: translateX(-4px);
  color: black;
}

.sea-sound-toggle input:checked + .toggle-shell .toggle-label.off {
  opacity: 1;
  font-weight: 600;
  transform: translateX(2px);
}

/* subtle hover */
.sea-sound-toggle:hover .toggle-shell {
  box-shadow:
    0 0 0 1px rgba(56, 189, 248, 0.6),
    0 12px 28px rgba(15, 23, 42, 0.9);
}

.sound-toggle-wrap {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
  margin-bottom: 8px; /* tweak or remove as you like */
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


<audio id="stone-sound" preload="auto">
  <source src="assets/bubble.mp3" type="audio/mpeg">
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

  <div class="container" style="margin-top:18px;">

    <!-- ⭐ TOP BAR -->
    <div class="topbar">
      <a href="lobby.php" class="back">← Lobby</a>

      <div class="room-title">
        Room: <strong id="roomCode"><?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>

      <button id="resign" class="btn danger">Resign</button>
    </div>

    <!-- ⭐ MAIN GRID -->
    <div class="grid">



      <!-- LEFT: BOARD -->
      <div class="left card">

        <h2 style="margin-bottom:4px;"><center>Game Board </center></center></h2>
        <div id="themeStoneSelect"></div>
        <div id="boardWrap"></div>
        
      </div>


      <!-- RIGHT: STATUS CARD -->
      <div class="right card status-card">
        <h2 align="center" style="margin-bottom:18px; margin-top:10px;">Status</h2>

        <div class="status-players">
          <div id="players" style="margin-bottom:18px;"></div>
        </div>

        <div class="status-info">
          <div id="turn"></div>
          <div id="phase"></div>
        </div>

        <div class="status-text">
          <div id="hint" class="muted"></div>
          <div id="stoneInfo" class="muted small"></div>
        </div>
		<br>
		<div class="sound-toggle-wrap">
		  <label class="sea-sound-toggle">
			<input type="checkbox" id="soundToggle">
			<span class="toggle-shell">
			  <span class="toggle-bubble"></span>
			  <span class="toggle-label on">Sound</span>
			  <span class="toggle-label off">Muted</span>
			</span>
		  </label>
		</div>
		
      </div>


    </div>

    <!-- ⭐ END GAME MODAL -->
    <div id="endModal" class="modal hidden">
      <div class="modal-content">
        <h2 id="resultTitle">Result</h2>
        <p id="resultMsg"></p>
        <button id="leaveBtn">Leave Game</button>
      </div>
    </div>

    <!-- Loading indicator -->
    <div id="loading">Loading game...</div>

  </div>

  <script>
    // Pass room code securely
    window.__ROOM_CODE__ = <?php echo json_encode($code); ?>;

    // Hide loading once JS loads
    document.addEventListener('DOMContentLoaded', function () {
      document.getElementById('loading').style.display = 'none';
    });
  </script>
  <script src="assets/app.js"></script>
<script src="music.js"></script>
</body>

</html>