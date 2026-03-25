<?php
/**
 * FlowUI Public Index
 * Example entry point for serving FlowUI examples
 */

// Check if running PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $requestUri = $_SERVER['REQUEST_URI'];
    $parsedUri = parse_url($requestUri, PHP_URL_PATH);
    
    // Handle assets directory - serve from project root
    if (strpos($parsedUri, '/assets/') === 0) {
        $assetFile = __DIR__ . '/..' . $parsedUri;
        if (is_file($assetFile)) {
            // Determine content type
            $ext = pathinfo($assetFile, PATHINFO_EXTENSION);
            $contentTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
            ];
            
            if (isset($contentTypes[$ext])) {
                header('Content-Type: ' . $contentTypes[$ext]);
            }
            
            readfile($assetFile);
            exit;
        }
    }
    
    // Handle examples directory
    if (strpos($parsedUri, '/examples/') === 0) {
        $exampleFile = __DIR__ . '/..' . $parsedUri;
        if (is_file($exampleFile)) {
            require $exampleFile;
            exit;
        }
    }
}

// Route to examples
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    // Show index of examples
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FlowUI Examples</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
            }
            h1 { color: #1976d2; }
            .example {
                border: 1px solid #e0e0e0;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 8px;
            }
            .example h2 { margin-top: 0; }
            a {
                display: inline-block;
                padding: 10px 20px;
                background: #1976d2;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 10px;
            }
            a:hover { background: #1565c0; }
        </style>
    </head>
    <body>
        <h1>FlowUI Framework Examples</h1>
        <p>Explore working examples of FlowUI features:</p>
        
        <div class="example">
            <h2>Registration Form</h2>
            <p>Demonstrates form validation, CSRF protection, error handling, and sticky forms.</p>
            <a href="/examples/register.php">View Example →</a>
        </div>
        
        <div class="example">
            <h2>UI Components</h2>
            <p>Showcase of tabs, accordion, and modal dialog components.</p>
            <a href="/examples/components.php">View Example →</a>
        </div>
        
        <div class="example">
            <h2>Advanced Components (Phase 2)</h2>
            <p>Dropdowns, alerts, sortable tables, and pagination - all the new Phase 2 features!</p>
            <a href="/examples/advanced.php">View Example →</a>
        </div>
        
        <div class="example">
            <h2>Performance & AJAX (Phase 3)</h2>
            <p>DOM caching, AJAX forms, performance monitoring, and dynamic content loading!</p>
            <a href="/examples/performance.php">View Example →</a>
        </div>
        
        <hr style="margin: 40px 0;">
        <p><strong>Quick Start:</strong> Run <code>php -S localhost:8000 -t public</code> in the project root.</p>
        <p><strong>Documentation:</strong> See <a href="https://github.com/flowui/flowui">README.md</a> for full documentation.</p>
    </body>
    </html>
    <?php
    exit;
}

// For other URIs, just show 404
http_response_code(404);
echo "<h1>404 Not Found</h1>";
