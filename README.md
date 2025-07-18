# filo.cards - European Mobility Solutions

A static multilingual website for the **filo.cards** mobility services. It serves content in German, Turkish and English using a lightweight JavaScript translation system. The project is entirely static and can be deployed on any web server without a build step.

## Project Architecture

```
css/                    Stylesheets with cache busting
js/                     JavaScript modules (translations, features, chat, SEO)
images/                 Graphics and assets
data/                   JSON configuration files
seo/                    Auto-generated SEO landing pages
admin/                  Content management panel
logs/                   Contact form and webhook logs
tr/                     Legacy Turkish version (deprecated)
```

Key files include `index.template.html` as the main HTML template, `generate-seo-pages.php` for SEO landing page generation and `contact.php` for form handling and logging. All text content lives in `data/translations.json`.

## Deployment System

The site uses a webhook-based deployment process which pulls from Git, regenerates `index.html` with cache busting, generates SEO landing pages and optionally purges the Cloudflare cache. The webhook script is not part of this repository.

### Cache Busting

Assets are referenced with their file modification timestamps to ensure browsers always load the latest versions:

```
css/styles.css?v={timestamp}
js/main.js?v={timestamp}
js/translations.js?v={timestamp}
data/translations.json?v={timestamp-hash}
```

## SEO Landing Pages

`generate-seo-pages.php` creates 12 landing pages (four keywords × three languages) along with `sitemap-seo.xml`. Each page contains a short redirect, structured data and the correct meta tags for search engines.

## Feature Flags

Configuration is stored in `data/feature-flags.json` and loaded at runtime. Flags can enable integrations such as WhatsApp chat, content management or analytics. Phone numbers and other details are configurable but not stored in this repository.

## Admin Panel

The `/admin/` directory provides a password-protected interface for managing feature flags, regenerating SEO pages and viewing logs. Credentials are configured outside of version control.

## Translation System

Translations are loaded from `data/translations.json` by `js/translations.js`. The JavaScript API exposes a `getTranslation` method to populate page content dynamically.

## Contact Form

`contact.php` processes multilingual form submissions, performs spam detection and writes detailed logs. Logs are stored under `logs/` and can be viewed through a dedicated interface protected with a password.

## Development Workflow

Because the site is purely static there is no build step. For local testing you can serve the directory with a simple HTTP server:

```bash
npx http-server
```

Deployment is handled via the webhook which performs `git pull`, regenerates pages and purges caches.

## Security & Performance

Sensitive files such as webhook scripts and Cloudflare credentials are kept out of the repository. The site uses cache busting, optimized images and optional Cloudflare CDN integration to ensure good performance.

## License

This project is released under the [MIT License](LICENSE).
