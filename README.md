# Tankkarte

A static multilingual website for the **Filo.cards** fuel card service. The project delivers
content in German and Turkish using a small JavaScript based translation system. The site is
primarily HTML, CSS and vanilla JavaScript so no heavy build process is required.

## Project Structure

```
css/            Stylesheets
js/             JavaScript for translation and page logic
images/         Graphics used by the site
_en_backup/     Previous English version kept for reference
tr/             Turkish version of the landing page
```

All text translations live in `data/translations.json` and are loaded at runtime.

## Building

Because the project is purely static, there is no traditional build step. If you would like to
work on the site locally, simply clone the repository and open `index.html` in your browser.
Optionally you can use any static HTTP server to view the site, for example:

```bash
npx http-server
```

This command serves the directory on a local port so you can test the translation loader.

## Deployment

Deployment consists of copying the repository contents to your hosting provider. Any static file
host such as GitHub Pages, Netlify or a traditional web server will work. Make sure the folder
structure is preserved and that `index.html` is served from the root of your domain.

### Cache busting via webhook

The file `index.template.html` contains the main HTML markup. A deployment webhook
generates `index.html` from this template after running `git pull`. The script
adds the modification timestamps of `css/styles.css`, `js/translations.js` and
`js/main.js` as query parameters so browsers always fetch the latest assets. The
generated `index.html` is ignored by Git.

The relevant PHP snippet for the webhook looks like this:

```php
// Cache-Busting: Generate index.html with timestamps
$cssTime = filemtime("$repoDir/css/styles.css");
$jsTransTime = filemtime("$repoDir/js/translations.js");
$jsMainTime = filemtime("$repoDir/js/main.js");

// Read template
$template = file_get_contents("$repoDir/index.template.html");

// Replace with timestamps
$html = str_replace(
    [
        'href="css/styles.css"',
        'src="js/translations.js"',
        'src="js/main.js"'
    ],
    [
        'href="css/styles.css?v=' . $cssTime . '"',
        'src="js/translations.js?v=' . $jsTransTime . '"',
        'src="js/main.js?v=' . $jsMainTime . '"'
    ],
    $template
);

// Write index.html
file_put_contents("$repoDir/index.html", $html);
```

## License

This project is released under the [MIT License](LICENSE). You are free to use, modify and
redistribute it under the terms of that license.
