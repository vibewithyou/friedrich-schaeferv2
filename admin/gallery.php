<?php
require_once __DIR__ . '/../cms/db.php';
require_once __DIR__ . '/_upload.php';
require_admin();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $a = $_POST['action'] ?? '';
    try {
        if ($a === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $file = handle_upload('file', 'gallery', ['jpg','jpeg','png','webp']);
            $data = [
                'caption_de' => trim($_POST['caption_de'] ?? ''),
                'caption_en' => trim($_POST['caption_en'] ?? ''),
                'published'  => isset($_POST['published']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];
            if ($id > 0) {
                $sets = []; $params = $data;
                foreach (array_keys($data) as $k) $sets[] = "$k=:$k";
                if ($file) { $sets[] = 'file_path=:file_path'; $params['file_path'] = $file; }
                $params['id'] = $id;
                db()->prepare("UPDATE gallery_images SET " . implode(',', $sets) . " WHERE id=:id")->execute($params);
            } else {
                if (!$file) throw new RuntimeException('Bilddatei erforderlich.');
                $data['file_path'] = $file;
                $cols = implode(',', array_keys($data));
                $vals = ':' . implode(',:', array_keys($data));
                db()->prepare("INSERT INTO gallery_images ($cols) VALUES ($vals)")->execute($data);
            }
        } elseif ($a === 'delete') {
            db()->prepare("DELETE FROM gallery_images WHERE id=?")->execute([(int)$_POST['id']]);
        }
        header('Location: /admin/gallery.php'); exit;
    } catch (Throwable $e) { $err = $e->getMessage(); }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM gallery_images WHERE id=?"); $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM gallery_images ORDER BY sort_order, id")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><title>Admin · Galerie</title><link rel="stylesheet" href="/admin/admin.css"></head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Galerie</h1>
  <?php if ($err): ?><div class="error" style="margin-top:14px;"><?= h($err) ?></div><?php endif; ?>
  <h2><?= $edit ? 'Bearbeiten' : 'Neues Bild' ?></h2>
  <form method="post" enctype="multipart/form-data" class="form-card" style="max-width:780px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Bild (jpg/png/webp)<input type="file" name="file" accept="image/*" <?= $edit ? '' : 'required' ?>></label>
    <div class="form-row">
      <label>Bildunterschrift (DE)<input name="caption_de" value="<?= h($edit['caption_de'] ?? '') ?>"></label>
      <label>Bildunterschrift (EN)<input name="caption_en" value="<?= h($edit['caption_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Reihenfolge<input name="sort_order" type="number" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    </div>
    <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
  </form>

  <h2>Galerie</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:16px;">
    <?php foreach ($rows as $r): ?>
      <div style="background:#141414;border:1px solid rgba(255,255,255,0.06);">
        <img src="/<?= h($r['file_path']) ?>" alt="" style="width:100%;height:150px;object-fit:cover;display:block;">
        <div style="padding:10px;display:flex;justify-content:space-between;align-items:center;font-size:11px;">
          <span><?= $r['published'] ? '✓' : '—' ?> · #<?= $r['sort_order'] ?></span>
          <div class="actions">
            <a href="?edit=<?= $r['id'] ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Löschen?')">
              <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn-danger" type="submit">Del</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (!$rows): ?><p class="subtle" style="margin-top:14px;">Noch keine Bilder.</p><?php endif; ?>
</main>
</body></html>
