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

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM players WHERE name = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Username already exists
        header("Location: register.php?error=username_taken");
        exit();
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO players (name, password, profile_img) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $hashed_password, 'assets/whale.jpg'])) {
            // Redirect to login page
            header("Location: index.php?registration=success");
            exit();
        } else {
            echo "Error: Could not register. Please try again later.";
        }
    }
}
?>
