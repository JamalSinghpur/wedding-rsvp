<?php
session_start();
session_destroy(); // Destroy the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6; /* Soft light gray background */
            color: #333; /* Dark gray text for readability */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        h1 {
            color: #2c3e50; /* Professional dark color */
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-in-out; /* Smooth fade-in animation */
        }

        a {
            display: inline-block;
            padding: 12px 24px;
            color: #fff;
            background-color: #3498db; /* Bright blue color for the button */
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease; /* Smooth transition for hover effects */
        }

        a:hover {
            background-color: #2980b9; /* Darker blue on hover */
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2); /* Increase shadow on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        a:active {
            background-color: #1c639a; /* Even darker blue on click */
            transform: translateY(0); /* Reset position on click */
        }

        .container {
            text-align: center;
            padding: 40px;
            background-color: #ffffff; /* White container background */
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            animation: slideUp 1s ease-out; /* Slide-up animation for the container */
        }

        /* Keyframe animations */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideUp {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Wedding RSVP Application</h1>
        <a href="login.php">Login</a>
    </div>
</body>
</html>
