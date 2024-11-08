<?php
require 'config.php'; // Include the database configuration file

session_start(); // Start the session

// Redirect logged-in users to their respective dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'guest_dashboard.php'));
    exit;
}

// Handle the login process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loginInput = $_POST['login_input']; // Accept either username or email
    $password = $_POST['password'];

    // Fetch the user from the database using either username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :login OR email = :login");
    $stmt->execute(['login' => $loginInput]);
    
    // Fetch the user record
    $user = $stmt->fetch();

    // Verify the user's password
    if ($user && password_verify($password, $user['password'])) {
        // Store user information in session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect to the appropriate dashboard based on user role
        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'guest_dashboard.php'));
        exit;
    } else {
        $error = "Invalid username/email or password."; // Error message for failed login
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5; /* Light gray for a modern look */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            color: #555;
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 1rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="login_input">Username or Email:</label>
                <input type="text" name="login_input" id="login_input" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
