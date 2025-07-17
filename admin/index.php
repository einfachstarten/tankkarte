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

// Fallback-Werte definieren (aktuelle Website-Inhalte)
$defaultValues = [
    'content_management' => [
        'enabled' => false,
        'contact_email' => 'o.gokceviran@rmc-service.com',
        'titles' => [
            'hero_main' => 'Europäische Mobilitätslösungen | Tankkarte, Maut & Kreditkarte',
            'services_main' => 'Unsere Mobilitätslösungen',
            'fuelcard_main' => 'RMC Tankkarte',
            'creditcard_main' => 'RMC Prepaid Kreditkarte',
            'toll_main' => 'Mautlösungen für Europa',
            'contact_main' => 'Kontaktieren Sie uns'
        ],
        'external_urls' => [
            'station_finder_web' => 'https://finder.rmc-service.com/rmc/map',
            'station_finder_android' => 'https://play.google.com/store/apps/details?id=at.rmc.app',
            'station_finder_ios' => 'https://apps.apple.com/at/app/rmc-finder/id6477531013',
            'rmc_info_de' => 'https://www.rmc-service.com/de/tankstellenfinder/tankstellennetz',
            'rmc_info_en' => 'https://www.rmc-service.com/en/station-finder/station-network',
            'rmc_info_tr' => 'https://www.rmc-service.com/tr/istasyon-bulucu/istasyon-agi'
        ]
    ]
];

// Merge defaults mit aktueller Config
function mergeWithDefaults($currentConfig, $defaults) {
    foreach ($defaults as $feature => $defaultConfig) {
        if (!isset($currentConfig['features'][$feature])) {
            $currentConfig['features'][$feature] = $defaultConfig;
        } else {
            $currentConfig['features'][$feature] = array_merge_recursive(
                $defaultConfig,
                $currentConfig['features'][$feature]
            );
        }
    }
    return $currentConfig;
}

// Nach dem Config-Laden:
$currentConfig = mergeWithDefaults($currentConfig, $defaultValues);

