<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = '127.0.0.1:3309';
$DB_NAME = 'morris_db';
$DB_USER = 'root';
$DB_PASS = '';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Find user by username
    $stmt = $pdo->prepare("SELECT * FROM players WHERE name = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start session
        $_SESSION['player_id'] = $user['id'];
        $_SESSION['player_name'] = $user['name'];
        
        // Redirect to a new dashboard page
        header("Location:lobby.php");
        exit();
    } else {
        // Invalid credentials
        header("Location: index.php?error=invalid_credentials");
        exit();
    }
}
?>
