<?php
require 'config.php';

// Only accessible by Admin
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Initialize search query and attendance filter
$search = '';
$attendanceFilter = 'all'; // Default to show all
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}
if (isset($_POST['attendance'])) {
    $attendanceFilter = $_POST['attendance'];
}

// Build query based on attendance filter
$query = "SELECT * FROM users WHERE role = 'guest' AND username LIKE :search";
if ($attendanceFilter == 'attending') {
    $query .= " AND attendance = 'yes'";
} elseif ($attendanceFilter == 'not_attending') {
    $query .= " AND attendance = 'no'";
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute(['search' => "%$search%"]);
$guests = $stmt->fetchAll();

// Fetch total registered guests
$totalRegisteredGuestsStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'guest'");
$totalRegisteredGuests = $totalRegisteredGuestsStmt->fetchColumn();

// Calculate totals
$totalGuests = count($guests);
$attendingGuests = count(array_filter($guests, function($guest) {
    return $guest['attendance'] === 'yes';
}));
$submittedRSVPs = count(array_filter($guests, function($guest) {
    return !empty($guest['updated_at']);
}));

// Handle delete user action
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->execute(['id' => $userId]);
    header("Location: admin.php"); // Redirect to refresh the page
    exit;
}

// Handle update user action
if (isset($_POST['update_user'])) {
    $userId = $_POST['user_id'];
    $newUsername = $_POST['username'];
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash new password

    // Update user information
    $updateStmt = $pdo->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
    $updateStmt->execute(['username' => $newUsername, 'password' => $newPassword, 'id' => $userId]);
    header("Location: admin.php"); // Redirect to refresh the page
    exit;
}

// Handle token generation
if (isset($_POST['generate_token'])) {
    $userId = $_POST['user_id'];
    $token = bin2hex(random_bytes(16)); // Generate a secure random token

    // Update the user's token in the database
    $updateTokenStmt = $pdo->prepare("UPDATE users SET rsvp_token = :token WHERE id = :id");
    $updateTokenStmt->execute(['token' => $token, 'id' => $userId]);

    // Redirect to the admin page
    header("Location: admin.php"); // Redirect to refresh the page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #35424a;
            color: #ffffff;
            padding: 20px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        .btn {
            display: inline-block;
            color: #fff;
            background: #35424a;
            padding: 10px 20px;
            margin: 10px 0;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #3e606f;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background: #ffffff;
        }
        table th, table td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background: #35424a;
            color: white;
        }
        table tr:nth-child(even) {
            background: #f2f2f2;
        }
        .user-actions {
            display: flex;
            gap: 10px;
        }
        .token-link {
            color: #007bff;
            text-decoration: none;
        }
        .token-link:hover {
            text-decoration: underline;
        }
        .stats {
            margin-bottom: 20px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .search-form {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
    </header>
    
    <div class="container">
        <a href="register.php" class="btn">Register New Guest</a>
        <a href="index.php" class="btn">Logout</a> <!-- Redirect to index.php -->

        <!-- Stats Section -->
        <div class="stats">
            <h2>Guest Statistics</h2>
            <p>Total Guests: <?= $totalGuests ?></p>
            <p>Guests Attending: <?= $attendingGuests ?></p>
            <p>RSVP Forms Submitted: <?= $submittedRSVPs ?> (out of <?= $totalRegisteredGuests ?> registered guests)</p>
        </div>

        <!-- Search Form with Attendance Filter -->
        <div class="search-form">
            <form method="POST">
                <input type="text" name="search" placeholder="Search by Guest Name" value="<?= htmlspecialchars($search) ?>">
                <select name="attendance">
                    <option value="all" <?= $attendanceFilter == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="attending" <?= $attendanceFilter == 'attending' ? 'selected' : '' ?>>Attending</option>
                    <option value="not_attending" <?= $attendanceFilter == 'not_attending' ? 'selected' : '' ?>>Not Attending</option>
                </select>
                <button type="submit" class="btn">Search</button>
            </form>
        </div>

        <!-- Navigation Buttons for Bride/Groom and Event Details -->
        <div class="navigation-buttons" style="margin: 20px 0;">
            <a href="bridegroom.php" class="btn">Bride/Groom Info</a>
            <a href="event_details.php" class="btn">Event Details</a>
        </div>

        <h2>Guests List</h2>
        <table>
            <tr>
                <th>Guest Name</th>
                <th>Email</th>
                <th>RSVP Token</th>
                <th>Attendance Status</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($guests as $guest): ?>
            <tr>
                <td><?= htmlspecialchars($guest['username']) ?></td>
                <td><?= htmlspecialchars($guest['email']) ?></td>
                <td>
                    <?php if ($guest['rsvp_token']): ?>
                        <a href="rsvp_form.php?token=<?= htmlspecialchars($guest['rsvp_token']) ?>" class="token-link">
                            <?= htmlspecialchars($guest['rsvp_token']) ?>
                        </a>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $guest['id'] ?>">
                            <button type="submit" name="generate_token" class="btn">Generate Token</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $guest['attendance'] == 'yes' ? 'Attending' : 'Not Attending' ?> <br>
                    Submitted: <?= $guest['updated_at'] ? htmlspecialchars($guest['updated_at']) : 'NULL' ?> <br>
                    Guests: <?= $guest['guest_count'] > 0 ? htmlspecialchars($guest['guest_count']) : 'NULL' ?>
                </td>
                <td><?= htmlspecialchars($guest['role']) ?></td>
                <td class="user-actions">
                    <!-- Update User Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $guest['id'] ?>">
                        <input type="text" name="username" placeholder="New Username" required>
                        <input type="password" name="password" placeholder="New Password" required>
                        <button type="submit" name="update_user" class="btn">Update</button>
                    </form>

                    <!-- Delete User Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $guest['id'] ?>">
                        <button type="submit" name="delete_user" class="btn" style="background: #d9534f;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
