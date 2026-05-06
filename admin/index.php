<?php
require_once __DIR__ . '/../cms/db.php';
$admin = require_admin();

// Quick stats
$counts = [];
foreach (['concerts','videos','audio_tracks','press_quotes','gallery_images','hero_slides'] as $t) {
    $counts[$t] = (int)db()->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
}
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Übersicht</h1>
  <p class="subtle">Willkommen, <?= h($admin['username']) ?>.</p>
  <div class="grid">
    <a class="stat-card" href="/admin/concerts.php"><span><?= $counts['concerts'] ?></span>Konzerte</a>
    <a class="stat-card" href="/admin/videos.php"><span><?= $counts['videos'] ?></span>Videos</a>
    <a class="stat-card" href="/admin/audio.php"><span><?= $counts['audio_tracks'] ?></span>Hörproben</a>
    <a class="stat-card" href="/admin/press.php"><span><?= $counts['press_quotes'] ?></span>Pressezitate</a>
    <a class="stat-card" href="/admin/gallery.php"><span><?= $counts['gallery_images'] ?></span>Galerie</a>
    <a class="stat-card" href="/admin/hero.php"><span><?= $counts['hero_slides'] ?></span>Hero-Slides</a>
  </div>
</main>
</body></html>
