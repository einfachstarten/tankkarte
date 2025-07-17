<?php
session_start();

$adminPassword = 'FiloCards2025!Admin';
$configFile = '../data/feature-flags.json';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Login handling
if (isset($_POST['password'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Falsches Passwort';
    }
}

// Save changes
if (isset($_POST['save_config']) && $_SESSION['admin_logged_in']) {
    $config = json_decode($_POST['config'], true);
    if ($config) {
        // Backup old config
        $backup = file_get_contents($configFile);
        file_put_contents($configFile . '.backup.' . time(), $backup);
        
        // Save new config
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        $success = 'Konfiguration gespeichert!';
    } else {
        $error = 'Ungültiges JSON Format';
    }
}

// Load current config
$currentConfig = json_decode(file_get_contents($configFile), true);

if (!isset($_SESSION['admin_logged_in'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>filo.cards Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #f5f5f5; }
        .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="password"] { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .btn { width: 100%; padding: 0.75rem; background: #1B5E20; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0D2F0F; }
        .error { color: red; margin-top: 0.5rem; }
        h1 { text-align: center; color: #1B5E20; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>filo.cards Admin</h1>
        <form method="post">
            <div class="form-group">
                <label for="password">Admin-Passwort</label>
                <input type="password" name="password" required>
                <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            </div>
            <button type="submit" class="btn">Anmelden</button>
        </form>
    </div>
</body>
</html>
<?php
exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>filo.cards Admin Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #1B5E20; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .feature-toggle { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 1rem; }
        .toggle { position: relative; width: 60px; height: 30px; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 30px; }
        .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #1B5E20; }
        input:checked + .slider:before { transform: translateX(30px); }
        .btn { padding: 0.75rem 1.5rem; background: #1B5E20; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 1rem; }
        .btn:hover { background: #0D2F0F; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .success { color: green; margin-bottom: 1rem; }
        .error { color: red; margin-bottom: 1rem; }
        .config-section { margin-top: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], input[type="tel"] { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>filo.cards Admin Panel</h1>
        <a href="?logout=1" class="btn btn-danger">Abmelden</a>
    </div>

    <div class="container">
        <?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="post" id="configForm">
            <!-- WhatsApp Integration -->
            <div class="card">
                <h2>WhatsApp Integration</h2>
                <div class="feature-toggle">
                    <div>
                        <strong>WhatsApp Button</strong>
                        <br><small>Floating WhatsApp Button auf der Website</small>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="whatsapp_enabled" <?php echo $currentConfig['features']['whatsapp_integration']['enabled'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="config-section">
                    <div class="form-group">
                        <label>Telefonnummer</label>
                        <input type="tel" id="whatsapp_phone" value="<?php echo $currentConfig['features']['whatsapp_integration']['phone_number']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Button-Position</label>
                        <select id="whatsapp_position">
                            <option value="bottom-right" <?php echo $currentConfig['features']['whatsapp_integration']['position'] == 'bottom-right' ? 'selected' : ''; ?>>Unten Rechts</option>
                            <option value="bottom-left" <?php echo $currentConfig['features']['whatsapp_integration']['position'] == 'bottom-left' ? 'selected' : ''; ?>>Unten Links</option>
                        </select>
                    </div>
                    <div class="feature-toggle">
                        <div>
                            <strong>Mobile anzeigen</strong>
                            <br><small>Button auch auf mobilen Geräten zeigen</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="whatsapp_mobile" <?php echo $currentConfig['features']['whatsapp_integration']['show_on_mobile'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="feature-toggle">
                        <div>
                            <strong>Notifications</strong>
                            <br><small>WhatsApp-Benachrichtigungen nach CTA-Klicks</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="whatsapp_notifications" <?php echo $currentConfig['features']['whatsapp_integration']['show_notifications'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Future Features -->
            <div class="card">
                <h2>Zukünftige Features</h2>
                <div class="feature-toggle">
                    <div>
                        <strong>Chat Widget</strong>
                        <br><small>Alternatives Chat-System (z.B. Tawk.to)</small>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="chat_widget_enabled" <?php echo $currentConfig['features']['chat_widget']['enabled'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <input type="hidden" name="config" id="configData">
            <button type="submit" name="save_config" class="btn">Konfiguration speichern</button>
        </form>
    </div>

    <script>
        document.getElementById('configForm').addEventListener('submit', function(e) {
            // Build config object from form
            const config = {
                features: {
                    whatsapp_integration: {
                        enabled: document.getElementById('whatsapp_enabled').checked,
                        phone_number: document.getElementById('whatsapp_phone').value,
                        display_name: "WhatsApp",
                        position: document.getElementById('whatsapp_position').value,
                        show_on_mobile: document.getElementById('whatsapp_mobile').checked,
                        show_notifications: document.getElementById('whatsapp_notifications').checked
                    },
                    chat_widget: {
                        enabled: document.getElementById('chat_widget_enabled').checked,
                        provider: "tawk",
                        widget_id: ""
                    }
                }
            };
            
            document.getElementById('configData').value = JSON.stringify(config);
        });
    </script>
</body>
</html>
