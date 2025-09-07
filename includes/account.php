<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Handle form submissions
if(isset($_POST['update_username'])){
    $new_username = $_POST['username'];
    $stmt = $conn->prepare("UPDATE users SET username=? WHERE email=?");
    $stmt->bind_param("ss", $new_username, $email);
    $stmt->execute();
    $_SESSION['username'] = $new_username;
    $username = $new_username;
}

if(isset($_POST['update_password'])){
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if(password_verify($current, $res['password'])){
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt2->bind_param("ss",$hashed,$email);
        $stmt2->execute();
        $msg = "Password updated successfully!";
    } else {
        $msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account - DailyScope</title>
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.account-container {
    max-width: 600px;
    margin: auto;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
}

.account-container h2 {
    text-align: center;
    margin-bottom: 30px;
}

.account-container label {
    display: block;
    margin: 10px 0 5px;
}

.account-container input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: none;
    margin-bottom: 15px;
}

.account-container button {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: rgba(255,255,255,0.2);
    color: #fff;
    transition: 0.3s;
}

.account-container button:hover {
    background: rgba(255,255,255,0.4);
}

.message {
    text-align: center;
    margin-bottom: 15px;
    color: #ffeb3b;
}
</style>
</head>
<body>

<div id="wrapper">
    <?php include 'dashboard_sidebar.php'; // your sidebar ?>
    <div class="main-content">
        <header>
            <div class="header-right">
                <p>Hi, <?php echo $username; ?></p>
            </div>
        </header>

        <div class="content">
            <div class="account-container">
                <h2>Account Settings</h2>
                <?php if(isset($msg)) echo "<p class='message'>{$msg}</p>"; ?>
                
                <!-- Update Username -->
                <form method="POST">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo $username; ?>" required>
                    <button type="submit" name="update_username">Update Username</button>
                </form>

                <!-- Update Password -->
                <form method="POST">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                    <button type="submit" name="update_password">Update Password</button>
                </form>

                <!-- Logout -->
                <form method="POST" action="logout.php" style="text-align:center; margin-top:20px;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/sidebar.js"></script>
</body>
</html>

