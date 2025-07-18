<?php
/**
 * Session ID (SID) Example for Lab 9 - PHP Sessions
 * This file demonstrates different ways to get and work with Session IDs
 */

// Start the session first
session_start();

// Handle form submissions for session testing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_session_data'])) {
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['favorite_color'] = $_POST['favorite_color'];
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        $message = "✅ Session data has been set!";
    }

    if (isset($_POST['clear_session'])) {
        session_destroy();
        session_start(); // Restart session after destroying
        $message = "✅ Session has been cleared and regenerated!";
    }

    if (isset($_POST['regenerate_id'])) {
        session_regenerate_id(true);
        $message = "✅ Session ID has been regenerated for security!";
    }
}

$message = isset($message) ? $message : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session ID (SID) Example - Lab 9</title>
    <!-- CSS modules for session ID example page -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/utilities.css">
    <style>
        .sid-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .sid-info {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 14px;
        }

        .sid-method {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }

        .method-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .code-example {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 3px;
            padding: 10px;
            font-family: monospace;
            margin: 10px 0;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .session-data {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .form-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 10px 0;
        }

        .form-row input,
        .form-row select {
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .highlight {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>🔐 Session ID (SID) Example - Lab 9</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="../auth/index.php">Back to Login</a></li>
                <li><a href="simple_cookie_example.php">Cookie Example</a></li>
                <li><a href="cookie_demo.php">Advanced Cookie Demo</a></li>
                <li><a href="../../first_page.php">Basic Session Example</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">

        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Current Session Information -->
        <div class="sid-section">
            <h2>📋 Current Session Information</h2>

            <div class="sid-info">
                <strong>Session Status:</strong>
                <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?><br>
                <strong>Session Name:</strong> <?php echo session_name(); ?><br>
                <strong>Current Session ID:</strong> <span class="highlight"><?php echo session_id(); ?></span>
            </div>
        </div>

        <!-- Different Ways to Get Session ID -->
        <div class="sid-section">
            <h2>🔧 Different Ways to Get Session ID</h2>

            <div class="sid-method">
                <div class="method-title">Method 1: Using session_id() function</div>
                <div class="code-example">
                    $session_id = session_id();<br>
                    echo $session_id;
                </div>
                <div><strong>Result:</strong> <?php echo session_id(); ?></div>
                <div><em>This returns just the session ID value</em></div>
            </div>

            <div class="sid-method">
                <div class="method-title">Method 2: Using SID constant</div>
                <div class="code-example">
                    $sid = SID;<br>
                    echo $sid;
                </div>
                <div><strong>Result:</strong> <?php echo SID; ?></div>
                <div><em>This returns session_name=session_id format (useful for URLs)</em></div>
            </div>

            <div class="sid-method">
                <div class="method-title">Method 3: Building SID manually</div>
                <div class="code-example">
                    $manual_sid = session_name() . '=' . session_id();<br>
                    echo $manual_sid;
                </div>
                <div><strong>Result:</strong> <?php echo session_name() . '=' . session_id(); ?></div>
                <div><em>This creates the same format as SID constant</em></div>
            </div>
        </div>

        <!-- Session ID Comparison Table -->
        <div class="sid-section">
            <h2>📊 Session ID Methods Comparison</h2>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Function/Constant</th>
                        <th>Output Format</th>
                        <th>Use Case</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>session_id()</td>
                        <td>Function</td>
                        <td><?php echo session_id(); ?></td>
                        <td>When you need just the ID value</td>
                    </tr>
                    <tr>
                        <td>SID</td>
                        <td>Constant</td>
                        <td><?php echo SID ? SID : 'Empty (cookies enabled)'; ?></td>
                        <td>For URL parameters when cookies disabled</td>
                    </tr>
                    <tr>
                        <td>session_name()</td>
                        <td>Function</td>
                        <td><?php echo session_name(); ?></td>
                        <td>To get the session variable name</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Current Session Data -->
        <div class="sid-section">
            <h2>💾 Current Session Data</h2>

            <?php if (empty($_SESSION)): ?>
                <div class="session-data">
                    <p><em>No session data is currently stored. Use the form below to add some data.</em></p>
                </div>
            <?php else: ?>
                <div class="session-data">
                    <h4>Stored Session Variables:</h4>
                    <?php foreach ($_SESSION as $key => $value): ?>
                        <div><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Session Management Forms -->
        <div class="sid-section">
            <h2>🛠️ Session Management</h2>

            <!-- Set Session Data -->
            <h3>Set Session Data</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="username" placeholder="Username" required>
                    <select name="favorite_color" required>
                        <option value="">Select Color</option>
                        <option value="Red">Red</option>
                        <option value="Blue">Blue</option>
                        <option value="Green">Green</option>
                        <option value="Purple">Purple</option>
                    </select>
                    <button type="submit" name="set_session_data" class="btn">Set Session Data</button>
                </div>
            </form>

            <!-- Session Actions -->
            <h3>Session Actions</h3>
            <form method="POST" style="display: inline;">
                <button type="submit" name="regenerate_id" class="btn"
                    onclick="return confirm('This will create a new session ID for security. Continue?')">
                    Regenerate Session ID
                </button>
            </form>

            <form method="POST" style="display: inline; margin-left: 10px;">
                <button type="submit" name="clear_session" class="btn btn-danger"
                    onclick="return confirm('This will clear all session data. Continue?')">
                    Clear Session
                </button>
            </form>
        </div>

        <!-- SID Usage Examples -->
        <div class="sid-section">
            <h2>🌐 Practical SID Usage Examples</h2>

            <div class="sid-method">
                <div class="method-title">1. Adding SID to URLs (when cookies are disabled)</div>
                <div class="code-example">
                    $url = 'page.php?' . SID;<br>
                    // Result: page.php?<?php echo session_name(); ?>=<?php echo session_id(); ?>
                </div>
                <div><strong>Example URL:</strong> <a href="?<?php echo SID; ?>">current_page.php?<?php echo SID; ?></a>
                </div>
            </div>

            <div class="sid-method">
                <div class="method-title">2. Checking if session ID exists</div>
                <div class="code-example">
                    if (session_id()) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;echo "Session is active";<br>
                    } else {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;echo "No session found";<br>
                    }
                </div>
                <div><strong>Current Status:</strong>
                    <?php echo session_id() ? 'Session is active' : 'No session found'; ?></div>
            </div>

            <div class="sid-method">
                <div class="method-title">3. Session security check</div>
                <div class="code-example">
                    // Check if session ID is valid format<br>
                    if (preg_match('/^[a-zA-Z0-9,-]{22,250}$/', session_id())) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;echo "Valid session ID format";<br>
                    }
                </div>
                <div><strong>Current ID Validation:</strong>
                    <?php echo preg_match('/^[a-zA-Z0-9,-]{22,250}$/', session_id()) ? 'Valid format' : 'Invalid format'; ?>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="sid-section">
            <h2>⚠️ Important Notes About Session IDs</h2>
            <ul>
                <li><strong>Security:</strong> Session IDs should be unpredictable and long enough to prevent guessing
                </li>
                <li><strong>Regeneration:</strong> Regenerate session IDs after login for security (prevents session
                    fixation)</li>
                <li><strong>SID Constant:</strong> SID is empty when cookies are enabled (which is the default)</li>
                <li><strong>URL Parameters:</strong> Use SID in URLs only when cookies are disabled</li>
                <li><strong>Session Start:</strong> Always call session_start() before using session functions</li>
                <li><strong>HTTPS:</strong> Use HTTPS in production to prevent session ID theft</li>
            </ul>
        </div>

        <!-- Quick Test Links -->
        <div class="sid-section">
            <h2>🧪 Quick Test Links</h2>
            <p>Test how SID works in URLs:</p>
            <ul>
                <li><a href="session_id_example.php?<?php echo SID; ?>">Reload this page with SID parameter</a></li>
                <li><a href="../../first_page.php">Go to Basic Session Example</a></li>
                <li><a href="../../next_page.php">Go to Session Retrieval Example</a></li>
            </ul>
        </div>

    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Session ID Example - Lab 9 PHP Exercise</p>
        </div>
    </footer>
</body>

</html>