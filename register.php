<?php
require 'config.php'; // Include the database configuration file

// Handle user registration
$message = ''; // Initialize message variable
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
    $role = $_POST['role'];
    $email = $_POST['email'] ?? null; // Optional email field

    // Insert new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (:username, :password, :role, :email)");
    try {
        if ($stmt->execute(['username' => $username, 'password' => $password, 'role' => $role, 'email' => $email])) {
            $message = "<div class='success'>Registration successful! You can now <a href='login.php'>log in</a>.</div>";
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $message = "<div class='error'>Error: Username already exists. Please choose a different username.</div>";
        } else {
            $message = "<div class='error'>An error occurred during registration: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            margin: 10px 0; /* Add margin for spacing */
        }

        .success {
            color: green;
            text-align: center;
            margin: 10px 0; /* Add margin for spacing */
        }

        .message {
            min-height: 30px; /* Set a minimum height for message area */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        <div class="message"><?= $message; ?></div> <!-- Message area -->
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="guest">Guest</option>
                    <option value="bride">Bride</option>
                    <option value="groom">Groom</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email (optional):</label>
                <input type="email" name="email" id="email">
            </div>

            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
