<?php
/**
 * FlowUI Example - Registration Form
 * Demonstrates form validation, CSRF protection, and error handling
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FlowUI\Core\FlowUI;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (FlowUI::validateRequest()) {
        // Validation passed - save user
        $email = htmlspecialchars($_POST['email']);
        echo "<h2>Success! User registered: {$email}</h2>";
        echo '<a href="register.php">Register another user</a>';
        exit;
    }
    // Validation failed - form will be re-rendered with errors
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowUI - Registration Example</title>
    <link rel="stylesheet" href="../assets/css/flow-ui.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        input, button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background: #1976d2;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #1565c0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?= FlowUI::start(['debug' => true]) ?>

<div class="info">
    <strong>FlowUI Registration Demo</strong>
    <p>Try submitting with invalid data to see validation in action!</p>
</div>

<form action="register.php" method="POST" class="auth-form">
    <h2>User Registration</h2>

    <label for="name">Full Name</label>
    <input type="text" 
           id="name"
           name="name"
           data-rules="required|min:3|alpha"
           placeholder="John Doe">

    <label for="email">Email Address</label>
    <input type="email" 
           id="email"
           name="email"
           data-rules="required|email"
           placeholder="john@example.com">

    <label for="password">Password</label>
    <input type="password" 
           id="password"
           name="password"
           data-rules="required|min:8|confirmed"
           placeholder="Minimum 8 characters">

    <label for="password_confirmation">Confirm Password</label>
    <input type="password" 
           id="password_confirmation"
           name="password_confirmation"
           data-rules="required"
           placeholder="Repeat password">

    <button type="submit">Register</button>
</form>

<?= FlowUI::end() ?>

</body>
</html>
