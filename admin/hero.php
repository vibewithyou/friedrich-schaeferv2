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
            $type = $_POST['media_type'] === 'video' ? 'video' : 'image';
            $allowed = $type === 'video' ? ['mp4','mov','webm','m4v'] : ['jpg','jpeg','png','webp'];
            $file = handle_upload('file', 'hero', $allowed);
            $data = [
                'media_type' => $type,
                'published'  => isset($_POST['published']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];
            if ($id > 0) {
                $sets = []; $params = $data;
                foreach (array_keys($data) as $k) $sets[] = "$k=:$k";
                if ($file) { $sets[] = 'file_path=:file_path'; $params['file_path'] = $file; }
                $params['id'] = $id;
                db()->prepare("UPDATE hero_slides SET " . implode(',', $sets) . " WHERE id=:id")->execute($params);
            } else {
                if (!$file) throw new RuntimeException('Datei erforderlich.');
                $data['file_path'] = $file;
                $cols = implode(',', array_keys($data));
                $vals = ':' . implode(',:', array_keys($data));
                db()->prepare("INSERT INTO hero_slides ($cols) VALUES ($vals)")->execute($data);
            }
        } elseif ($a === 'delete') {
            db()->prepare("DELETE FROM hero_slides WHERE id=?")->execute([(int)$_POST['id']]);
        }
        header('Location: /admin/hero.php'); exit;
    } catch (Throwable $e) { $err = $e->getMessage(); }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM hero_slides WHERE id=?"); $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM hero_slides ORDER BY sort_order, id")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><title>Admin · Hero</title><link rel="stylesheet" href="/admin/admin.css"></head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Hero-Slides</h1>
  <p class="subtle">Hintergrund-Medien für die Startseite (max. 3 wirken am besten).</p>
  <?php if ($err): ?><div class="error" style="margin-top:14px;"><?= h($err) ?></div><?php endif; ?>
  <h2><?= $edit ? 'Bearbeiten' : 'Neuer Slide' ?></h2>
  <form method="post" enctype="multipart/form-data" class="form-card" style="max-width:780px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Typ
      <select name="media_type">
        <option value="image" <?= ($edit['media_type'] ?? '') === 'image' ? 'selected' : '' ?>>Bild</option>
        <option value="video" <?= ($edit['media_type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
      </select>
    </label>
    <label>Datei<input type="file" name="file" <?= $edit ? '' : 'required' ?>></label>
    <div class="form-row">
      <label>Reihenfolge<input name="sort_order" type="number" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    </div>
    <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
  </form>

  <h2>Alle Slides</h2>
  <table class="list-table">
    <thead><tr><th>#</th><th>Typ</th><th>Datei</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= $r['sort_order'] ?></td>
        <td><?= h($r['media_type']) ?></td>
        <td style="font-family:monospace;font-size:11px;"><?= h(basename($r['file_path'])) ?></td>
        <td><?= $r['published'] ? '✓' : '—' ?></td>
        <td class="actions">
          <a href="?edit=<?= $r['id'] ?>">Bearbeiten</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Löschen?')">
            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn-danger" type="submit">Löschen</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="subtle">Noch keine Slides.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body></html>
