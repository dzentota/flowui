<?php
/**
 * FlowUI Phase 3 - Performance & AJAX Demo
 * Demonstrates caching, AJAX forms, and performance monitoring
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FlowUI\Core\FlowUI;
use FlowUI\Core\Performance;

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax_test'])) {
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_FLOWUI_AJAX']) || 
              !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    
    if ($isAjax) {
        // Return only the result HTML for AJAX
        $name = htmlspecialchars($_POST['name'] ?? 'Guest');
        $message = htmlspecialchars($_POST['message'] ?? '');
        
        echo FlowUI::start();
        ?>
        <div data-alert="success">
            <strong>Success!</strong> Message received from <?= $name ?>
        </div>
        <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; margin-top: 10px;">
            <strong>Your message:</strong><br>
            <?= nl2br($message) ?>
        </div>
        <p style="margin-top: 15px;">
            <a href="performance.php">← Back to form</a>
        </p>
        <?php
        echo FlowUI::end();
        exit;
    }
}

// Handle cache actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clear_cache':
            FlowUI::clearCache();
            FlowUI::success('Cache cleared successfully!');
            break;
        case 'enable_cache':
            FlowUI::cache()->enable();
            FlowUI::success('Cache enabled!');
            break;
        case 'disable_cache':
            FlowUI::cache()->disable();
            FlowUI::warning('Cache disabled!');
            break;
    }
    header('Location: performance.php');
    exit;
}

// Get cache stats
$cacheStats = FlowUI::cache()->getStats();
$perfReport = Performance::getReport();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowUI Phase 3 - Performance & AJAX</title>
    <link rel="stylesheet" href="../assets/css/flow-ui.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        h1 { color: #1976d2; }
        section {
            margin: 40px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #1976d2;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        button, .button {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .button:hover {
            background: #1565c0;
        }
        .button-danger {
            background: #f44336;
        }
        .button-danger:hover {
            background: #d32f2f;
        }
        .button-warning {
            background: #ff9800;
        }
        .button-warning:hover {
            background: #f57c00;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        #ajax-result {
            min-height: 50px;
        }
    </style>
</head>
<body>

<?= FlowUI::start([
    'debug' => true, 
    'show_performance' => true,
    'cache_enabled' => true
]) ?>

<h1>⚡ FlowUI Phase 3: Performance & AJAX</h1>

<!-- Cache Statistics -->
<section>
    <h2>Cache System</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $cacheStats['enabled'] ? 'ON' : 'OFF' ?></div>
            <div class="stat-label">Cache Status</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= $cacheStats['total_files'] ?></div>
            <div class="stat-label">Cached Files</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= number_format($cacheStats['total_size'] / 1024, 2) ?> KB</div>
            <div class="stat-label">Cache Size</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= number_format(memory_get_usage(true) / 1024 / 1024, 2) ?> MB</div>
            <div class="stat-label">Memory Usage</div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <?php if ($cacheStats['enabled']): ?>
            <a href="?action=disable_cache" class="button button-warning">Disable Cache</a>
        <?php else: ?>
            <a href="?action=enable_cache" class="button">Enable Cache</a>
        <?php endif; ?>
        
        <a href="?action=clear_cache" class="button button-danger">Clear Cache</a>
        
        <button onclick="location.reload()">Reload Page</button>
    </div>
    
    <p style="margin-top: 20px; color: #666; font-size: 14px;">
        <strong>Tip:</strong> Reload the page multiple times to see caching in action. 
        The second load should be faster as the DOM structure is cached.
    </p>
</section>

<!-- AJAX Form Demo -->
<section>
    <h2>AJAX Form Submission</h2>
    <p>This form submits without page reload:</p>
    
    <form method="POST" data-ajax="true" data-ajax-target="#ajax-result">
        <input type="hidden" name="ajax_test" value="1">
        
        <label>Your Name:</label>
        <input type="text" 
               name="name"
               data-rules="required|min:2"
               placeholder="Enter your name"
               required>
        
        <label>Message:</label>
        <textarea name="message"
                  data-rules="required|min:10"
                  placeholder="Enter your message (min 10 characters)"
                  required></textarea>
        
        <button type="submit">Send via AJAX</button>
    </form>
    
    <div id="ajax-result" style="margin-top: 20px;">
        <!-- AJAX response will appear here -->
    </div>
</section>

<!-- AJAX API Demo -->
<section>
    <h2>JavaScript AJAX API</h2>
    <p>Use FlowUI's JavaScript methods to load content dynamically:</p>
    
    <button onclick="loadContent()">Load External Content</button>
    <button onclick="clearContent()">Clear</button>
    
    <div id="dynamic-content" style="margin-top: 20px; min-height: 100px; background: white; padding: 15px; border-radius: 4px;">
        <p style="color: #666;">Click "Load External Content" to see AJAX in action...</p>
    </div>
    
    <script>
        function loadContent() {
            // Using FlowUI's static load method
            FlowUI.load('/examples/test.php', '#dynamic-content')
                .then(html => {
                    console.log('Content loaded successfully');
                })
                .catch(error => {
                    console.error('Failed to load content:', error);
                });
        }
        
        function clearContent() {
            document.getElementById('dynamic-content').innerHTML = 
                '<p style="color: #666;">Content cleared. Click "Load External Content" again.</p>';
        }
        
        // Listen for AJAX events
        document.addEventListener('flowui:ajax:success', (e) => {
            console.log('AJAX Success:', e.detail);
        });
        
        document.addEventListener('flowui:ajax:error', (e) => {
            console.error('AJAX Error:', e.detail);
        });
    </script>
</section>

<!-- Performance Monitoring -->
<section>
    <h2>Performance Monitoring</h2>
    
    <p>FlowUI automatically tracks performance metrics:</p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) ?> MB</div>
            <div class="stat-label">Peak Memory</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= count($perfReport['marks']) ?></div>
            <div class="stat-label">Performance Marks</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= count($perfReport['counters']) ?></div>
            <div class="stat-label">Counters</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= PHP_VERSION ?></div>
            <div class="stat-label">PHP Version</div>
        </div>
    </div>
    
    <p style="color: #666; font-size: 14px; margin-top: 20px;">
        <strong>Note:</strong> Performance stats are embedded in HTML comments at the bottom of the page when debug mode is enabled.
        Check the page source to see detailed metrics.
    </p>
</section>

<?= FlowUI::end() ?>

<hr style="margin: 40px 0;">
<p style="text-align: center; color: #666;">
    <a href="/examples/advanced.php">← Phase 2</a> |
    <a href="/">Home</a> |
    <strong>Phase 3</strong>
</p>

</body>
</html>