// Helper function für sichere Werte
function getConfigValue($config, $path, $default = '') {
    $keys = explode('.', $path);
    $value = $config;
    foreach ($keys as $key) {
        if (isset($value[$key])) {
            $value = $value[$key];
        } else {
            return $default;
        }
    }
    return $value;
}

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

            <div class="card">
                <h2>Content Management</h2>
                <div class="feature-toggle">
                    <div>
                        <strong>Content Management</strong>
                        <br><small>Titel und Links über Admin Panel verwalten</small>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="content_enabled" <?php echo $currentConfig['features']['content_management']['enabled'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="config-section">
                    <h3>Kontakt</h3>
                    <div class="form-group">
                        <label>E-Mail-Adresse</label>
                        <input type="email" id="contact_email" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.contact_email', 'o.gokceviran@rmc-service.com')); ?>">
                    </div>

                    <h3>Haupttitel</h3>
                    <div class="form-group">
                        <label>Hero Titel</label>
                        <input type="text" id="hero_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.hero_main', 'Europäische Mobilitätslösungen | Tankkarte, Maut & Kreditkarte')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Services Titel</label>
                        <input type="text" id="services_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.services_main', 'Unsere Mobilitätslösungen')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Tankkarte Titel</label>
                        <input type="text" id="fuelcard_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.fuelcard_main', 'RMC Tankkarte')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Kreditkarte Titel</label>
                        <input type="text" id="creditcard_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.creditcard_main', 'RMC Prepaid Kreditkarte')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Maut Titel</label>
                        <input type="text" id="toll_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.toll_main', 'Mautlösungen für Europa')); ?>">
                    </div>
                    <div class="form-group">
                        <label>Kontakt Titel</label>
                        <input type="text" id="contact_title" value="<?php echo htmlspecialchars(getConfigValue($currentConfig, 'features.content_management.titles.contact_main', 'Kontaktieren Sie uns')); ?>">
                    </div>

                    <h3>Externe Links</h3>
                    <div class="form-group">
                        <label>Station Finder Web</label>
                        <input type="url" id="finder_web" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.station_finder_web', 'https://finder.rmc-service.com/rmc/map'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Android App</label>
                        <input type="url" id="finder_android" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.station_finder_android', 'https://play.google.com/store/apps/details?id=at.rmc.app'); ?>">
                    </div>
                    <div class="form-group">
                        <label>iOS App</label>
                        <input type="url" id="finder_ios" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.station_finder_ios', 'https://apps.apple.com/at/app/rmc-finder/id6477531013'); ?>">
                    </div>
                    <div class="form-group">
                        <label>RMC Info DE</label>
                        <input type="url" id="rmc_info_de" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.rmc_info_de', 'https://www.rmc-service.com/de/tankstellenfinder/tankstellennetz'); ?>">
                    </div>
                    <div class="form-group">
                        <label>RMC Info EN</label>
                        <input type="url" id="rmc_info_en" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.rmc_info_en', 'https://www.rmc-service.com/en/station-finder/station-network'); ?>">
                    </div>
                <div class="form-group">
                        <label>RMC Info TR</label>
                        <input type="url" id="rmc_info_tr" value="<?php echo getConfigValue($currentConfig, 'features.content_management.external_urls.rmc_info_tr', 'https://www.rmc-service.com/tr/istasyon-bulucu/istasyon-agi'); ?>">
                    </div>
                </div>
            </div>

            <!-- SEO & Analytics Section -->
            <div class="card">
                <h2>SEO & Analytics</h2>
                <div class="feature-toggle">
                    <div>
                        <strong>SEO & Analytics</strong>
                        <br><small>Google Analytics, Structured Data, Social Media Optimization</small>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" id="seo_enabled" <?php echo $currentConfig['features']['seo_analytics']['enabled'] ? 'checked' : ''; ?> >
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="config-section">
                    <h3>Google Analytics</h3>
                    <div class="feature-toggle">
                        <div>
                            <strong>Google Analytics 4</strong>
                            <br><small>Website Tracking und Conversion Measurement</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="ga_enabled" <?php echo $currentConfig['features']['seo_analytics']['google_analytics']['enabled'] ? 'checked' : ''; ?> >
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Google Analytics Tracking ID</label>
                        <input type="text" id="ga_tracking_id" value="<?php echo $currentConfig['features']['seo_analytics']['google_analytics']['tracking_id']; ?>" placeholder="G-XXXXXXXXXX">
                    </div>

                    <h3>Structured Data</h3>
                    <div class="feature-toggle">
                        <div>
                            <strong>Schema.org Markup</strong>
                            <br><small>Bessere Google Search Results</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="structured_enabled" <?php echo $currentConfig['features']['seo_analytics']['structured_data']['enabled'] ? 'checked' : ''; ?> >
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Organisation Name</label>
                        <input type="text" id="org_name" value="<?php echo $currentConfig['features']['seo_analytics']['structured_data']['organization_name']; ?>">
                    </div>

                    <h3>Social Media</h3>
                    <div class="feature-toggle">
                        <div>
                            <strong>Open Graph & Twitter Cards</strong>
                            <br><small>Bessere Social Media Previews</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="social_enabled" <?php echo $currentConfig['features']['seo_analytics']['social_media']['enabled'] ? 'checked' : ''; ?> >
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Open Graph Image</label>
                        <input type="text" id="og_image" value="<?php echo $currentConfig['features']['seo_analytics']['social_media']['og_image']; ?>">
                    </div>

                    <h3>Performance</h3>
                    <div class="feature-toggle">
                        <div>
                            <strong>Lazy Loading</strong>
                            <br><small>Images on demand für bessere Performance</small>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="lazy_loading" <?php echo $currentConfig['features']['seo_analytics']['performance']['lazy_loading'] ? 'checked' : ''; ?> >
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
                    },
                    content_management: {
                        enabled: document.getElementById('content_enabled').checked,
                        contact_email: document.getElementById('contact_email').value,
                        titles: {
                            hero_main: document.getElementById('hero_title').value,
                            services_main: document.getElementById('services_title').value,
                            fuelcard_main: document.getElementById('fuelcard_title').value,
                            creditcard_main: document.getElementById('creditcard_title').value,
                            toll_main: document.getElementById('toll_title').value,
                            contact_main: document.getElementById('contact_title').value
                        },
                        external_urls: {
                            station_finder_web: document.getElementById('finder_web').value,
                            station_finder_android: document.getElementById('finder_android').value,
                            station_finder_ios: document.getElementById('finder_ios').value,
                            rmc_info_de: document.getElementById('rmc_info_de').value,
                            rmc_info_en: document.getElementById('rmc_info_en').value,
                            rmc_info_tr: document.getElementById('rmc_info_tr').value
                        }
                    },
                    seo_analytics: {
                        enabled: document.getElementById('seo_enabled').checked,
                        google_analytics: {
                            enabled: document.getElementById('ga_enabled').checked,
                            tracking_id: document.getElementById('ga_tracking_id').value,
                            enhanced_ecommerce: false
                        },
                        structured_data: {
                            enabled: document.getElementById('structured_enabled').checked,
                            organization_name: document.getElementById('org_name').value,
                            contact_phone: "+90 5530540989",
                            service_area: ["Austria", "Germany", "Turkey", "Europe"]
                        },
                        social_media: {
                            enabled: document.getElementById('social_enabled').checked,
                            og_image: document.getElementById('og_image').value,
                            twitter_handle: "@filocards"
                        },
                        performance: {
                            lazy_loading: document.getElementById('lazy_loading').checked,
                            critical_css: true,
                            preload_fonts: true
                        }
                    }
                }
            };
            
            document.getElementById('configData').value = JSON.stringify(config);
        });
    </script>
</body>
</html>
