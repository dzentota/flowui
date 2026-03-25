# FlowUI Framework

**FlowUI** is a progressive enhancement PHP framework for rapid development of secure and interactive user interfaces. Write clean, semantic HTML and let FlowUI automatically add validation, CSRF protection, and interactivity.

## Philosophy

1. **HTML as Source of Truth**: Layout defines behavior. No hidden configs.
2. **Progressive Enhancement**: Functional without JS, enhanced with JS.
3. **Security by Default**: CSRF, XSS protection, and validation applied automatically.
4. **Zero-Build Step**: No Node.js, Webpack, or npm required for basic usage.

## Installation

```bash
composer require flowui/flowui
```

Or manually:
```bash
git clone https://github.com/your-org/flowui.git
cd flowui
composer install
```

## Quick Start

### Basic Form with Validation

```php
<?php
require_once 'vendor/autoload.php';

use FlowUI\Core\FlowUI;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (FlowUI::validateRequest()) {
        // Process form data
        echo "Success!";
        exit;
    }
}
?>

<?= FlowUI::start() ?>

<form action="" method="POST">
    <input type="email" 
           name="email"
           data-rules="required|email"
           placeholder="Email">

    <input type="password" 
           name="password"
           data-rules="required|min:8"
           placeholder="Password">

    <button type="submit">Login</button>
</form>

<?= FlowUI::end() ?>
```

## Features

### 🔒 Automatic CSRF Protection

FlowUI automatically injects CSRF tokens into all POST forms:

```php
<form method="POST">
    <!-- FlowUI automatically injects: -->
    <!-- <input type="hidden" name="_token" value="..."> -->
    
    <input type="text" name="username">
    <button type="submit">Submit</button>
</form>
```

### ✅ Declarative Validation

Define validation rules directly in HTML:

```php
<input type="text" 
       name="username"
       data-rules="required|min:3|alphanumeric">

<input type="email" 
       name="email"
       data-rules="required|email">
```

**Available Validation Rules:**
- `required` - Field must have a value
- `email` - Must be valid email
- `min:n` - Minimum length
- `max:n` - Maximum length
- `numeric` - Must be numeric
- `alpha` - Letters only
- `alphanumeric` - Letters and numbers only
- `url` - Must be valid URL
- `confirmed` - Must match `{field}_confirmation`

### 🔄 Sticky Forms

Forms automatically restore user input on validation errors:

```php
<?php
// User submits invalid data
// FlowUI automatically:
// 1. Validates form
// 2. Stores input in session
// 3. Re-renders form with values filled
// 4. Shows error messages
?>
```

### 📝 Inline Error Messages

FlowUI automatically injects error messages next to invalid fields:

```html
<!-- After validation error: -->
<input type="email" name="email" class="flow-error-field" value="invalid">
<div class="flow-error">The email must be a valid email address.</div>
```

### 🎨 UI Components

#### Tabs

```php
<div>
    <div data-tab="Tab 1">
        Content for tab 1
    </div>
    <div data-tab="Tab 2">
        Content for tab 2
    </div>
</div>
```

FlowUI automatically generates navigation and handles switching.

#### Accordion

```php
<div data-ui="accordion" data-single-open="true">
    <section>
        <header>Section 1</header>
        <content>Hidden content...</content>
    </section>
    <section>
        <header>Section 2</header>
        <content>More content...</content>
    </section>
</div>
```

#### Modal Dialog

```php
<button id="open-btn">Open Modal</button>

<dialog id="my-modal" data-trigger="open-btn">
    <h2>Modal Content</h2>
    <p>Click outside or press ESC to close.</p>
</dialog>
```

### ⚡ AJAX Forms (Optional)

```php
<form method="POST" data-ajax="true" data-ajax-target="#result">
    <input type="text" name="query">
    <button type="submit">Search</button>
</form>

<div id="result"></div>
```

## Configuration

```php
FlowUI::start([
    'debug' => true,                      // Show detailed errors
    'script_url' => '/js/flow-ui.js',    // Custom JS path
    'csrf_enabled' => true,               // Enable CSRF protection
    'csrf_token_name' => '_token',        // CSRF token field name
]);
```

## Client-Side Features

The lightweight JavaScript runtime (~10KB gzipped) provides:

- Client-side validation mirroring server rules
- Interactive component behavior
- AJAX form submissions
- Dynamic content hydration via MutationObserver

## Security

### CSRF Protection
Automatically enabled for all POST forms. Tokens are generated per-session and validated on submission.

### XSS Prevention
All user input restored to forms is automatically escaped using `htmlspecialchars`.

### Content Security Policy
FlowUI works with strict CSP policies. No inline scripts required.

## Examples

See the `examples/` directory for complete working examples:

- `examples/register.php` - Registration form with validation
- `examples/components.php` - UI components showcase

## Advanced Usage

### Custom Validation Rules

```php
use FlowUI\Validation\Validator;

$validator = new Validator($config);
$validator->addRule('phone', function($value, $params) {
    return preg_match('/^\d{10}$/', $value);
});
```

### Custom Processors

Create custom DOM processors to extend FlowUI:

```php
use FlowUI\Processors\ProcessorInterface;

class MyProcessor implements ProcessorInterface {
    public function process(\DOMDocument $dom): void {
        // Transform DOM as needed
    }
}
```

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with polyfills for `fetch` and `Promise`)

## Requirements

- PHP 7.4+
- Composer
- masterminds/html5 library

## Development Roadmap

### Phase 1: MVP ✅
- Core engine (start/end)
- Form processing with CSRF
- Validation system (PHP + JS)

### Phase 2: UI Components (In Progress)
- Tabs, Accordion, Modal components
- CSS presets (Bootstrap/Tailwind compatible)

### Phase 3: Performance
- DOM structure caching
- AJAX form handling

### Phase 4: Ecosystem
- IDE plugins for syntax highlighting
- Additional components library

## Contributing

Contributions are welcome! Please see CONTRIBUTING.md for details.

## License

MIT License - see LICENSE file for details.

## Credits

Built with:
- [Masterminds/HTML5](https://github.com/Masterminds/html5-php) for robust HTML parsing
- Vanilla JavaScript for zero-dependency client runtime
