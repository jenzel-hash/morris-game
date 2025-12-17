<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Morris Game</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="auth-bg"></div>
	
	<div class="auth-bubbles">
    <div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 0s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 4s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
	<div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 1s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
	<div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 0s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 4s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
	<div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 1s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
	<div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 0s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 4s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
	<div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 1s;"></div>
    <div class="auth-bubble"    style="--x-start: 20vw; --x-end: 15vw;  animation-delay: 8s;"></div>
    <div class="auth-bubble lg" style="--x-start: 40vw; --x-end: 35vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble sm" style="--x-start: 60vw; --x-end: 55vw;  animation-delay: 12s;"></div>
    <div class="auth-bubble"    style="--x-start: 80vw; --x-end: 70vw;  animation-delay: 16s;"></div>
    <div class="auth-bubble lg" style="--x-start: 30vw; --x-end: 25vw;  animation-delay: 20s;"></div>
  </div>


<div class="auth-foreground">
    <div class="auth-card"> 	

        <!-- ⭐ TWO CIRCLE LOGOS ⭐ -->
        <div class="auth-header">
					  <div class="auth-header-logos">
						<img src="ISU.png" alt="Logo 1" class="auth-logo">
						<img src="ccsict.jpg" alt="Logo 2" class="auth-logo second">
						</div>
					</div>
					  <h1 class="auth-title">Morris Game</h1>
					  <p class="auth-subtitle">JAJ version • Log in to continue</p>
        <?php
        if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials') {
            echo '<div style="color: #b00020; font-weight: 700; margin-bottom: 16px;">Invalid username or password.</div>';
        } elseif (isset($_GET['registration']) && $_GET['registration'] === 'success') {
            echo '<div style="color: #2e7d32; font-weight: 700; margin-bottom: 16px;">Registration successful. Please log in.</div>';
        }
        ?>

        <form action="login_process.php" method="post" autocomplete="off">
		<div class="auth-group">
            <label class="auth-label" for="username">Username</label>
            <input class="auth-input" type="text" name="username" id="username" required autofocus>
		</div>

		<div class="auth-group">
            <label class="auth-label" for="password">Password</label>
            <input class="auth-input" type="password" name="password" id="password" required>
		</div>

            <button type="submit" class="auth-btn">Login</button>
        </form>

        <div class="auth-switch">
            Don't have an account?
            <a href="register.php">Register here</a>
        </div>
    </div>
</div>
</body>
</html>
