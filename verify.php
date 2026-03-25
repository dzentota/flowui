<?php
/**
 * FlowUI Verification Script
 * Quick test to ensure FlowUI is working correctly
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "FlowUI Framework Verification\n";
echo "==============================\n\n";

// Test 1: Autoloading
echo "✓ Test 1: Autoloading works\n";

// Test 2: Core classes
try {
    $config = new \FlowUI\Core\Config();
    echo "✓ Test 2: Config class loaded\n";
} catch (Exception $e) {
    echo "✗ Test 2 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Session class
try {
    // Prevent "headers already sent" error
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $session = new \FlowUI\Core\Session();
    $token = $session->getToken();
    echo "✓ Test 3: Session class working (Token: " . substr($token, 0, 16) . "...)\n";
} catch (Exception $e) {
    echo "✗ Test 3 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Validator
try {
    $validator = new \FlowUI\Validation\Validator($config);
    $errors = $validator->validate(
        ['email' => 'invalid'],
        ['email' => 'required|email']
    );
    
    if (!empty($errors)) {
        echo "✓ Test 4: Validator working (Found validation errors as expected)\n";
    } else {
        echo "✗ Test 4 Failed: Validator should have found errors\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Test 4 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: FormProcessor
try {
    $formProcessor = new \FlowUI\Forms\FormProcessor($session, $config);
    echo "✓ Test 5: FormProcessor class loaded\n";
} catch (Exception $e) {
    echo "✗ Test 5 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: FlowUI main class
try {
    // Capture output
    ob_start();
    \FlowUI\Core\FlowUI::start();
    echo '<form method="POST"><input name="test"></form>';
    $output = \FlowUI\Core\FlowUI::end();
    
    if (strpos($output, '<form') !== false && strpos($output, 'flow-ui.js') !== false) {
        echo "✓ Test 6: FlowUI processing works\n";
    } else {
        echo "✗ Test 6 Failed: FlowUI output doesn't contain expected elements\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Test 6 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 7: JavaScript file exists
$jsPath = __DIR__ . '/assets/js/flow-ui.js';
if (file_exists($jsPath)) {
    $jsSize = filesize($jsPath);
    echo "✓ Test 7: JavaScript runtime exists (" . number_format($jsSize / 1024, 2) . " KB)\n";
} else {
    echo "✗ Test 7 Failed: JavaScript file not found\n";
    exit(1);
}

// Test 8: CSS file exists
$cssPath = __DIR__ . '/assets/css/flow-ui.css';
if (file_exists($cssPath)) {
    $cssSize = filesize($cssPath);
    echo "✓ Test 8: CSS stylesheet exists (" . number_format($cssSize / 1024, 2) . " KB)\n";
} else {
    echo "✗ Test 8 Failed: CSS file not found\n";
    exit(1);
}

// Test 9: Examples exist
$examples = ['register.php', 'components.php'];
$examplesExist = true;
foreach ($examples as $example) {
    if (!file_exists(__DIR__ . '/examples/' . $example)) {
        $examplesExist = false;
        break;
    }
}

if ($examplesExist) {
    echo "✓ Test 9: Example files present\n";
} else {
    echo "✗ Test 9 Failed: Example files missing\n";
    exit(1);
}

// Test 10: PHPUnit tests
$phpunitPath = __DIR__ . '/vendor/bin/phpunit';
if (file_exists($phpunitPath)) {
    echo "✓ Test 10: PHPUnit installed\n";
} else {
    echo "✗ Test 10 Failed: PHPUnit not found\n";
    exit(1);
}

echo "\n";
echo "==============================\n";
echo "All Tests Passed! ✓\n";
echo "==============================\n\n";

echo "FlowUI is ready to use!\n\n";
echo "Quick Start:\n";
echo "  1. Run: php -S localhost:8000 -t public\n";
echo "  2. Visit: http://localhost:8000\n";
echo "  3. Check examples at: http://localhost:8000/examples/register.php\n\n";
echo "Documentation:\n";
echo "  - Quick Start: QUICKSTART.md\n";
echo "  - Full Docs: README.md\n";
echo "  - Implementation Details: IMPLEMENTATION.md\n\n";

exit(0);
