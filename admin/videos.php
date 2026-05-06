<?php
require_once __DIR__ . '/../cms/db.php';
require_once __DIR__ . '/_upload.php';
require_admin();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $file = handle_upload('file', 'videos', ['mp4','mov','webm','m4v']);
            $poster = handle_upload('poster', 'videos', ['jpg','jpeg','png','webp']);
            $data = [
                'title_de'    => trim($_POST['title_de']),
                'title_en'    => trim($_POST['title_en'] ?? ''),
                'caption_de'  => trim($_POST['caption_de'] ?? ''),
                'caption_en'  => trim($_POST['caption_en'] ?? ''),
                'duration'    => trim($_POST['duration'] ?? ''),
                'featured'    => isset($_POST['featured']) ? 1 : 0,
                'published'   => isset($_POST['published']) ? 1 : 0,
                'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            ];
            if ($id > 0) {
                $sets = []; $params = $data;
                foreach (array_keys($data) as $k) $sets[] = "$k=:$k";
                if ($file)   { $sets[] = 'file_path=:file_path';     $params['file_path']   = $file; }
                if ($poster) { $sets[] = 'poster_path=:poster_path'; $params['poster_path'] = $poster; }
                $params['id'] = $id;
                db()->prepare("UPDATE videos SET " . implode(',', $sets) . " WHERE id=:id")->execute($params);
            } else {
                if (!$file) throw new RuntimeException('Videodatei erforderlich.');
                $data['file_path']   = $file;
                $data['poster_path'] = $poster;
                $cols = implode(',', array_keys($data));
                $vals = ':' . implode(',:', array_keys($data));
                db()->prepare("INSERT INTO videos ($cols) VALUES ($vals)")->execute($data);
            }
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM videos WHERE id=?")->execute([(int)$_POST['id']]);
        }
        header('Location: /admin/videos.php'); exit;
    } catch (Throwable $e) { $err = $e->getMessage(); }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM videos WHERE id=?"); $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM videos ORDER BY featured DESC, sort_order, id DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Videos</title>
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Videos</h1>
  <p class="subtle">Konzertmitschnitte als eigene Datei (kein YouTube-Embed).</p>
  <?php if ($err): ?><div class="error" style="margin-top:14px;"><?= h($err) ?></div><?php endif; ?>
  <h2><?= $edit ? 'Bearbeiten' : 'Neues Video' ?></h2>
  <form method="post" enctype="multipart/form-data" class="form-card" style="max-width:780px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <label>Titel (DE)<input name="title_de" required value="<?= h($edit['title_de'] ?? '') ?>"></label>
      <label>Titel (EN)<input name="title_en" value="<?= h($edit['title_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Untertitel (DE)<input name="caption_de" value="<?= h($edit['caption_de'] ?? '') ?>"></label>
      <label>Untertitel (EN)<input name="caption_en" value="<?= h($edit['caption_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Dauer (z.B. 4:12)<input name="duration" value="<?= h($edit['duration'] ?? '') ?>"></label>
      <label>Reihenfolge<input name="sort_order" type="number" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
    </div>
    <label>Videodatei (mp4/mov/webm)<?= $edit ? ' — leer lassen, um zu behalten' : '' ?><input type="file" name="file" accept="video/*" <?= $edit ? '' : 'required' ?>></label>
    <label>Vorschaubild (optional, jpg/png)<input type="file" name="poster" accept="image/*"></label>
    <div style="display:flex;gap:24px;align-items:center;">
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="featured" <?= ($edit['featured'] ?? 0) ? 'checked' : '' ?>> Hauptvideo</label>
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    </div>
    <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
  </form>

  <h2>Alle Videos</h2>
  <table class="list-table">
    <thead><tr><th>Titel</th><th>Dauer</th><th>Hauptvideo</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['title_de']) ?></td>
        <td><?= h($r['duration']) ?></td>
        <td><?= $r['featured'] ? '★' : '' ?></td>
        <td><?= $r['published'] ? '✓' : '—' ?></td>
        <td class="actions">
          <a href="?edit=<?= $r['id'] ?>">Bearbeiten</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Wirklich löschen?')">
            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn-danger" type="submit">Löschen</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="subtle">Noch keine Videos.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body></html>
