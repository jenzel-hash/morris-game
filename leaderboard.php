<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Leaderboard - Nine Men's Morris</title>

  <!-- GLOBAL STYLE -->
  <link rel="stylesheet" href="assets/style.css" />


  <!-- ICONS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 40px 16px;

      /* Background image + dark ocean overlay */
      background: url('BackG.jpg') center/cover no-repeat fixed;
      position: relative;
      color: #E1F4FF;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: radial-gradient(circle at 20% 0%,
          rgba(210, 219, 235, 0.15),
          rgba(1, 22, 43, 0.92));
      z-index: -1;
    }


    .leaderboard-container {
      width: 100%;
      max-width: 780px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.96), rgba(0, 56, 90, 0.94));
      border-radius: 24px;
      padding: 26px 22px 24px;
      border: 1px solid rgba(148, 162, 191, 0.8);
      box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.85),
        0 0 30px rgba(106, 144, 180, 0.7);
      animation: fade 0.6s ease-out;
      color: #E1F4FF;
    }

    .leaderboard-card {
      background: rgba(0, 56, 90, 0.85);
      border-radius: 18px;
      padding: 14px 14px 16px;
      border: 1px solid rgba(148, 162, 191, 0.6);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
    }

    @keyframes fade {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Top bar */
    .topbar-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .back-btn {
      text-decoration: none;
      font-size: 0.95rem;
      color: #D9F5F0;
      font-weight: 600;
      padding: 8px 14px;
      border-radius: 999px;
      border: 1px solid rgba(117, 226, 224, 0.7);
      background: rgba(2, 77, 96, 0.85);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .back-btn:hover {
      filter: brightness(1.08);
    }

    .leaderboard-title {
      font-size: 1.6rem;
      color: #D9F5F0;
      font-family: 'Orbitron', sans-serif;
      text-shadow: 0 0 14px rgba(117, 226, 224, 0.8);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 10px;
    }



    /* Table */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
      font-size: 0.9rem;
    }

    th {
      text-align: left;
      padding: 8px 10px;
      color: #D9F5F0;
      text-transform: uppercase;
      font-family: 'Orbitron', sans-serif;
      font-size: 0.75rem;
      letter-spacing: 0.08em;
      border-bottom: 1px solid rgba(148, 162, 191, 0.6);
    }

    td {
      padding: 9px 10px;
      background: rgba(21, 39, 64, 0.9);
      border-radius: 10px;
      color: #E1F4FF;
      font-weight: 500;
    }

    /* TOP 3 rows with ocean-highlighted medals */
    tbody tr:nth-child(1) td {
      background: linear-gradient(135deg, #1C4EA7, #75E2E0);
      font-weight: 700;
      color: #0B1A30;
    }

    tbody tr:nth-child(2) td {
      background: linear-gradient(135deg, #487F86, #D9F5F0);
      font-weight: 600;
      color: #0B1A30;
    }

    tbody tr:nth-child(3) td {
      background: linear-gradient(135deg, #2CACAD, #75E2E0);
      font-weight: 600;
      color: #0B1A30;
    }



    /* Mobile Responsiveness with Orientation Detection */
    @media (max-width: 768px) and (orientation: portrait) {

      /* Mobile Portrait: Show only 3 columns - Rank (#), Wins, Win Rate */
      body {
        padding: 20px 8px;
      }

      .leaderboard-container {
        max-width: 100%;
        padding: 16px 12px;
        border-radius: 16px;
      }

      .leaderboard-card {
        padding: 10px 8px;
        border-radius: 12px;
        overflow-x: auto;
      }

      .topbar-row {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
      }

      .leaderboard-title {
        font-size: 1.3rem;
      }

      table {
        font-size: 0.8rem;
        min-width: 300px;
      }

      th,
      td {
        padding: 6px 4px;
        white-space: nowrap;
      }

      /* Hide columns: #, Points, Games, Wins */
      /* Show only: Name, Rank, Win % */
      th:nth-child(1),
      th:nth-child(4),
      th:nth-child(5),
      th:nth-child(6),
      td:nth-child(1),
      td:nth-child(4),
      td:nth-child(5),
      td:nth-child(6) {
        display: none;
      }

      /* Make remaining columns more prominent */
      th:nth-child(2),
      th:nth-child(3),
      th:nth-child(7) {
        font-size: 0.85rem;
        padding: 8px 6px;
      }

      td:nth-child(2),
      td:nth-child(3),
      td:nth-child(7) {
        padding: 8px 6px;
        font-weight: 600;
      }

      /* Ensure Name column with image is visible and styled */
      td:nth-child(2) {
        text-align: left;
      }
    }

    @media (max-width: 768px) and (orientation: landscape) {

      /* Mobile Landscape: Show all columns, encourage landscape */
      body {
        padding: 20px 12px;
      }

      .leaderboard-container {
        max-width: 100%;
        padding: 18px 16px;
      }

      .leaderboard-card {
        padding: 12px 10px;
        overflow-x: auto;
      }

      table {
        font-size: 0.85rem;
        min-width: 600px;
      }

      th,
      td {
        padding: 6px 4px;
      }

      /* Show all columns in landscape */
      th,
      td {
        display: table-cell !important;
      }
    }

    /* Tablet and Desktop: Show all columns */
    @media (min-width: 769px) {

      /* Ensure all columns are visible on larger screens */
      th,
      td {
        display: table-cell !important;
      }

      table {
        min-width: auto;
      }
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

  <div class="leaderboard-container">

    <!-- Header row -->
    <div class="topbar-row">
      <a href="lobby.php" class="back-btn">
        <i class="fa fa-arrow-left"></i> Lobby
      </a>

      <div class="leaderboard-title">
        <i class="fa fa-trophy" style="color:#D38062;"></i>
        Leaderboard
      </div>
    </div>

    <!-- Table card -->
    <div class="leaderboard-card">
      <table id="leaderboardTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Rank</th>
            <th>Points</th>
            <th>Games</th>
            <th>Wins</th>
            <th>Win %</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

  </div>

  <script>
    async function getLeaderboard() {
      const res = await fetch('api/leaderboard.php');
      const data = await res.json();
      if (!data.ok) return;

      const tbody = document.querySelector('#leaderboardTable tbody');
      tbody.innerHTML = '';

      data.leaders.forEach((p, i) => {

        // Rank icon colors
        let icon = '<i class="fa fa-star" style="color:#D38062"></i>';
        if (p.points >= 20) icon = '<i class="fa fa-trophy" style="color:#D9F5F0"></i>';
        else if (p.points >= 10) icon = '<i class="fa fa-crown" style="color:#2CACAD"></i>';

        const winPct = p.games > 0 ? Math.round((p.wins / p.games) * 100) : 0;

        tbody.innerHTML += `
        <tr>
          <td>${i + 1}</td>
          <td><img src="${p.profile_img}" alt="Profile" style="width:50px; height:50px; border-radius:50%; vertical-align:middle; margin-right:10px;">${p.name}</td>
          <td>${icon}</td>
          <td>${p.points}</td>
          <td>${p.games}</td>
          <td>${p.wins}</td>
          <td>${winPct}%</td>
        </tr>
      `;
      });
    }

    getLeaderboard();
  </script>
  <script src="music.js"></script>
</body>

</html>