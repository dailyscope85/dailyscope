<?php
require 'includes/db.php';
date_default_timezone_set('Asia/Kolkata');
$selected_date = $_GET['date'] ?? date('Y-m-d');

$date_start = $selected_date . ' 00:00:00';
$date_end = $selected_date . ' 23:59:59';
$stmt = $conn->prepare("SELECT * FROM news WHERE publishedAt BETWEEN ? AND ? ORDER BY publishedAt DESC");
$stmt->bind_param("ss", $date_start, $date_end);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DailyScope News</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.main-container {
    width: 100%;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 20px; /* single color */
    box-sizing: border-box;
}

.calendar-container {
    width: 100%;
    margin-bottom: 20px;
}

.news-grid-container {
    flex: 1;
    overflow-y: auto; /* scrollable */
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.news-card {
    background: #e0f7fa; /* light cyan */
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 300px;
}

.news-card img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.news-card .content {
    padding: 10px;
    overflow: hidden;
    flex: 1;
}

.news-card h3 {
    font-size: 1rem;
    margin: 0 0 8px;
    color: #00796b; /* dark teal for title */
}

.news-card p {
    font-size: 0.85rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    color: #004d40; /* slightly darker text */
}

.news-card small {
    font-size: 0.7rem;
    color: #555;
}


</style>
</head>
<body>
<div class="main-container">
    
    <!-- Scrollable News Grid -->
    <div class="news-grid-container">
        <div class="news-grid">
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="news-card">
                        <?php if($row['image']): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="News Image">
                        <?php endif; ?>
                        <div class="content">
                            <h3><a href="<?= htmlspecialchars($row['url']) ?>" target="_blank"><?= htmlspecialchars($row['title']) ?></a></h3>
                            <?php if($row['description']): ?>
                                <p><?= htmlspecialchars($row['description']) ?></p>
                            <?php endif; ?>
                            <small>Published at: <?= $row['publishedAt'] ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No news found for <?= htmlspecialchars($selected_date) ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function changeDate(date) {
    window.location.href = "?date=" + date;
}
</script>
</body>
</html>

