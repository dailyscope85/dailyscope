<?php
session_start();
require 'includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $user = mysqli_fetch_assoc($check);
            if (password_verify($password, $user['password'])) {
                // ✅ Store both username and email in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - DailyScope</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Centered login form */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #4a90e2, #50e3c2);
}
.login-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 40px;
    border-radius: 20px;
    width: 350px;
    text-align: center;
    box-shadow: 0 10px 35px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(30px);
    animation: cardFade 0.8s forwards;
}
@keyframes cardFade {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.login-card h2 {
    margin-bottom: 25px;
    font-size: 28px;
    color: #fff;
    text-shadow: 2px 2px 15px rgba(0,0,0,0.2);
}
.login-card input {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border-radius: 50px;
    border: none;
    outline: none;
    font-size: 16px;
    transition: all 0.3s ease;
}
.login-card input:focus {
    box-shadow: 0 0 10px rgba(255,255,255,0.5);
    transform: scale(1.02);
}
.login-card button {
    margin-top: 15px;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 50px;
    background: #fff;
    color: #4a90e2;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.login-card button:hover {
    background: #4a90e2;
    color: #fff;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 15px rgba(255,255,255,0.2);
}
.message {
    color: #ffcccb;
    margin-bottom: 15px;
    font-weight: 500;
}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h2>Login</h2>
        <?php if($message != "") { echo "<div class='message'>$message</div>"; } ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p style="margin-top:15px; color:#fff;">Don't have an account? <a href="register.php" style="color:#fff; text-decoration: underline;">Sign Up</a></p>
    </div>
</div>
</body>
</html>

