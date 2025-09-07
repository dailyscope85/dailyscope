<?php
session_start();
require 'includes/db.php';
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$msg = '';

if(isset($_POST['update_username'])){
    $new_username = $_POST['username'];
    $stmt = $conn->prepare("UPDATE users SET username=? WHERE email=?");
    $stmt->bind_param("ss", $new_username, $email);
    $stmt->execute();
    $_SESSION['username'] = $new_username;
    $username = $new_username;
    $msg = "Username updated successfully!";
}

if(isset($_POST['update_password'])){
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if(password_verify($current, $res['password'])){
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt2->bind_param("ss", $hashed, $email);
        $stmt2->execute();
        $msg = "Password updated successfully!";
    } else {
        $msg = "Current password is incorrect!";
    }
}
?>

<div class="panel account-panel">
    <h2>Account Settings</h2>
    <?php if($msg) echo "<p class='message'>{$msg}</p>"; ?>

    <div class="form-section">
        <h3>Update Username</h3>
        <form method="POST" class="account-container">
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            <button type="submit" name="update_username">Update Username</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Update Password</h3>
        <form method="POST" class="account-container">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>
    </div>

    <div class="form-section" style="text-align:center; margin-top:20px;">
        <form method="POST" action="logout.php">
            <button type="submit">Logout</button>
        </form>
    </div>
</div>

