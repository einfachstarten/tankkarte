<?php
// SEO Pages Generator using native JSON parsing
ini_set('memory_limit', '256M');
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

class RobustSEOGenerator {
    private $translations;
    private $languages = ['de', 'tr', 'en'];
    private $outputDir = 'seo/';

    public function __construct() {
        try {
            $this->loadTranslations();
            echo "SEO Generator initialized successfully\n";
        } catch (Exception $e) {
            echo "Initialization failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function loadTranslations() {
        $translationsFile = 'data/translations.json';

        if (!file_exists($translationsFile)) {
            throw new Exception("Translations file not found: $translationsFile");
        }

        $jsonContent = file_get_contents($translationsFile);
        if ($jsonContent === false) {
            throw new Exception("Could not read translations file");
        }

        $decoded = json_decode($jsonContent, true);

        if ($decoded === null) {
            $error = json_last_error_msg();
            throw new Exception("JSON decode failed: $error");
        }

        if (!isset($decoded['translations'])) {
            throw new Exception("Invalid translations structure");
        }

        $this->translations = $decoded['translations'];

        if (!is_dir($this->outputDir)) {
            if (!mkdir($this->outputDir, 0755, true)) {
                throw new Exception("Could not create output directory: $this->outputDir");
            }
        }

        echo "Translations loaded successfully (native JSON)\n";
    }


    public function generateAllPages() {
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
                'de' => ['title' => 'Tankkarte Europa - G\xC3\xBCnstiger Diesel f\xC3\xBCr LKW Flotten', 'focus' => 'fuel-solutions'],
                'tr' => ['title' => 'Avrupa Yak\xC4\xB1t Kart\xC4\xB1 - Ucuz Dizel TIR Filolar', 'focus' => 'fuel-solutions'],
                'en' => ['title' => 'Europe Fuel Card - Cheap Diesel for Truck Fleets', 'focus' => 'fuel-solutions']
            ],
            'maut-europa-lkw' => [
                'de' => ['title' => 'LKW Maut Europa - EETS Mautbox f\xC3\xBCr alle L\xC3\xA4nder', 'focus' => 'payment-mobility'],
                'tr' => ['title' => 'Avrupa TIR Ge\xC3\xA7i\xC5\x9F \xC3\x9Ccreti - Tek Cihazla T\xC3\xBCm \xC3\x9Clkeler', 'focus' => 'payment-mobility'],
                'en' => ['title' => 'European Truck Toll - EETS Box for All Countries', 'focus' => 'payment-mobility']
            ],
            'prepaid-kreditkarte-flotte' => [
                'de' => ['title' => 'Prepaid Kreditkarte Flotte - Ohne Schufa f\xC3\xBCr LKW Fahrer', 'focus' => 'payment-mobility'],
                'tr' => ['title' => 'Filo \xC3\x96n \xC3\x96demeli Kart\xC4\xB1 - Kredi Kontrols\xC3\xBCz TIR \xC5\x9Eof\xC3\xB6rleri', 'focus' => 'payment-mobility'],
                'en' => ['title' => 'Fleet Prepaid Credit Card - No Credit Check for Drivers', 'focus' => 'payment-mobility']
            ],
            'flottenmanagement-telematik' => [
                'de' => ['title' => 'Flottenmanagement Telematik - GPS Fahrzeugortung Europa', 'focus' => 'services'],
                'tr' => ['title' => 'Filo Y\xC3\xB6netimi Telematik - GPS Ara\xC3\xA7 Takip Avrupa', 'focus' => 'services'],
                'en' => ['title' => 'Fleet Management Telematics - GPS Vehicle Tracking Europe', 'focus' => 'services']
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
        .highlight { background: #FFD600; padding: 20px; border-radius: 8px; margin: 30px 0; }
        .contact { margin-top: 40px; }
    </style>

    {$structuredData}
</head>
<body>
    <div class="redirect-notice">
        \xE2\x8F\xB1\xEF\xB8\x8F {$this->getRedirectText($lang)} <a href="https://www.filo.cards/#{$focus}" style="color: #FFD600;">{$this->getRedirectLinkText($lang)}</a>
    </div>

    <main class="content">
        <h1>{$config['title']}</h1>
        {$contentHtml}
    </main>

    <!-- Sofortiger Redirect f\xC3\xBCr JavaScript-Nutzer -->
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
            case 'fuel-solutions':
                return $this->generateFuelCardContent($lang, $t);
            case 'payment-mobility':
                if (strpos($focus, 'toll') !== false || strpos($focus, 'maut') !== false) {
                    return $this->generateTollContent($lang, $t);
                } else {
                    return $this->generateCreditCardContent($lang, $t);
                }
                break;
            case 'services':
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

        <div class="highlight">
            <strong>{$t['fuelcard']['countries']['austriaSpecial']}</strong>
        </div>

        <div class="contact">
            <h2>{$t['contact']['title']}</h2>
            <p>\xF0\x9F\x93\x9E +90 5530540989</p>
            <p>\xF0\x9F\x93\xA7 o.gokceviran@rmc-service.com</p>
        </div>
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
                <p>Echtzeit\xC3\xBCberwachung Ihrer Fahrzeugflotte mit pr\xC3\xA4ziser Standortbestimmung und Routenoptimierung.</p>
            </div>
            <div class="feature">
                <h3>Treibstoff\xC3\xBCberwachung</h3>
                <p>Verhindern Sie Kraftstoffdiebstahl und optimieren Sie den Verbrauch durch detaillierte Analysen.</p>
            </div>
            <div class="feature">
                <h3>Fahrerverhalten</h3>
                <p>Analysieren Sie Fahrweise, Geschwindigkeit und Effizienz f\xC3\xBCr bessere Kostenkontrolle.</p>
            </div>
        </div>

        <h2>Integration mit RMC Services</h2>
        <p>Vollst\xC3\xA4ndige Integration mit Tankkarten und Mautl\xC3\xB6sungen f\xC3\xBCr komplettes Flottenmanagement aus einer Hand.</p>
HTML;
    }

    private function generateGeneralContent($lang, $t) {
        $desc = $t['page']['description'] ?? 'Europ\xC3\xA4ische Mobilit\xC3\xA4tsl\xC3\xB6sungen von filo.cards';
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
        echo "Generated: sitemap-seo.xml\n";
    }

    private function getRedirectText($lang) {
        $texts = [
            'de' => 'Sie werden automatisch zur Hauptseite weitergeleitet.',
            'tr' => 'Ana sayfaya otomatik olarak y\xC3\xB6nlendiriliyorsunuz.',
            'en' => 'You are being automatically redirected to the main page.'
        ];
        return $texts[$lang];
    }

    private function getRedirectLinkText($lang) {
        $texts = [
            'de' => 'Direkt zur Seite',
            'tr' => 'Do\xC4\x9Frudan sayfaya git',
            'en' => 'Go directly to page'
        ];
        return $texts[$lang];
    }

    private function generateMetaDescription($focus, $lang) {
        $descriptions = [
            'de' => [
                'fuel-solutions' => 'Europ\xC3\xA4ische Tankkarte f\xC3\xBCr Unternehmen. G\xC3\xBCnstiger Diesel, 19 L\xC3\xA4nder, keine Geb\xC3\xBChren. Jetzt kostenlos anfragen!',
                'payment-mobility' => 'LKW Maut Europa aus einer Hand. EETS-kompatibel f\xC3\xBCr 17 L\xC3\xA4nder. Einfache Abrechnung f\xC3\xBCr Speditionen.',
                'services' => 'Professionelles Flottenmanagement mit GPS-Tracking. KITIN Telematik f\xC3\xBCr Europa.'
            ],
            'tr' => [
                'fuel-solutions' => 'Kurumsal yak\xC4\xB1t kart\xC4\xB1 Avrupa. Ucuz dizel, 19 \xC3\xBClke, \xC3\xBCcretsiz. Hemen ba\xC5\x9Fvurun!',
                'payment-mobility' => 'Avrupa TIR ge\xC3\xA7i\xC5\x9F \xC3\xBCcreti tek yerden. 17 \xC3\xBClke EETS uyumlu. Basit faturaland\xC4\xB1rma.',
                'services' => 'GPS takipli profesyonel filo y\xC3\xB6netimi. KITIN telematik Avrupa \xC3\xA7\xC3\xB6z\xC3\xBCm\xC3\xBC.'
            ],
            'en' => [
                'fuel-solutions' => 'European fuel card for businesses. Cheap diesel, 19 countries, no fees. Apply now!',
                'payment-mobility' => 'European truck toll solution. EETS compatible for 17 countries. Simple billing.',
                'services' => 'Professional fleet management with GPS tracking. KITIN telematics for Europe.'
            ]
        ];

        return $descriptions[$lang][$focus] ?? $descriptions[$lang]['fuel-solutions'];
    }
}

try {
    echo "Starting robust SEO page generation...\n";
    $generator = new RobustSEOGenerator();
    $success = $generator->generateAllPages();

    if ($success) {
        echo "Robust SEO Pages generated successfully!\n";
        exit(0);
    } else {
        echo "SEO Page generation completed with errors!\n";
        exit(1);
    }

} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
