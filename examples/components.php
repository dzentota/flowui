<?php
/**
 * FlowUI Example - UI Components
 * Demonstrates tabs, accordion, and modal components
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FlowUI\Core\FlowUI;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowUI - Components Example</title>
    <link rel="stylesheet" href="../assets/css/flow-ui.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        h1 {
            color: #1976d2;
        }
        section {
            margin-bottom: 50px;
        }
        .demo-button {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .demo-button:hover {
            background: #1565c0;
        }
    </style>
</head>
<body>

<?= FlowUI::start(['debug' => true]) ?>

<h1>FlowUI Components Demo</h1>

<!-- Tabs Example -->
<section>
    <h2>Tabs Component</h2>
    
    <div>
        <div data-tab="Overview">
            <h3>Overview Tab</h3>
            <p>This is the overview section. FlowUI automatically generates navigation and handles tab switching.</p>
        </div>
        
        <div data-tab="Features">
            <h3>Features Tab</h3>
            <ul>
                <li>Automatic tab navigation generation</li>
                <li>ARIA attributes for accessibility</li>
                <li>Smooth transitions</li>
            </ul>
        </div>
        
        <div data-tab="Settings">
            <h3>Settings Tab</h3>
            <p>Configure your preferences here.</p>
        </div>
    </div>
</section>

<!-- Accordion Example -->
<section>
    <h2>Accordion Component</h2>
    
    <div data-ui="accordion" data-single-open="true">
        <section>
            <header>What is FlowUI?</header>
            <content>
                FlowUI is a progressive enhancement framework that transforms semantic HTML 
                into interactive components with built-in validation and security.
            </content>
        </section>
        
        <section>
            <header>How does it work?</header>
            <content>
                FlowUI uses output buffering to intercept HTML, parse it with a DOM parser,
                apply transformations, and inject interactive JavaScript.
            </content>
        </section>
        
        <section>
            <header>Why use FlowUI?</header>
            <content>
                Write clean, semantic HTML without boilerplate JavaScript. Get validation,
                CSRF protection, and UI components automatically.
            </content>
        </section>
    </div>
</section>

<!-- Modal Example -->
<section>
    <h2>Modal Component</h2>
    
    <button id="open-modal" class="demo-button">Open Modal</button>
    
    <dialog id="my-modal" data-trigger="open-modal">
        <h3>Modal Dialog</h3>
        <p>This is a modal dialog. Click outside or press ESC to close.</p>
        <button class="demo-button" onclick="document.getElementById('my-modal').close()">
            Close
        </button>
    </dialog>
</section>

<?= FlowUI::end() ?>

</body>
</html>
