<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
if (!isset($_SESSION['player_id'])) {
  header('Location: index.php');
  exit;
}
require_once __DIR__ . '/api/db.php';
$player_id = $_SESSION['player_id'];
$stmt = $pdo->prepare('SELECT name, profile_img FROM players WHERE id = ?');
$stmt->execute([$player_id]);
$user = $stmt->fetch();
$profile_img = ($user && !empty($user['profile_img'])) ? $user['profile_img'] : 'assets/whale.jpg';
$profile_name = ($user && !empty($user['name'])) ? $user['name'] : (isset($_SESSION['player_name']) ? $_SESSION['player_name'] : 'Player');
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Morris Lobby</title>

  <!-- Your main style.css (board/theme variables come from here) -->
  <link rel="stylesheet" href="assets/style.css">


  <style>
    /* ==============================
   BACKGROUND
================================= */
    body {
      margin: 0;
      font-family: system-ui, "Segoe UI", Arial;
      color: var(--text);
      min-height: 100vh;

      /* center main content, keep footer at bottom */
      display: flex;
      flex-direction: column;
    }

    /* Wrap header + lobby box */
    .page-wrap {
      flex: 1 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
    }


    /* Footer stays at end of flex column */
    footer.site-footer {
      flex-shrink: 0;
      /* prevent shrinking */
      margin-top: 10px;
      padding: 20px 10px;
      background: linear-gradient(90deg, #ECC3C1, #F4DAD9);
      border-top: 3px solid var(--accent-border);
      text-align: center;
      color: var(--accent-border);
    }

    /* ==============================
   HEADER (MORRIS)
================================= */
    /* ==============================
   HEADER (MORRIS – cute + elegant)
================================= */
    .header {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .header-inner {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 22px;
      border-radius: 999px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.96), rgba(0, 56, 90, 0.94));
      border: 1px solid rgba(148, 162, 191, 0.9);
      box-shadow:
        0 20px 42px rgba(0, 0, 0, 0.9),
        0 0 28px rgba(106, 144, 180, 0.7);
    }

    /* cute glowing icon */
    .header-bubble {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background:
        radial-gradient(circle at 30% 30%, #D9F5F0, #75E2E0 55%, #1C4EA7 100%);
      box-shadow:
        0 0 14px rgba(117, 226, 224, 0.9),
        0 0 26px rgba(117, 226, 224, 0.7);
      position: relative;
    }

    .header-bubble::after {
      content: "";
      position: absolute;
      right: 4px;
      top: 5px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.9);
      opacity: 0.9;
    }

    /* title stack: big cute name + tiny subtitle */
    .header-text {
      display: flex;
      flex-direction: column;
    }

    .header-title {
      margin: 0;
      font-family: 'Charm';
      font-size: 1.6rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #D9F5F0;
      text-shadow: 0 0 14px rgba(117, 226, 224, 0.9);
    }

    .header-subtitle {
      margin: 0;
      margin-top: 2px;
      font-size: 0.7rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: #94A2BF;
    }

    /* ==============================
   MAIN CONTAINER
================================= */
    .lobby-box {
      width: 100%;
      max-width: 420px;
      margin: 40px auto 0 auto;
      padding: 24px 18px;
      border-radius: 24px;

      /* transparent / glass look so it “floats” */
      background: rgba(1, 22, 43, 0.35);
      border: 1px solid rgba(148, 162, 191, 0.6);
      box-shadow: 0 18px 40px rgba(1, 22, 43, 0.7);
      backdrop-filter: blur(10px);
    }


    /* TOPBAR */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar-name {
      font-family: 'Orbitron';
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--accent-border);
    }

    .btn-logout {
      background: var(--accent-dark);
      color: white;
      padding: 10px 16px;
      border: 2px solid var(--accent-border);
      border-radius: 10px;
      cursor: pointer;
      font-weight: 700;
    }

    /* MODE CARD */
    .card-mode {
      margin-top: 12px;
      background: transparent;
      padding: 0;
      border-radius: 0;
      border: none;
    }

    .card-title {
      font-family: 'Orbitron';
      font-weight: 900;
      font-size: 1.3rem;
      text-align: center;
      color: var(--accent);
      margin-bottom: 14px;
    }

    /* vertical column like the sample image */
    .lobby-row,
    .action-row {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }

    /* “big menu button” style */
    .btn-main,
    .btn-lead,
    .btn-customize {
      width: 100%;
      padding: 12px 16px;
      border-radius: 14px;
      border: 2px solid rgba(0, 0, 0, 0.4);
      background: linear-gradient(135deg, #f7d29c, #d58a55);
      color: #3b230f;
      font-family: 'Orbitron';
      font-weight: 900;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.55);
      cursor: pointer;
    }

    .btn-lead {
      background: linear-gradient(135deg, #ffb25b, #e16842);
    }

    .btn-customize {
      background: linear-gradient(135deg, #f0f0f0, #d0d0d0);
      color: #1d1d1d;
    }

    .input-code {
      width: 100%;
      text-align: center;
      margin-top: 4px;
    }

    /* profile + logout row inside card */
    .account-row {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .account-btn {
      padding: 10px 16px;
      border-radius: 14px;
      border: 2px solid rgba(0, 0, 0, 0.4);
      background: linear-gradient(135deg, #f0f0f0, #d0d0d0);
      color: #1d1d1d;
      font-family: 'Orbitron';
      font-weight: 800;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.45);
      text-decoration: none;
    }

    .account-btn.logout {
      background: linear-gradient(135deg, #e66a5a, #b3362d);
      color: #fff;
    }

    /* Big PLAY button */
    .play-btn-wrap {
      display: flex;
      justify-content: center;
      margin-bottom: 14px;
    }

    .play-btn {
      padding: 12px 40px;
      border-radius: 18px;
      border: 3px solid rgba(0, 0, 0, 0.55);
      background: linear-gradient(135deg, #ffdd7a, #f29b3a);
      color: #3b230f;
      font-family: 'Orbitron';
      font-weight: 900;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.55);
      cursor: pointer;
      text-decoration: none;
    }

    .play-btn:hover {
      filter: brightness(1.05);
      transform: translateY(-1px);
    }

    .play-btn:active {
      transform: translateY(1px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.55);
    }


    /* BUTTONS BELOW */
    .action-row {
      margin-top: 22px;
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-lead {
      width: 100%;
      max-width: 600px;
      padding: 16px;
      border-radius: 12px;
      border: 2px solid var(--accent-border);
      background: linear-gradient(90deg, var(--accent), var(--accent-dark));
      color: var(--text);
      font-family: 'Orbitron';
      font-weight: 900;
    }

    .btn-customize {
      padding: 14px 40px;
      border-radius: 18px;
      border: 3px solid rgba(0, 0, 0, 0.55);
      background: linear-gradient(135deg, #ffdd7a, #f29b3a);
      color: #3b230f;
      font-family: 'Orbitron';
      font-weight: 900;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.55);
      cursor: pointer;
      text-decoration: none;
      display: flex;
      justify-content: center;
      margin-bottom: 14px;
      width: 50%;
    }

    .btn-customize:hover {
      filter: brightness(1.05);
      transform: translateY(-1px);
    }

    .btn-customize:active {
      transform: translateY(1px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.55);
    }

    /* ==============================
   FOOTER
================================= */
    footer.site-footer {
      margin-top: 130px;
      padding: 20px 10px;
      background: linear-gradient(135deg, #00385a, #6a90b4);
      border-top: 3px solid var(--accent-border);
      text-align: center;
      color: var(--accent-border);
      box-shadow: 0 16px 40px rgba(1, 22, 43, 0.9);
      border-radius: 20px;
    }

    .footer-title {
      font-family: 'Orbitron';
      font-weight: 900;
    }

    .footer-names {
      color: var(--text);
      font-weight: 700;
    }

    /* ==============================
   CUSTOMIZE MODAL
================================= */
    /* Play modal (reuses base .modal styles) */
    .play-modal-box {
      width: 90%;
      max-width: 420px;
      background: rgba(1, 22, 43, 0.95);
      border-radius: 20px;
      padding: 22px 20px;
      border: 2px solid rgba(148, 162, 191, 0.7);
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.8);
      color: #f5f7fb;
    }

    .play-modal-title {
      font-family: 'Orbitron';
      font-size: 1.3rem;
      font-weight: 900;
      text-align: center;
      margin-bottom: 14px;
    }

    .play-modal-close {
      margin-top: 14px;
      width: 100%;
      padding: 10px 14px;
      border-radius: 12px;
      border: 2px solid rgba(0, 0, 0, 0.4);
      background: linear-gradient(135deg, #f0f0f0, #d0d0d0);
      font-weight: 700;
      cursor: pointer;
      color: #000000;
    }

    .custom-modal-box {
      width: 90%;
      max-width: 600px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.96), rgba(0, 56, 90, 0.94));
      border-radius: 22px;
      padding: 28px 28px 24px;
      border: 1px solid rgba(148, 162, 191, 0.8);
      box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.85),
        0 0 30px rgba(106, 144, 180, 0.7);
      position: relative;
      color: #d2dbeb;
    }

    .custom-modal-title {
      margin: 0 0 8px;
      text-align: center;
      font-family: 'Orbitron';
      letter-spacing: 0.12em;
      text-transform: uppercase;
      font-size: 1.2rem;
    }

    .custom-modal-subtitle {
      margin: 0 0 24px;
      text-align: center;
      font-size: 0.9rem;
      color: #94a2bf;
    }

    /* Single column layout for better spacing */
    .custom-modal-grid {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .custom-modal-grid .full-row {
      width: 100%;
    }

    .custom-modal-grid>div {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .custom-select,
    .custom-file {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #6a90b4;
      background: rgba(1, 22, 43, 0.9);
      color: #d2dbeb;
      outline: none;
      font-size: 1rem;
    }

    .custom-select:focus,
    .custom-file:focus {
      box-shadow: 0 0 0 2px rgba(106, 144, 180, 0.6);
      border-color: #75E2E0;
    }

    .option-label {
      display: block;
      font-weight: 700;
      color: #D9F5F0;
      font-size: 0.95rem;
      margin-bottom: 4px;
    }

    /* Image choice thumbnails */
    .image-choices {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 6px;
    }

    .image-choice {
      background: transparent;
      border: 2px solid transparent;
      padding: 6px;
      border-radius: 10px;
      cursor: pointer;
      transition: border-color 0.12s, transform 0.12s;
      width: 72px;
      height: 72px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .image-choice img {
      max-width: 100%;
      max-height: 100%;
      object-fit: cover;
      display: block;
    }

    .image-choice.selected {
      border-color: #75E2E0;
      transform: scale(1.04);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
    }

    /* Buttons at bottom */
    .modal-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 18px;
    }

    .modal-btn.custom-save,
    .modal-btn.custom-cancel {
      padding: 8px 16px;
      border-radius: 999px;
      border: none;
      font-weight: 700;
      cursor: pointer;
    }

    .modal-btn.custom-save {
      background: linear-gradient(135deg, #00385a, #6a90b4);
      color: #f5f7ff;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.7);
    }

    .modal-btn.custom-cancel {
      background: rgba(210, 219, 235, 0.1);
      color: #d2dbeb;
      border: 1px solid rgba(148, 162, 191, 0.5);
    }


    .modal {
      position: fixed;
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
      background: #00000080;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 200;
    }

    .modal.hidden {
      display: none;

    }

    .modal-box {
      width: 90%;
      max-width: 500px;
      background: white;
      border-radius: 18px;
      padding: 20px;
      border: 3px solid var(--accent);
      box-shadow: 0 6px 30px #0005;
    }

    .modal-title {
      font-family: 'Orbitron';
      font-size: 1.5rem;
      font-weight: 900;
      text-align: center;
      margin-bottom: 16px;
      color: var(--accent-border);
    }

    .option-label {
      font-weight: 700;
      margin: 8px 0 4px;
      color: var(--text);
    }

    select,
    input[type="file"] {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 2px solid var(--accent-border);
      margin-bottom: 12px;
    }

    .modal-buttons {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-top: 10px;
    }

    .modal-btn {
      padding: 10px 16px;
      font-weight: 700;
      border-radius: 10px;
      cursor: pointer;
      border: 2px solid var(--accent-border);
    }

    .save-btn {
      background: var(--accent);
    }

    .close-btn {
      background: #ddd;
    }



    /* Bottom nav buttons: Theme, About, How to Play, Logout */
    .bottom-nav {
      margin-top: 22px;
      display: flex;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    /* Elegant pill buttons */
    .bottom-btn {
      flex: 1 1 0;
      min-width: 100px;
      padding: 9px 14px;
      border-radius: 999px;
      border: 1px solid rgba(210, 219, 235, 0.7);
      background: linear-gradient(135deg, #00385a, #6a90b4);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.7);

      display: inline-flex;
      align-items: center;
      justify-content: center;

      color: #f5f7ff;
      font-family: 'Orbitron', sans-serif;
      font-size: 0.75rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-align: center;
      text-decoration: none;

      cursor: pointer;
      transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
    }

    .bottom-btn:hover {
      filter: brightness(1.06);
      transform: translateY(-1px);
      box-shadow: 0 14px 24px rgba(0, 0, 0, 0.8);
    }

    .bottom-btn:active {
      transform: translateY(1px);
      box-shadow: 0 7px 14px rgba(0, 0, 0, 0.7);
    }

    /* Variants */
    .bottom-btn.secondary {
      background: linear-gradient(135deg, rgba(1, 22, 43, 0.95), rgba(0, 56, 90, 0.9));
      border-color: rgba(148, 162, 191, 0.8);
    }

    .bottom-btn.danger {
      background: linear-gradient(135deg, #e66a5a, #b3362d);
      border-color: rgba(255, 190, 190, 0.85);
    }

    .how-modal-box {
      width: 90%;
      max-width: 480px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.97), rgba(0, 56, 90, 0.94));
      border-radius: 22px;
      padding: 22px 22px 20px;
      border: 1px solid rgba(148, 162, 191, 0.85);
      box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.9),
        0 0 30px rgba(106, 144, 180, 0.7);
      position: relative;
      color: #D9F5F0;
    }

    .how-close {
      position: absolute;
      top: 10px;
      right: 12px;
      width: 28px;
      height: 28px;
      border-radius: 999px;
      border: none;
      background: rgba(210, 219, 235, 0.16);
      color: #D9F5F0;
      font-size: 18px;
      line-height: 1;
      cursor: pointer;
    }

    .how-title {
      margin: 0 0 4px;
      text-align: center;
      font-family: 'Orbitron';
      letter-spacing: 0.12em;
      text-transform: uppercase;
      font-size: 24px;
    }

    .how-subtitle {
      margin: 0 0 12px;
      text-align: center;
      font-size: 21px;
      color: #94A2BF;
    }

    .how-list {
      margin: 8px 0 16px 20px;
      padding: 0;
      font-size: 20px;
      color: #E1F4FF;
    }

    .how-list li {
      margin-bottom: 6px;
    }

    .how-ok {
      width: 100%;
      padding: 9px 14px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, #00385a, #6a90b4);
      color: #F5F7FF;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.8);
    }

    .about-modal-box {
      width: 90%;
      max-width: 520px;
      background: linear-gradient(145deg, rgba(1, 22, 43, 0.97), rgba(0, 56, 90, 0.94));
      border-radius: 22px;
      padding: 22px 22px 20px;
      border: 1px solid rgba(148, 162, 191, 0.85);
      box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.9),
        0 0 30px rgba(106, 144, 180, 0.7);
      position: relative;
      color: #D9F5F0;
    }

    .about-close {
      position: absolute;
      top: 10px;
      right: 12px;
      width: 28px;
      height: 28px;
      border-radius: 999px;
      border: none;
      background: rgba(210, 219, 235, 0.16);
      color: #D9F5F0;
      font-size: 18px;
      line-height: 1;
      cursor: pointer;
    }

    .about-title {
      margin: 0 0 4px;
      text-align: center;
      font-family: 'Orbitron';
      letter-spacing: .08em;
      text-transform: uppercase;
      font-size: 24px;
    }

    .about-subtitle {
      margin: 4px 0 14px;
      text-align: center;
      font-size: 16px;
      color: #E1F4FF;
    }

    .about-section-title {
      margin: 0 0 6px;
      font-size: 18px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A2BF;
    }

    .about-text {
      margin: 0 0 12px;
      font-size: 16px;
      color: #D9F5F0;
    }

    .about-dev-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 16px;
    }

    .about-dev {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(0, 56, 90, 0.9);
      border-radius: 14px;
      padding: 8px 10px;
      border: 1px solid rgba(148, 162, 191, 0.7);
    }

    .about-dev img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #75E2E0;
      box-shadow: 0 0 10px rgba(117, 226, 224, 0.8);
    }

    .about-dev span {
      font-size: 0.9rem;
      font-weight: 600;
      color: #F5F7FF;
    }

    .about-ok {
      width: 100%;
      padding: 9px 14px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, #00385a, #6a90b4);
      color: #F5F7FF;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.8);
    }

    .lobby-music-wrap {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 10px;
    }

    .btn-music {
      padding: 6px 14px;
      border-radius: 999px;
      border: 1px solid rgba(56, 189, 248, 0.6);
      background: radial-gradient(circle at 20% 0%, #38bdf8 0%, #0b1120 50%, #020617 100%);
      color: #e5f2ff;
      font-size: 0.8rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      cursor: pointer;
      box-shadow:
        0 0 8px rgba(15, 23, 42, 0.9),
        0 0 16px rgba(37, 99, 235, 0.5);
      transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
    }

    .btn-music:hover {
      transform: translateY(-1px);
      box-shadow:
        0 0 10px rgba(56, 189, 248, 0.9),
        0 0 22px rgba(37, 99, 235, 0.7);
    }

    .btn-music:active {
      transform: translateY(0);
      box-shadow:
        0 0 6px rgba(15, 23, 42, 0.9),
        0 0 14px rgba(37, 99, 235, 0.4);
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

  <audio id="lobby-music" loop autoplay>
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



  <div class="page-wrap">
    <!-- HEADER -->
    <div class="header">
      <div class="header-inner">
        <div class="header-bubble"></div>
        <div class="header-text">
          <h1 class="header-title">MORRIS GAME</h1>
          <p class="header-subtitle">Silent Depth · JAJ VERSION</p>
        </div>
      </div>
    </div>



    <!-- MAIN CONTENT -->
    <div class="lobby-box">

      <div class="topbar">
        <div style="display:flex;align-items:center;gap:60px;">
          <img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile"
            style="width:64px;height:64px;border-radius:50%;border:2.5px solid var(--accent);box-shadow:0 0 8px var(--accent-glow);object-fit:cover; margin-left:15px;">
          <span class="topbar-name" style="font-size:1.2em; color:white;"> Hi, <?= htmlspecialchars($profile_name); ?>
            here!</span>
        </div>
      </div>

      <div class="card-mode">

        <div class="play-btn-wrap">
          <button type="button" class="play-btn" id="playBtn">Play</button>
        </div>

        <div class="account-row">
          <a href="profile.php" class="btn-customize" style="color:#1d1d1d;font-weight:800;">Profile</a>
        </div>

        <div class="account-row">
          <button id="openCustomize" class="btn-customize" style="width:70%; color:black;">CUSTOMISE</button>
        </div>

        <div class="action-row">
          <button onclick="location.href='leaderboard.php'" class="btn-customize" style="width:100%; color:black;">VIEW
            LEADERBOARD</button>
        </div>

      </div>
    </div>

    <!-- Bottom horizontal buttons -->
    <div class="bottom-nav">
      <button id="musicToggle" class="btn btn-music">
        Music: On
      </button>
      <button type="button" class="bottom-btn secondary" id="openAbout">About</button>
      <button type="button" class="bottom-btn secondary" id="openHowTo">How to Play</button>
      <a href="logout.php" class="bottom-btn danger">Logout</a>
    </div>

  </div>

  <!-- ==============================
     CUSTOMIZE MODAL
================================= -->
  <!-- ABOUT MODAL -->
  <div id="aboutModal" class="modal hidden">
    <div class="about-modal-box">
      <button type="button" class="about-close" id="closeAbout">&times;</button>

      <h2 class="about-title">About the Game</h2>
      <p class="about-subtitle">
        Welcome to the Morris Game, a web-based adaptation of the classic strategy board game,
        also known as JAJ version MORRIS GAME!
      </p>

      <h3 class="about-section-title">Meet the Developers</h3>
      <p class="about-text">
        We are the team behind the creation of this game. Thank you for playing and testing our application!
      </p>

      <div class="about-dev-list">
        <div class="about-dev">
          <img src="jen.jpg" alt="Jenzel Rica Barrita">
          <span>Jenzel Rica Barrita</span>
        </div>
        <div class="about-dev">
          <img src="anna.jpg" alt="De Leon Anna Marie">
          <span>De Leon Anna Marie</span>
        </div>
        <div class="about-dev">
          <img src="rey.jpg" alt="John Rey Camacho">
          <span>John Rey Camacho</span>
        </div>

        <div class="about-dev">
          <h4 style="text-align:center;">Professor/Adviser: Dr. Eddie I. Peru</h4>
        </div>

      </div>

      <button type="button" class="about-ok" id="closeAboutBtn">Close</button>
    </div>
  </div>

  <!-- HOW TO PLAY MODAL -->
  <div id="howModal" class="modal hidden">
    <div class="how-modal-box">
      <button type="button" class="how-close" id="closeHow"></button>

      <h2 class="how-title">How to Play</h2>
      <p class="how-subtitle">Nine Men's Morris · Quick guide</p>

      <ol class="how-list">
        <li>Place <strong>9 pieces</strong> each, taking turns on empty points.</li>
        <li>Form a line of <strong>3 in a row</strong> (a “mill”) to remove one opponent’s piece.</li>
        <li>After all pieces are placed, <strong>move along connected lines</strong> to adjacent spots.</li>
        <li>When you have only <strong>3 pieces</strong>, you may <strong>fly</strong> to any empty point.</li>
        <li>You win if your opponent has <strong>fewer than 3 pieces</strong> or <strong>no legal moves</strong>.
        </li>
      </ol>

      <button type="button" class="how-ok" id="closeHowBtn">Got it</button>
    </div>
  </div>

  <!-- PLAY MODAL -->
  <div id="playModal" class="modal hidden">
    <div class="play-modal-box">
      <div class="play-modal-title">Choose Mode</div>

      <div class="lobby-row">
        <input id="joinCodeModal" class="input-code" placeholder="Enter code" style="color:black;">
        <button id="joinRoomModal" class="btn-main" style="color:black;">Join Room</button>
        <button id="createRoomModal" class="btn-main" style="color:black;">Create Room</button>
      </div>
      <p id="roomStatusModal" style="text-align:center;color:var(--accent-border);font-weight:700;margin-top:8px;">
      </p>

      <button type="button" class="play-modal-close" id="closePlayModal">Close</button>
    </div>
  </div>


  <div id="customModal" class="modal hidden">
    <div class="custom-modal-box">

      <button type="button" class="custom-modal-close-icon" id="closeCustomizeIcon">×</button>

      <div class="custom-modal-title">Customize Your Stones</div>
      <p class="custom-modal-subtitle">Choose a stone image from the presets below for a unique look.</p>

      <!-- FORM FIELDS -->
      <div class="custom-modal-grid">

        <!-- STONE IMAGE CHOICES -->
        <div>
          <label class="option-label">Choose a Stone Image</label>
          <div class="image-choices">
            <button type="button" class="image-choice" data-src="ISU.png" title="Preset 1">
              <img src="ISU.png" alt="Preset 1">
            </button>
            <button type="button" class="image-choice" data-src="ccsict.png" title="Preset 2">
              <img src="ccsict.jpg" alt="Preset 2" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="ccje.png" title="Preset 3">
              <img src="ccje.png" alt="Preset 3">
            </button>
            <button type="button" class="image-choice" data-src="tech.png" title="Preset 4">
              <img src="tech.png" alt="Preset 4">
            </button>
            <button type="button" class="image-choice" data-src="stone1.png" title="Preset 5">
              <img src="stone1.png" alt="Preset 5">
            </button>
            <button type="button" class="image-choice" data-src="stone2.png" title="Preset 6">
              <img src="stone2.png" alt="Preset 6">
            </button>
            <button type="button" class="image-choice" data-src="stone3.png" title="Preset 7">
              <img src="stone3.png" alt="Preset 7">
            </button>
            <button type="button" class="image-choice" data-src="stone4.png" title="Preset 8">
              <img src="stone4.png" alt="Preset 8">
            </button>
            <button type="button" class="image-choice" data-src="stone5.png" title="Preset 9">
              <img src="stone5.png" alt="Preset 9">
            </button>
            <button type="button" class="image-choice" data-src="sea.jpg" title="Preset 9">
              <img src="sea.jpg" alt="Preset 9" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="coral.jpg" title="Preset 9">
              <img src="coral.jpg" alt="Preset 9" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="coral2.jpg" title="Preset 9">
              <img src="coral2.jpg" alt="Preset 9" style="border-radius:100px;">
            </button>
            <button type="button" class="image-choice" data-src="coral3.jpg" title="Preset 9">
              <img src="coral3.jpg" alt="Preset 9" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="stone8.jpg" title="Preset 10">
              <img src="stone8.jpg" alt="Preset 10" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="blue1.png" title="Preset 10">
              <img src="blue1.png" alt="Preset 10">
            </button>
            <button type="button" class="image-choice" data-src="stone9.jpg" title="Preset 10">
              <img src="stone9.jpg" alt="Preset 10" style="border-radius:50px;">
            </button>
            <button type="button" class="image-choice" data-src="stone7.jpg" title="Preset 10">
              <img src="stone7.jpg" alt="Preset 10" style="border-radius:100px;">
            </button>
            <button type="button" class="image-choice" data-src="black.png" title="Preset 10">
              <img src="black.png" alt="Preset 10">
            </button>
            <button type="button" class="image-choice" data-src="white.png" title="Preset 10">
              <img src="white.png" alt="Preset 10">
            </button>
          </div>
        </div>


      </div>

      <!-- BUTTONS -->
      <div class="modal-buttons">
        <button id="saveCustomize" class="modal-btn custom-save">Save</button>
        <button id="closeCustomize" class="modal-btn custom-cancel">Close</button>
      </div>

    </div>
  </div>
  </div>

  <!-- ==============================
     JAVASCRIPT
================================= -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // ABOUT MODAL LOGIC
      const aboutModal = document.getElementById('aboutModal');

      document.getElementById('openAbout').onclick = () => {
        aboutModal.classList.remove('hidden');
      };

      document.getElementById('closeAbout').onclick = () => {
        aboutModal.classList.add('hidden');
      };

      document.getElementById('closeAboutBtn').onclick = () => {
        aboutModal.classList.add('hidden');
      };

      // HOW TO PLAY MODAL LOGIC
      const howModal = document.getElementById('howModal');

      document.getElementById('openHowTo').onclick = () => {
        howModal.classList.remove('hidden');
      };

      document.getElementById('closeHow').onclick = () => {
        howModal.classList.add('hidden');
      };

      document.getElementById('closeHowBtn').onclick = () => {
        howModal.classList.add('hidden');
      };

      // Bottom "Theme" button reuses same customize modal (if you have it)
      const openCustomizeBottom = document.getElementById("openCustomizeBottom");
      if (openCustomizeBottom) {
        openCustomizeBottom.onclick = () => {
          document.getElementById("customModal").classList.remove("hidden");
        };
      }

      document.getElementById("closeCustomizeIcon").onclick = () => {
        document.getElementById("customModal").classList.add("hidden");
      };

      document.getElementById("openCustomize").onclick = () => {
        document.getElementById("customModal").classList.remove("hidden");
      };

      document.getElementById("closeCustomize").onclick = () => {
        document.getElementById("customModal").classList.add("hidden");
      };

      // PRESET CHOICES
      let selectedPreset = null;

      // preset image choices
      document.querySelectorAll('.image-choice').forEach(btn => {
        btn.addEventListener('click', () => {
          const src = btn.dataset.src;
          selectedPreset = src;
          // highlight selected preset
          document.querySelectorAll('.image-choice').forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
        });
      });

      document.getElementById("saveCustomize").onclick = async () => {
        let stoneImg = null;

        if (selectedPreset) {
          stoneImg = selectedPreset;
          await saveStoneImg(stoneImg);
        } else {
          // No image chosen; clear custom image
          await saveStoneImg(null);
        }
      };

      async function saveStoneImg(stoneImg) {
        try {
          const response = await fetch('api/update_stone.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ stone_img: stoneImg })
          });
          const result = await response.json();
          if (result.ok) {
            alert("Customization saved! Theme will apply in the game.");
            document.getElementById("customModal").classList.add("hidden");
          } else {
            alert("Failed to save customization: " + (result.error || "Unknown error"));
          }
        } catch (error) {
          alert("Failed to save customization: " + error.message);
        }
      }
    });
  </script>


  <!-- ROOM logic -->


  <script>


    // PLAY MODAL LOGIC
    const playModal = document.getElementById('playModal');
    document.getElementById('playBtn').onclick = () => {
      playModal.classList.remove('hidden');
    };

    document.getElementById('closePlayModal').onclick = () => {
      playModal.classList.add('hidden');
    };

    // Use modal buttons for create/join
    document.getElementById('createRoomModal').onclick = async () => {
      const r = await post('api/create_room.php', {});
      if (r.ok) location.href = 'game.php?code=' + r.code;
      else document.getElementById('roomStatusModal').textContent = r.error;
    };

    document.getElementById('joinRoomModal').onclick = async () => {
      const code = document.getElementById('joinCodeModal').value.trim();
      if (!code) return alert("Please enter a room code.");
      const r = await post('api/join_room.php', { code });
      if (r.ok) location.href = 'game.php?code=' + code.toUpperCase();
      else document.getElementById('roomStatusModal').textContent = r.error;
    };

    async function post(url, data) {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });
      return res.json();
    }

    // Create Room
    document.getElementById('createRoom').onclick = async () => {
      const r = await post('api/create_room.php', {});
      if (r.ok) location.href = 'game.php?code=' + r.code;
      else document.getElementById('roomStatus').textContent = r.error;
    };

    // Join Room
    document.getElementById('joinRoom').onclick = async () => {
      const code = document.getElementById('joinCode').value.trim();
      if (!code) return alert("Please enter a room code.");
      const r = await post('api/join_room.php', { code });
      if (r.ok) location.href = 'game.php?code=' + code.toUpperCase();
      else document.getElementById('roomStatus').textContent = r.error;
    };
  </script>

  <script>
    (function () {
      const audio = document.getElementById('lobby-music');
      const btn = document.getElementById('musicToggle');
      if (!audio || !btn) return;

      // load saved preference
      let musicEnabled = localStorage.getItem('lobbyMusic') !== 'off';
      btn.textContent = musicEnabled ? 'Music: On' : 'Music: Off';

      function updatePlayback() {
        if (musicEnabled) {
          audio.volume = 0.35; // softer background level
          audio.play().catch(() => {
            // browser blocked autoplay; wait for user click
          });
        } else {
          audio.pause();
          audio.currentTime = 0;
        }
      }

      // initial attempt to play (may be blocked until first click)
      updatePlayback();

      // toggle on button click
      btn.addEventListener('click', () => {
        musicEnabled = !musicEnabled;
        localStorage.setItem('lobbyMusic', musicEnabled ? 'on' : 'off');
        btn.textContent = musicEnabled ? 'Music: On' : 'Music: Off';
        updatePlayback();
      });

      // as a fallback for autoplay blocking, start music on first user click anywhere
      document.addEventListener('click', function startOnFirstClick() {
        if (musicEnabled && audio.paused) {
          audio.play().catch(() => { });
        }
        document.removeEventListener('click', startOnFirstClick);
      });
    })();
  </script>

  <script src="music.js"></script>

</body>

</html>