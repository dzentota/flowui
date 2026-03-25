<?php
/**
 * Simple test page to demonstrate FlowUI
 */
require_once __DIR__ . '/../vendor/autoload.php';

use FlowUI\Core\FlowUI;

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (FlowUI::validateRequest()) {
        $success = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowUI Test</title>
    <link rel="stylesheet" href="../assets/css/flow-ui.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        input, button {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: #1976d2;
            color: white;
            border: none;
            cursor: pointer;
        }
        .success {
            background: #4caf50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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

<h1>FlowUI Live Test</h1>

<?php if ($success): ?>
<div class="success">
    ✓ Form submitted successfully!<br>
    Email: <?= htmlspecialchars($_POST['email']) ?><br>
    <a href="test.php">Test again</a>
</div>
<?php endif; ?>

<div class="info">
    <strong>Test Instructions:</strong>
    <ul>
        <li>Try submitting empty form → should see "required" errors</li>
        <li>Enter "test" in email → should see "invalid email" error</li>
        <li>Enter password &lt; 8 chars → should see "min length" error</li>
        <li>Enter valid data → should see success message</li>
    </ul>
</div>

<?= FlowUI::start(['debug' => true]) ?>

<form method="POST" action="test.php">
    <h3>Login Form</h3>
    
    <label>Email Address</label>
    <input type="email" 
           name="email"
           data-rules="required|email"
           placeholder="your@email.com">

    <label>Password</label>
    <input type="password" 
           name="password"
           data-rules="required|min:8"
           placeholder="Minimum 8 characters">

    <button type="submit">Login</button>
</form>

<?= FlowUI::end() ?>

<hr style="margin: 40px 0;">
<p style="color: #666; font-size: 12px;">
    <strong>Debug Info:</strong><br>
    PHP Version: <?= PHP_VERSION ?><br>
    FlowUI: Active<br>
    CSRF Protection: Enabled<br>
    Client Validation: Enabled
</p>

</body>
</html>
