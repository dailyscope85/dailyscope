<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

// Ensure user is logged in
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
if (!$username || !$email) {
    header("Location: login.php");
    exit();
}

// Handle account updates
$msg = '';
if (isset($_POST['update_username'])) {
    $new_username = $_POST['username'];
    $stmt = $conn->prepare("UPDATE users SET username=? WHERE email=?");
    $stmt->bind_param("ss", $new_username, $email);
    $stmt->execute();
    $_SESSION['username'] = $new_username;
    $username = $new_username;
    $msg = "Username updated successfully!";
}

if (isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (password_verify($current, $res['password'])) {
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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - DailyScope</title>
<link rel="stylesheet" href="assets/css/dashboard.css?v=1.0">



</head>
<body>
<div id="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>DailyScope</h2>
            <button id="toggle-btn">&#9776;</button>
        </div>

        <ul class="sidebar-menu">
            <li><button class="sidebar-btn" data-target="news-panel">📰 <span class="text">News</span></button></li>
            <li><button class="sidebar-btn" data-target="notes-panel">📝 <span class="text">Notes</span></button></li>
            <li><button class="sidebar-btn" data-target="quizzes-panel">❓ <span class="text">Quizzes</span></button></li>
            <li><button class="sidebar-btn" data-target="progress-panel">📈 <span class="text">Progress</span></button></li>
        </ul>

        <div class="sidebar-footer">
            <button class="sidebar-btn" data-target="account-panel">⚙️ <span class="text">Account</span></button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>Hi, <?php echo htmlspecialchars($username); ?></header>

        <!-- News Panel -->
        <div class="panel active" id="news-panel">
            <div class="news-container">
                
                <input type="date" id="news-date" value="<?php echo date('Y-m-d'); ?>" />
                <div id="news-list">
                    <?php
                    // Load today's news by default
                    $_GET['date'] = date('Y-m-d');
                    include 'news.php';
                    ?>
                </div>
            </div>
        </div>

        <!-- Notes Panel -->
<div class="panel" id="notes-panel">
<div id="news-list">
                    <?php
                    
                    include 'notes.php';
                    ?>
                </div>
    
</div>


        <!-- Quizzes Panel -->
        <div class="panel" id="quizzes-panel">
            <h2>Quizzes</h2>
            <p>Quizzes content goes here...</p>
        </div>

        <!-- Progress Panel -->
        <div class="panel" id="progress-panel">
            <h2>Progress</h2>
            <p>Your progress content goes here...</p>
        </div>

        <!-- Account Panel -->
        <div class="panel account-container" id="account-panel">
            <h2>Account Settings</h2>
            <?php if($msg) echo "<p class='message'>{$msg}</p>"; ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <button type="submit" name="update_username">Update Username</button>
            </form>

            <form method="POST">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
                <label>New Password</label>
                <input type="password" name="new_password" required>
                <button type="submit" name="update_password">Update Password</button>
            </form>

            <form method="POST" action="logout.php" style="text-align:center; margin-top:20px;">
                <button type="submit">Logout</button>
            </form>
        </div>

    </div>
</div>

<script>


// Panel toggle
const buttons = document.querySelectorAll('.sidebar-btn');
const panels = document.querySelectorAll('.panel');
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-target');
        panels.forEach(panel => panel.classList.remove('active'));
        document.getElementById(target).classList.add('active');
        document.getElementById(target).scrollIntoView({behavior:'smooth', block:'start'});
    });
});

// News date picker fetch
const newsList = document.getElementById('news-list');
const newsDate = document.getElementById('news-date');

newsDate.addEventListener('change', function() {
    const selectedDate = this.value;
    fetch(`news.php?date=${selectedDate}`)
        .then(res => res.text())
        .then(html => newsList.innerHTML = html);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-btn');
    const wrapper = document.getElementById('wrapper');

    toggleBtn.addEventListener('click', () => {
        wrapper.classList.toggle('collapsed');
    });

    const buttons = document.querySelectorAll('.sidebar-btn');
    const panels = document.querySelectorAll('.panel');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            panels.forEach(panel => panel.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
    });
});
</script>

</body>
</html> 
