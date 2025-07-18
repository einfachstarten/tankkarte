<?php
// SEO Pages Generator für filo.cards - Server Error Resilient Version
ini_set('display_errors', 0);  // Suppress display of warnings
error_reporting(E_ERROR | E_PARSE);  // Only fatal errors, ignore warnings
@ini_set('imagick.skip_version_check', 1);

require_once 'translations-loader.php';

class SEOPageGenerator {
    private $translations;
    private $languages = ['de', 'tr', 'en'];
    private $outputDir = 'seo/';
    private $errors = [];
    
    public function __construct() {
        try {
            // Suppress any ImageMagick startup warnings
            @ini_set('display_errors', 0);

            $translationsFile = 'data/translations.json';
            if (!file_exists($translationsFile)) {
                throw new Exception("Translations file not found: $translationsFile");
            }

            $jsonContent = file_get_contents($translationsFile);
            if ($jsonContent === false) {
                throw new Exception("Could not read translations file");
            }

            $decodedJson = json_decode($jsonContent, true);
            if ($decodedJson === null) {
                throw new Exception("Invalid JSON in translations file");
            }

            $this->translations = $decodedJson['translations'];

            if (!is_dir($this->outputDir)) {
                if (!mkdir($this->outputDir, 0755, true)) {
                    throw new Exception("Could not create output directory: $this->outputDir");
                }
            }

            echo "SEO Generator initialized successfully\n";

        } catch (Exception $e) {
            $this->errors[] = "Initialization failed: " . $e->getMessage();
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    public function generateAllPages() {
        if (!empty($this->errors)) {
            echo "Cannot generate pages due to initialization errors\n";
            return false;
        }

        try {
            $keywords = $this->getKeywordMapping();
            $generatedCount = 0;

            foreach ($keywords as $keyword => $config) {
                foreach ($this->languages as $lang) {
                    if ($this->generatePage($keyword, $lang, $config)) {
                        $generatedCount++;
                    }
                }
            }

            $this->generateSitemap();

            echo "Successfully generated $generatedCount SEO pages\n";
            return true;

        } catch (Exception $e) {
            echo "ERROR: Failed to generate pages: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function getKeywordMapping() {
        return [
            'tankkarte-europa' => [
                'de' => ['title' => 'Tankkarte Europa - Günstiger Diesel für LKW Flotten', 'focus' => 'fuelcard'],
                'tr' => ['title' => 'Avrupa Yakıt Kartı - Ucuz Dizel TIR Filolar', 'focus' => 'fuelcard'],
                'en' => ['title' => 'Europe Fuel Card - Cheap Diesel for Truck Fleets', 'focus' => 'fuelcard']
            ],
            'maut-europa-lkw' => [
                'de' => ['title' => 'LKW Maut Europa - EETS Mautbox für alle Länder', 'focus' => 'toll'],
                'tr' => ['title' => 'Avrupa TIR Geçiş Üreti - Tek Cihazla Tüm Ülkeler', 'focus' => 'toll'],
                'en' => ['title' => 'European Truck Toll - EETS Box for All Countries', 'focus' => 'toll']
            ],
            'prepaid-kreditkarte-flotte' => [
                'de' => ['title' => 'Prepaid Kreditkarte Flotte - Ohne Schufa für LKW Fahrer', 'focus' => 'creditcard'],
                'tr' => ['title' => 'Filo Ön Ödemeli Kartı - Kredi Kontrolsüz TIR Şoförleri', 'focus' => 'creditcard'],
                'en' => ['title' => 'Fleet Prepaid Credit Card - No Credit Check for Drivers', 'focus' => 'creditcard']
            ],
            'flottenmanagement-telematik' => [
                'de' => ['title' => 'Flottenmanagement Telematik - GPS Fahrzeugortung Europa', 'focus' => 'telematics'],
                'tr' => ['title' => 'Filo Yönetimi Telematik - GPS Araç Takip Avrupa', 'focus' => 'telematics'],
                'en' => ['title' => 'Fleet Management Telematics - GPS Vehicle Tracking Europe', 'focus' => 'telematics']
            ]
        ];
    }
    
    private function generatePage($keyword, $lang, $config) {
        try {
            $filename = "{$this->outputDir}{$keyword}-{$lang}.html";
            $pageConfig = $config[$lang];
            $content = $this->buildPageContent($keyword, $lang, $pageConfig);

            $writeResult = file_put_contents($filename, $content);
            if ($writeResult === false) {
                throw new Exception("Could not write file: $filename");
            }

            echo "Generated: $filename\n";
            return true;

        } catch (Exception $e) {
            echo "ERROR generating $keyword-$lang: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function buildPageContent($keyword, $lang, $config) {
        $t = $this->translations[$lang];
        $focus = $config['focus'];
        
        $metaDescription = $this->generateMetaDescription($focus, $lang);
        $structuredData = $this->generateStructuredData($config['title'], $metaDescription);
        $contentHtml = $this->generateMainContent($focus, $lang);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$config['title']}</title>
    <meta name="description" content="{$metaDescription}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://www.filo.cards/seo/{$keyword}-{$lang}.html">
    
    <!-- Redirect nach 3 Sekunden -->
    <meta http-equiv="refresh" content="3;url=https://www.filo.cards/#{$focus}">
    
    <!-- Basic Styling -->
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .redirect-notice { background: #1B5E20; color: white; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .content { margin-bottom: 40px; }
        h1 { color: #1B5E20; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .feature { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
    </style>
    
    {$structuredData}
</head>
<body>
    <div class="redirect-notice">
        ⏱️ {$this->getRedirectText($lang)} <a href="https://www.filo.cards/#{$focus}" style="color: #FFD600;">{$this->getRedirectLinkText($lang)}</a>
    </div>
    
    <main class="content">
        <h1>{$config['title']}</h1>
        {$contentHtml}
    </main>
    
    <!-- Sofortiger Redirect für JavaScript-Nutzer -->
    <script>
        window.location.href = 'https://www.filo.cards/#{$focus}';
    </script>
</body>
</html>
HTML;
    }
    
    private function generateMainContent($focus, $lang) {
        $t = $this->translations[$lang];
        
        switch($focus) {
            case 'fuelcard':
                return $this->generateFuelCardContent($lang, $t);
            case 'toll':
                return $this->generateTollContent($lang, $t);
            case 'creditcard':
                return $this->generateCreditCardContent($lang, $t);
            case 'telematics':
                return $this->generateTelematicsContent($lang, $t);
            default:
                return $this->generateGeneralContent($lang, $t);
        }
    }
    
    private function generateFuelCardContent($lang, $t) {
        $countries = implode(', ', array_slice($t['fuelcard']['countries']['list'], 0, 10));
        
        return <<<HTML
        <p>{$t['fuelcard']['subtitle']}</p>
        
        <div class="features">
            <div class="feature">
                <h3>{$t['fuelcard']['benefit1']['title']}</h3>
                <p>{$t['fuelcard']['benefit1']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['fuelcard']['benefit2']['title']}</h3>
                <p>{$t['fuelcard']['benefit2']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['fuelcard']['benefit3']['title']}</h3>
                <p>{$t['fuelcard']['benefit3']['description']}</p>
            </div>
        </div>
        
        <h2>{$t['fuelcard']['countries']['title']}</h2>
        <p>{$countries}</p>
        
        <div style="background: #FFD600; padding: 20px; border-radius: 8px; margin: 30px 0;">
            <strong>{$t['fuelcard']['countries']['austriaSpecial']}</strong>
        </div>
        
        <h2>{$t['contact']['title']}</h2>
        <p>📞 +90 5530540989</p>
        <p>📧 o.gokceviran@rmc-service.com</p>
HTML;
    }
    
    private function generateTollContent($lang, $t) {
        $countries = implode(', ', array_slice($t['toll']['countries']['list'], 0, 8));
        
        return <<<HTML
        <p>{$t['toll']['subtitle']}</p>
        
        <div class="features">
            <div class="feature">
                <h3>{$t['toll']['advantages']['oneProvider']['title']}</h3>
                <p>{$t['toll']['advantages']['oneProvider']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['toll']['advantages']['eets']['title']}</h3>
                <p>{$t['toll']['advantages']['eets']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['toll']['advantages']['billing']['title']}</h3>
                <p>{$t['toll']['advantages']['billing']['description']}</p>
            </div>
        </div>
        
        <h2>{$t['toll']['countries']['title']}</h2>
        <p>{$countries}</p>
        
        <h2>{$t['toll']['tunnel']['title']}</h2>
        <p>{$t['toll']['tunnel']['description']}</p>
HTML;
    }
    
    private function generateCreditCardContent($lang, $t) {
        return <<<HTML
        <p>{$t['creditcard']['subtitle']}</p>
        
        <h2>{$t['creditcard']['usage']['title']}</h2>
        <div class="features">
            <div class="feature">
                <h3>{$t['creditcard']['usage']['hotels']['title']}</h3>
                <p>{$t['creditcard']['usage']['hotels']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['creditcard']['usage']['parking']['title']}</h3>
                <p>{$t['creditcard']['usage']['parking']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['creditcard']['usage']['emergencies']['title']}</h3>
                <p>{$t['creditcard']['usage']['emergencies']['description']}</p>
            </div>
        </div>
        
        <h2>{$t['creditcard']['benefits']['title']}</h2>
        <div class="features">
            <div class="feature">
                <h3>{$t['creditcard']['benefits']['noCredit']['title']}</h3>
                <p>{$t['creditcard']['benefits']['noCredit']['description']}</p>
            </div>
            <div class="feature">
                <h3>{$t['creditcard']['benefits']['control']['title']}</h3>
                <p>{$t['creditcard']['benefits']['control']['description']}</p>
            </div>
        </div>
HTML;
    }
    
    private function generateTelematicsContent($lang, $t) {
        return <<<HTML
        <h2>KITIN Telematik System</h2>
        <p>Modernes Flottenmanagement mit Echtzeit-GPS-Tracking, Fahrerverhalten-Analyse und Kraftstoffoptimierung.</p>
        
        <div class="features">
            <div class="feature">
                <h3>GPS Fahrzeugortung</h3>
                <p>Echtzeitüberwachung Ihrer Fahrzeugflotte mit präziser Standortbestimmung und Routenoptimierung.</p>
            </div>
            <div class="feature">
                <h3>Treibstoffüberwachung</h3>
                <p>Verhindern Sie Kraftstoffdiebstahl und optimieren Sie den Verbrauch durch detaillierte Analysen.</p>
            </div>
            <div class="feature">
                <h3>Fahrerverhalten</h3>
                <p>Analysieren Sie Fahrweise, Geschwindigkeit und Effizienz für bessere Kostenkontrolle.</p>
            </div>
        </div>
        
        <h2>Integration mit RMC Services</h2>
        <p>Vollständige Integration mit Tankkarten und Mautlösungen für komplettes Flottenmanagement aus einer Hand.</p>
HTML;
    }

    private function generateGeneralContent($lang, $t) {
        $desc = $t['page']['description'] ?? '';
        return "<p>{$desc}</p>";
    }
    
    private function generateStructuredData($title, $description) {
        return <<<JSON
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "{$title}",
        "description": "{$description}",
        "url": "https://www.filo.cards/",
        "mainEntity": {
            "@type": "Organization",
            "name": "filo.cards - RMC Service GmbH",
            "url": "https://www.filo.cards",
            "telephone": "+90 5530540989",
            "email": "o.gokceviran@rmc-service.com"
        }
    }
    </script>
JSON;
    }
    
    private function generateSitemap() {
        $urls = [];
        $keywords = array_keys($this->getKeywordMapping());
        
        foreach ($keywords as $keyword) {
            foreach ($this->languages as $lang) {
                $urls[] = "https://www.filo.cards/seo/{$keyword}-{$lang}.html";
            }
        }
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $sitemap .= "  <url>\n";
            $sitemap .= "    <loc>{$url}</loc>\n";
            $sitemap .= "    <changefreq>monthly</changefreq>\n";
            $sitemap .= "    <priority>0.8</priority>\n";
            $sitemap .= "  </url>\n";
        }
        
        $sitemap .= '</urlset>';
        
        file_put_contents($this->outputDir . 'sitemap-seo.xml', $sitemap);
    }
    
    private function getRedirectText($lang) {
        $texts = [
            'de' => 'Sie werden automatisch zur Hauptseite weitergeleitet.',
            'tr' => 'Ana sayfaya otomatik olarak yönlendiriliyorsunuz.',
            'en' => 'You are being automatically redirected to the main page.'
        ];
        return $texts[$lang];
    }
    
    private function getRedirectLinkText($lang) {
        $texts = [
            'de' => 'Direkt zur Seite',
            'tr' => 'Doğrudan sayfaya git',
            'en' => 'Go directly to page'
        ];
        return $texts[$lang];
    }
    
    private function generateMetaDescription($focus, $lang) {
        $descriptions = [
            'de' => [
                'fuelcard' => 'Europäische Tankkarte für Unternehmen. Günstiger Diesel, 19 Länder, keine Gebühren. Jetzt kostenlos anfragen!',
                'toll' => 'LKW Maut Europa aus einer Hand. EETS-kompatibel für 17 Länder. Einfache Abrechnung für Speditionen.',
                'creditcard' => 'Prepaid Firmenkreditkarte ohne Schufa. Perfekt für LKW-Fahrer. Volle Kostenkontrolle.',
                'telematics' => 'Professionelles Flottenmanagement mit GPS-Tracking. KITIN Telematik für Europa.'
            ],
            'tr' => [
                'fuelcard' => 'Kurumsal yakıt kartı Avrupa. Ucuz dizel, 19 ülke, ücretsiz. Hemen başvurun!',
                'toll' => 'Avrupa TIR geçiş ücreti tek yerden. 17 ülke EETS uyumlu. Basit faturalandırma.',
                'creditcard' => 'Kredi kontrolsüz ön ödemeli kart. TIR şoförleri için ideal. Tam maliyet kontrolü.',
                'telematics' => 'GPS takipli profesyonel filo yönetimi. KITIN telematik Avrupa çözümü.'
            ],
            'en' => [
                'fuelcard' => 'European fuel card for businesses. Cheap diesel, 19 countries, no fees. Apply now!',
                'toll' => 'European truck toll solution. EETS compatible for 17 countries. Simple billing.',
                'creditcard' => 'Prepaid business credit card without credit check. Perfect for truck drivers.',
                'telematics' => 'Professional fleet management with GPS tracking. KITIN telematics for Europe.'
            ]
        ];
        
        return $descriptions[$lang][$focus] ?? $descriptions[$lang]['fuelcard'];
    }
}

// Generator ausführen
$generator = null;

try {
    $generator = new SEOPageGenerator();
    $success = $generator->generateAllPages();

    if ($success) {
        echo "SEO Pages generated successfully!\n";
        exit(0);  // Success exit code
    } else {
        echo "SEO Page generation completed with errors!\n";
        exit(1);  // Error exit code
    }

} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
