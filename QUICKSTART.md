# FlowUI Quick Start Guide

Get started with FlowUI in 5 minutes!

## Installation

### Option 1: Composer (Recommended)

```bash
composer require flowui/flowui
```

### Option 2: Manual

```bash
git clone https://github.com/your-org/flowui.git
cd flowui
composer install
```

## Your First FlowUI Page

Create a new PHP file:

```php
<?php
// index.php
require 'vendor/autoload.php';

use FlowUI\Core\FlowUI;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (FlowUI::validateRequest()) {
        echo "<h2>Success! Email: " . htmlspecialchars($_POST['email']) . "</h2>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My First FlowUI App</title>
    <link rel="stylesheet" href="vendor/flowui/flowui/assets/css/flow-ui.css">
    <style>
        body { max-width: 500px; margin: 50px auto; font-family: sans-serif; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #1976d2; color: white; border: none; }
    </style>
</head>
<body>

<?= FlowUI::start() ?>

<h1>Contact Form</h1>

<form method="POST">
    <label>Email</label>
    <input type="email" 
           name="email"
           data-rules="required|email"
           placeholder="your@email.com">

    <label>Message</label>
    <textarea name="message"
              data-rules="required|min:10"
              placeholder="Your message..."></textarea>

    <button type="submit">Send</button>
</form>

<?= FlowUI::end() ?>

</body>
</html>
```

## Run It

```bash
php -S localhost:8000
```

Visit http://localhost:8000 and try:
1. Submit empty form → See validation errors
2. Enter invalid email → See email error
3. Short message → See length error
4. Valid data → Success!

## What Just Happened?

FlowUI automatically:
- ✅ Added CSRF protection
- ✅ Validated form on submission
- ✅ Showed error messages
- ✅ Restored your input on errors
- ✅ Added client-side validation
- ✅ Made form interactive

## Next Steps

### Add More Validation Rules

```php
<input type="text" 
       name="username"
       data-rules="required|min:3|max:20|alphanumeric">

<input type="number" 
       name="age"
       data-rules="required|numeric|min:18">

<input type="url" 
       name="website"
       data-rules="url">
```

### Create Tabs

```php
<?= FlowUI::start() ?>

<div>
    <div data-tab="Profile">
        <h3>Your Profile</h3>
        <p>Profile information here...</p>
    </div>
    
    <div data-tab="Settings">
        <h3>Settings</h3>
        <p>Settings options here...</p>
    </div>
</div>

<?= FlowUI::end() ?>
```

### Add Modal Dialog

```php
<?= FlowUI::start() ?>

<button id="open-help">Need Help?</button>

<dialog id="help-modal" data-trigger="open-help">
    <h2>Help & Support</h2>
    <p>Contact us at support@example.com</p>
    <button onclick="this.closest('dialog').close()">Close</button>
</dialog>

<?= FlowUI::end() ?>
```

### Create Accordion

```php
<?= FlowUI::start() ?>

<div data-ui="accordion" data-single-open="true">
    <section>
        <header>FAQ Question 1</header>
        <content>Answer to question 1...</content>
    </section>
    
    <section>
        <header>FAQ Question 2</header>
        <content>Answer to question 2...</content>
    </section>
</div>

<?= FlowUI::end() ?>
```

## Configuration

Customize FlowUI behavior:

```php
FlowUI::start([
    'debug' => true,                    // Show errors
    'csrf_enabled' => true,             // Enable CSRF
    'script_url' => '/js/flow-ui.js',  // Custom JS path
]);
```

## Common Patterns

### Login Form

```php
<form method="POST">
    <input type="email" 
           name="email"
           data-rules="required|email">
    
    <input type="password" 
           name="password"
           data-rules="required|min:8">
    
    <button>Login</button>
</form>
```

### Registration Form

```php
<form method="POST">
    <input type="text" 
           name="name"
           data-rules="required|min:3">
    
    <input type="email" 
           name="email"
           data-rules="required|email">
    
    <input type="password" 
           name="password"
           data-rules="required|min:8">
    
    <input type="password" 
           name="password_confirmation"
           data-rules="required">
    
    <button>Register</button>
</form>
```

### Contact Form

```php
<form method="POST">
    <input type="text" 
           name="name"
           data-rules="required">
    
    <input type="email" 
           name="email"
           data-rules="required|email">
    
    <input type="text" 
           name="subject"
           data-rules="required|min:5">
    
    <textarea name="message"
              data-rules="required|min:20"></textarea>
    
    <button>Send Message</button>
</form>
```

## Validation Rules Reference

| Rule | Example | Description |
|------|---------|-------------|
| `required` | `data-rules="required"` | Field must have value |
| `email` | `data-rules="email"` | Must be valid email |
| `min:n` | `data-rules="min:8"` | Minimum n characters |
| `max:n` | `data-rules="max:100"` | Maximum n characters |
| `numeric` | `data-rules="numeric"` | Must be number |
| `alpha` | `data-rules="alpha"` | Letters only |
| `alphanumeric` | `data-rules="alphanumeric"` | Letters & numbers |
| `url` | `data-rules="url"` | Valid URL |
| `confirmed` | `data-rules="confirmed"` | Match confirmation field |

## Tips

1. **Combine Rules**: Use `|` to chain rules: `required|email|min:5`
2. **Debug Mode**: Enable to see detailed errors
3. **Custom Styling**: Override `.flow-error` and `.flow-error-field` classes
4. **AJAX Forms**: Add `data-ajax="true"` for no-refresh submission
5. **Progressive**: Forms work without JavaScript!

## Examples

Check the `examples/` directory:
- `examples/register.php` - Full registration form
- `examples/components.php` - All UI components

## Need Help?

- 📖 Read the full [README.md](README.md)
- 🔍 See [IMPLEMENTATION.md](IMPLEMENTATION.md) for technical details
- 🐛 Report issues on GitHub

## That's It!

You're ready to build with FlowUI. Write HTML, get features automatically. No build tools, no complex setup, just clean code.

Happy coding! 🚀
