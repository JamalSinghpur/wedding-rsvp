<?php
session_start();
require 'config.php'; // Include database connection

$logged_in = isset($_SESSION['user_id']);

// Initialize variables to hold feedback messages
$rsvpMessage = "";
$rsvpError = "";

// Fetch wedding information from the database
$stmt = $pdo->prepare('SELECT * FROM wedding_info');
if (!$stmt->execute()) {
    die("Error executing the query: " . implode(" ", $stmt->errorInfo()));
}

$wedding_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($wedding_info === false) {
    echo "No wedding information found.";
    exit; // Stop script execution if no data is found
}

// Check if token is provided via GET parameter
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare statement to check if the token exists in the users table
    $stmt = $pdo->prepare('SELECT * FROM users WHERE rsvp_token = :token');
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    // Check if the RSVP token is valid
    if ($user) {
        // Handle the RSVP form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email']; // Get the email from the form
            $attendance = $_POST['attendance'];
            $guests = $_POST['guests'];
            $currentDateTime = date('Y-m-d H:i:s'); // Get current date and time

            // Begin transaction to update the users table
            $pdo->beginTransaction();
            try {
                // Update email and attendance/guest count in the users table
                $stmtUpdateUser = $pdo->prepare('UPDATE users SET email = :email, attendance = :attendance, guest_count = :guest_count, updated_at = :updated_at WHERE id = :user_id');
                $stmtUpdateUser->execute([
                    'email' => $email,
                    'attendance' => $attendance,
                    'guest_count' => $guests,
                    'updated_at' => $currentDateTime,
                    'user_id' => $user['id']
                ]);

                // Commit transaction
                $pdo->commit();

                $rsvpMessage = "RSVP submitted successfully!";
            } catch (PDOException $e) {
                // Roll back the transaction if there was an error
                $pdo->rollBack();
                $rsvpError = "Error submitting RSVP: " . $e->getMessage();
            }
        }
    } else {
        $rsvpError = "Invalid RSVP token.";
    }
} else {
    $rsvpError = "No RSVP token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($wedding_info['title']) ?>'s Wedding</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
            background-image: url("beautiful-pink-rose-flower-frame-with-watercolor-for-wedding-birthday-card-background-invitation-wallpaper-sticker-decoration-etc-vector.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgba(2, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .content, .rsvp-form {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
        }
        h1 {
            color: #d4a373;
            font-size: 2.5em;
        }
        .date {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        nav {
            margin: 20px 0;
        }
        nav a {
            color: #6b705c;
            text-decoration: none;
            margin: 0 10px;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
            text-align: left;
        }
        form input, form select, form textarea {
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
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Side: Content -->
        <div class="content">
            <h1><?= htmlspecialchars($wedding_info['title']) ?></h1>
            <p class="date"><?= htmlspecialchars($wedding_info['date']) ?></p>
            <nav>
                <a href="#story">Our Story</a>
                <a href="#details">Wedding Details</a>
                <a href="#rsvp">RSVP</a>
            </nav>
            
            <section id="story">
                <h2>Our Story</h2>
                <p><?= htmlspecialchars($wedding_info['story']) ?></p>
            </section>
            
            <section id="details">
                <h2>Wedding Details</h2>
                <p><?= htmlspecialchars($wedding_info['details']) ?></p>
            </section>
        </div>

        <!-- Right Side: RSVP Form -->
        <div class="rsvp-form">
            <h2>RSVP Form</h2>
            <?php if ($rsvpMessage): ?>
                <p style="color: green;"><?= htmlspecialchars($rsvpMessage); ?></p>
            <?php elseif ($rsvpError): ?>
                <p style="color: red;"><?= htmlspecialchars($rsvpError); ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required>

                <label for="attendance">Will you attend?</label>
                <select id="attendance" name="attendance" required>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>

                <label for="guests">How many people (including yourself)?</label>
                <input type="number" id="guests" name="guests" min="1" required>

                <input type="submit" value="Submit RSVP">
            </form>
        </div>
    </div>
</body>
</html>
