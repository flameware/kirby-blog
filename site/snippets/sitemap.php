<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php foreach ($pages as $page): ?>
  <url>
    <loc><?= $page->url() ?></loc>
    <lastmod><?= $page->modified("Y-m-d") ?></lastmod>
  </url>
  <?php endforeach; ?>
</urlset>
