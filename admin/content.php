<?php
require_once __DIR__ . '/../cms/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach (($_POST['blocks'] ?? []) as $key => $val) {
        $stmt = db()->prepare("INSERT INTO content_blocks (key_name, value_de, value_en) VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE value_de=VALUES(value_de), value_en=VALUES(value_en)");
        $stmt->execute([$key, $val['de'] ?? '', $val['en'] ?? '']);
    }
    header('Location: /admin/content.php?saved=1'); exit;
}

$rows = db()->query("SELECT * FROM content_blocks ORDER BY key_name")->fetchAll();
$blocks = [];
foreach ($rows as $r) $blocks[$r['key_name']] = $r;
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Texte</title>
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Texte</h1>
  <p class="subtle">Allgemeine Inhalte (Hero-Quote, Biografie, Kontakt). Beide Sprachen pflegbar.</p>
  <?php if (!empty($_GET['saved'])): ?><div class="ok" style="margin-top:14px;">Gespeichert.</div><?php endif; ?>
  <form method="post" class="form-card" style="max-width:880px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <?php foreach ($blocks as $key => $row): ?>
      <h2 style="margin-top:8px;"><?= h($key) ?></h2>
      <div class="form-row">
        <label>DE<textarea name="blocks[<?= h($key) ?>][de]" rows="3"><?= h($row['value_de']) ?></textarea></label>
        <label>EN<textarea name="blocks[<?= h($key) ?>][en]" rows="3"><?= h($row['value_en']) ?></textarea></label>
      </div>
    <?php endforeach; ?>
    <button type="submit">Speichern</button>
  </form>
</main>
</body></html>
