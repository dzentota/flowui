<?php
/**
 * FlowUI Phase 2 - Advanced Components Demo
 * Demonstrates dropdowns, alerts, sortable tables, and pagination
 */

require_once __DIR__ . '/../vendor/autoload.php';

use FlowUI\Core\FlowUI;
use FlowUI\Components\Paginator;

// Sample data for table
$users = [
    ['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
    ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'User'],
    ['id' => 3, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'User'],
    ['id' => 4, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'role' => 'Editor'],
    ['id' => 5, 'name' => 'Eve Adams', 'email' => 'eve@example.com', 'role' => 'User'],
    ['id' => 6, 'name' => 'Frank Miller', 'email' => 'frank@example.com', 'role' => 'Admin'],
    ['id' => 7, 'name' => 'Grace Lee', 'email' => 'grace@example.com', 'role' => 'Editor'],
    ['id' => 8, 'name' => 'Henry Ford', 'email' => 'henry@example.com', 'role' => 'User'],
];

// Handle sorting
$sortColumn = $_GET['sort'] ?? 'id';
$sortDir = $_GET['dir'] ?? 'asc';

if (in_array($sortColumn, ['id', 'name', 'email', 'role'])) {
    usort($users, function($a, $b) use ($sortColumn, $sortDir) {
        $result = $a[$sortColumn] <=> $b[$sortColumn];
        return $sortDir === 'desc' ? -$result : $result;
    });
}

// Handle action button clicks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'success':
                FlowUI::success('Operation completed successfully!');
                break;
            case 'error':
                FlowUI::error('An error occurred. Please try again.');
                break;
            case 'warning':
                FlowUI::warning('Warning: This action cannot be undone.');
                break;
            case 'info':
                FlowUI::info('Here is some helpful information.');
                break;
        }
        header('Location: advanced.php');
        exit;
    }
}

// Pagination example
$totalItems = 47;
$paginator = Paginator::fromRequest($totalItems, 10);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowUI Phase 2 - Advanced Components</title>
    <link rel="stylesheet" href="../assets/css/flow-ui.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        h1 {
            color: #1976d2;
        }
        section {
            margin: 40px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        button {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #1565c0;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 4px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?= FlowUI::start(['debug' => true]) ?>

<h1>🚀 FlowUI Phase 2: Advanced Components</h1>

<!-- Alert/Notification Demo -->
<section>
    <h2>Alerts & Notifications</h2>
    <p>Test different alert types:</p>
    
    <div class="demo-grid">
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="success">
            <button type="submit">Show Success</button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="error">
            <button type="submit" style="background: #f44336;">Show Error</button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="warning">
            <button type="submit" style="background: #ff9800;">Show Warning</button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="info">
            <button type="submit" style="background: #2196f3;">Show Info</button>
        </form>
    </div>
    
    <!-- Static alerts with auto-dismiss -->
    <div style="margin-top: 20px;">
        <div data-alert="info" data-auto-dismiss="5000" data-dismissible="true">
            This alert will auto-dismiss in 5 seconds!
        </div>
    </div>
</section>

<!-- Dropdown Demo -->
<section>
    <h2>Dropdown Menus</h2>
    <p>Click the button to open a dropdown:</p>
    
    <button id="actions-dropdown" data-toggle="dropdown">
        Actions Menu ▼
    </button>
    
    <menu id="actions-menu">
        <li><a href="#edit">Edit Profile</a></li>
        <li><a href="#settings">Settings</a></li>
        <li><a href="#help">Help & Support</a></li>
        <li><hr style="margin: 0.5rem 0;"></li>
        <li><a href="#logout" style="color: #f44336;">Logout</a></li>
    </menu>
    
    <button data-toggle="dropdown" style="background: #4caf50;">
        Export ▼
    </button>
    
    <ul>
        <li><button>Export as PDF</button></li>
        <li><button>Export as Excel</button></li>
        <li><button>Export as CSV</button></li>
    </ul>
</section>

<!-- Sortable Table Demo -->
<section>
    <h2>Sortable Data Table</h2>
    <p>Click column headers to sort (notice the URL changes with sort parameters):</p>
    
    <table>
        <thead>
            <tr>
                <th sortable data-column="id">ID</th>
                <th sortable data-column="name">Name</th>
                <th sortable data-column="email">Email</th>
                <th sortable data-column="role">Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px; color: #666;">
        <strong>Current Sort:</strong> <?= htmlspecialchars($sortColumn) ?> 
        (<?= $sortDir === 'asc' ? 'ascending' : 'descending' ?>)
    </p>
</section>

<!-- Pagination Demo -->
<section>
    <h2>Pagination</h2>
    <p>Showing items <?= $paginator->getOffset() + 1 ?> - 
       <?= min($paginator->getOffset() + 10, $totalItems) ?> of <?= $totalItems ?></p>
    
    <?= $paginator->render() ?>
    
    <p style="margin-top: 20px; color: #666;">
        <strong>Current Page:</strong> <?= $_GET['page'] ?? 1 ?> / <?= $paginator->getTotalPages() ?>
    </p>
</section>

<!-- Combined Example -->
<section>
    <h2>All Components Together</h2>
    <p>This section shows all Phase 2 components working together.</p>
    
    <div style="display: flex; gap: 10px; align-items: center; margin: 20px 0;">
        <button data-toggle="dropdown">
            File ▼
        </button>
        <ul>
            <li><button onclick="alert('New file')">New</button></li>
            <li><button onclick="alert('Open file')">Open</button></li>
            <li><button onclick="alert('Save file')">Save</button></li>
        </ul>
        
        <button data-toggle="dropdown">
            Edit ▼
        </button>
        <ul>
            <li><button onclick="alert('Cut')">Cut</button></li>
            <li><button onclick="alert('Copy')">Copy</button></li>
            <li><button onclick="alert('Paste')">Paste</button></li>
        </ul>
        
        <button data-toggle="dropdown">
            View ▼
        </button>
        <ul>
            <li><button onclick="alert('Zoom in')">Zoom In</button></li>
            <li><button onclick="alert('Zoom out')">Zoom Out</button></li>
            <li><button onclick="alert('Fullscreen')">Fullscreen</button></li>
        </ul>
    </div>
</section>

<?= FlowUI::end() ?>

<hr style="margin: 40px 0;">
<p style="text-align: center; color: #666;">
    <a href="/examples/components.php">← Phase 1 Components</a> |
    <a href="/">Home</a> |
    <strong>Phase 2 Components</strong>
</p>

</body>
</html>
