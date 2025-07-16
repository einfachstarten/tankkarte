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

## License

This project is released under the [MIT License](LICENSE). You are free to use, modify and
redistribute it under the terms of that license.
