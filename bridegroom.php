<?php
session_start();
require 'config.php'; // Include database connection

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Initialize variables for error and success messages
$updateMessage = "";
$updateError = "";

// Fetch current wedding information from the database
$stmt = $pdo->prepare('SELECT * FROM wedding_info');
if (!$stmt->execute()) {
    die("Error executing the query: " . implode(" ", $stmt->errorInfo()));
}

$wedding_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($wedding_info === false) {
    die("No wedding information found.");
}

// Handle form submission to update the wedding info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $story = $_POST['story'];
    $details = $_POST['details'];

    // Begin transaction to update the wedding_info table
    $pdo->beginTransaction();
    try {
        // Update wedding information in the database
        $stmtUpdate = $pdo->prepare('UPDATE wedding_info SET title = :title, date = :date, story = :story, details = :details WHERE id = :id');
        $stmtUpdate->execute([
            'title' => $title,
            'date' => $date,
            'story' => $story,
            'details' => $details,
            'id' => $wedding_info['id']
        ]);

        // Commit transaction
        $pdo->commit();

        $updateMessage = "Wedding information updated successfully!";
    } catch (PDOException $e) {
        // Roll back the transaction if there was an error
        $pdo->rollBack();
        $updateError = "Error updating wedding information: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wedding Information</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
        }
        form input, form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form input[type="submit"] {
            background-color: #d4a373;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        form input[type="submit"]:hover {
            background-color: #c89666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Wedding Information</h1>

        <?php if ($updateMessage): ?>
            <p style="color: green;"><?= htmlspecialchars($updateMessage); ?></p>
        <?php elseif ($updateError): ?>
            <p style="color: red;"><?= htmlspecialchars($updateError); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="title">Wedding Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($wedding_info['title']); ?>" required>

            <label for="date">Wedding Date</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($wedding_info['date']); ?>" required>

            <label for="story">Our Story</label>
            <textarea id="story" name="story" rows="5" required><?= htmlspecialchars($wedding_info['story']); ?></textarea>

            <label for="details">Wedding Details</label>
            <textarea id="details" name="details" rows="5" required><?= htmlspecialchars($wedding_info['details']); ?></textarea>

            <input type="submit" value="Update Wedding Info">
        </form>
    </div>
</body>
</html>
