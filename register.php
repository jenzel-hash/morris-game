<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Morris Game</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="auth-bg"></div>

  <!-- Keep your existing bubbles if you like them -->
  <div class="auth-bubbles">
    <div class="auth-bubble sm" style="--x-start: 5vw;  --x-end: 0vw;   animation-delay: 16s;"></div>
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
		<p class="auth-subtitle">JAJ version • Register now!</p>

        <?php
        if (isset($_GET['error']) && $_GET['error'] === 'username_taken') {
            echo '<div style="color: #b00020; font-weight: 700; margin-bottom: 16px;">Username already taken. Please choose a different username.</div>';
        }
        ?>

        <form action="register_process.php" method="post" autocomplete="off">
		<div class="auth-group">
            <label for="username">Username</label>
            <input class="auth-input" type="text" name="username" id="username" required autofocus>
		</div>	
		
		<div class="auth-group">
            <label for="password">Password</label>
            <input class="auth-input" type="password" name="password" id="password" required>
		</div>

            <button type="submit" class="auth-btn">Register</button>
        </form>

        <div class="auth-switch">
            Already have an account?
            <a href="index.php">Login here</a>
        </div>
    </div>
 </div>
</body>
</html>
