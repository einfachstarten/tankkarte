<?php
// Simple password protection
$password = 'filo2025!'; // Change this!
session_start();

if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
    } else {
        ?>
        <form method="post">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php
        exit;
    }
}

$logType = $_GET['type'] ?? 'submissions';
$logFile = __DIR__ . '/logs/contact_' . $logType . '.log';

if (!file_exists($logFile)) {
    echo "Log file not found: " . $logType;
    exit;
}

echo "<h2>Contact Form Logs - " . ucfirst($logType) . "</h2>";
echo "<a href='?type=submissions'>Submissions</a> | ";
echo "<a href='?type=errors'>Errors</a> | ";
echo "<a href='?type=spam'>Spam</a><br><br>";

$lines = file($logFile);
$lines = array_reverse($lines); // Show newest first

echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
echo "<tr><th>Time</th><th>IP</th><th>Message</th><th>Details</th></tr>";

foreach (array_slice($lines, 0, 50) as $line) { // Show last 50 entries
    $data = json_decode($line, true);
    if ($data) {
        echo "<tr>";
        echo "<td>" . $data['timestamp'] . "</td>";
        echo "<td>" . $data['ip'] . "</td>";
        echo "<td>" . htmlspecialchars($data['message']) . "</td>";
        echo "<td><pre>" . htmlspecialchars(json_encode($data['data'], JSON_PRETTY_PRINT)) . "</pre></td>";
        echo "</tr>";
    }
}
echo "</table>";
?>
