<?php

require 'includes/db.php';
require 'includes/auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new note
if (isset($_POST['add_note'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $note_date = $_POST['note_date'] ?: date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, note_date) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $user_id, $title, $content, $note_date);
    $stmt->execute();
}

// Handle update (auto-save)
if (isset($_POST['update_note'])) {
    $id = (int)$_POST['id'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE notes SET content=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sii", $content, $id, $user_id);
    $stmt->execute();
    exit("saved"); // AJAX response
}

// Handle delete
if (isset($_POST['delete_note'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM notes WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
}

// Handle download
if (isset($_GET['download'])) {
    $id = (int)$_GET['download'];
    $stmt = $conn->prepare("SELECT title, content FROM notes WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $note = $stmt->get_result()->fetch_assoc();

    if ($note) {
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=\"".preg_replace('/[^a-zA-Z0-9_-]/','_', $note['title']).".txt\"");
        echo $note['content'];
    }
    exit();
}

// Load notes for selected date
$selected_date = $_GET['date'] ?? date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM notes WHERE user_id=? AND note_date=? ORDER BY updated_at DESC");
$stmt->bind_param("is", $user_id, $selected_date);
$stmt->execute();
$notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="notes-container">
    <h2>📒 My Notes</h2>

    <!-- Add new note -->
    <form method="POST" class="add-note-form">
        <input type="text" name="title" placeholder="Note Title" required>
        <textarea name="content" placeholder="Write your note..."></textarea>
        <input type="date" name="note_date" value="<?php echo htmlspecialchars($selected_date); ?>">
        <button type="submit" name="add_note">➕ Add Note</button>
    </form>

    <!-- Date filter -->
    <div class="filter">
        <label for="note-date">📅 Select Date:</label>
        <input type="date" id="note-date" value="<?php echo htmlspecialchars($selected_date); ?>">
    </div>

    <!-- Notes list -->
    <div class="notes-list">
        <?php if ($notes): ?>
            <?php foreach ($notes as $note): ?>
                <div class="note-card" data-id="<?php echo $note['id']; ?>">
                    <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                    <textarea class="note-content"><?php echo htmlspecialchars($note['content']); ?></textarea>
                    <div class="note-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                            <button type="submit" name="delete_note">🗑️ Delete</button>
                        </form>
                        <a href="notes.php?download=<?php echo $note['id']; ?>" class="download-btn">⬇️ Download</a>
                    </div>
                    <small>Last updated: <?php echo $note['updated_at']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notes for this date.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-save on blur
document.querySelectorAll(".note-content").forEach(area => {
    area.addEventListener("blur", () => {
        const card = area.closest(".note-card");
        const id = card.dataset.id;
        const content = area.value;

        fetch("notes.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({update_note: 1, id, content})
        })
        .then(res => res.text())
        .then(msg => console.log("Note", id, msg));
    });
});

// Date filter reload
document.getElementById("note-date").addEventListener("change", function() {
    const date = this.value;
    fetch("notes.php?date=" + date)
        .then(res => res.text())
        .then(html => {
            document.querySelector("#notes-panel").innerHTML = html;
        });
});
</script>

