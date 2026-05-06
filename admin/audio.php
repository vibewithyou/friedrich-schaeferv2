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
            $file = handle_upload('file', 'audio', ['mp3','m4a','wav','ogg']);
            $data = [
                'track_num'   => trim($_POST['track_num'] ?? ''),
                'title_de'    => trim($_POST['title_de']),
                'title_en'    => trim($_POST['title_en'] ?? ''),
                'composer_de' => trim($_POST['composer_de'] ?? ''),
                'composer_en' => trim($_POST['composer_en'] ?? ''),
                'duration'    => trim($_POST['duration'] ?? ''),
                'published'   => isset($_POST['published']) ? 1 : 0,
                'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            ];
            if ($id > 0) {
                $sets = []; $params = $data;
                foreach (array_keys($data) as $k) $sets[] = "$k=:$k";
                if ($file) { $sets[] = 'file_path=:file_path'; $params['file_path'] = $file; }
                $params['id'] = $id;
                db()->prepare("UPDATE audio_tracks SET " . implode(',', $sets) . " WHERE id=:id")->execute($params);
            } else {
                if (!$file) throw new RuntimeException('Audiodatei erforderlich.');
                $data['file_path'] = $file;
                $cols = implode(',', array_keys($data));
                $vals = ':' . implode(',:', array_keys($data));
                db()->prepare("INSERT INTO audio_tracks ($cols) VALUES ($vals)")->execute($data);
            }
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM audio_tracks WHERE id=?")->execute([(int)$_POST['id']]);
        }
        header('Location: /admin/audio.php'); exit;
    } catch (Throwable $e) { $err = $e->getMessage(); }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM audio_tracks WHERE id=?"); $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM audio_tracks ORDER BY sort_order, id")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Hörproben</title>
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Hörproben</h1>
  <?php if ($err): ?><div class="error" style="margin-top:14px;"><?= h($err) ?></div><?php endif; ?>
  <h2><?= $edit ? 'Bearbeiten' : 'Neue Hörprobe' ?></h2>
  <form method="post" enctype="multipart/form-data" class="form-card" style="max-width:780px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <label>Nummer (z.B. 01)<input name="track_num" value="<?= h($edit['track_num'] ?? '') ?>"></label>
      <label>Dauer (z.B. 3:42)<input name="duration" value="<?= h($edit['duration'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Titel (DE)<input name="title_de" required value="<?= h($edit['title_de'] ?? '') ?>"></label>
      <label>Titel (EN)<input name="title_en" value="<?= h($edit['title_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Komponist/Werk (DE)<input name="composer_de" value="<?= h($edit['composer_de'] ?? '') ?>"></label>
      <label>Komponist/Werk (EN)<input name="composer_en" value="<?= h($edit['composer_en'] ?? '') ?>"></label>
    </div>
    <label>Audiodatei (mp3/m4a/wav)<input type="file" name="file" accept="audio/*" <?= $edit ? '' : 'required' ?>></label>
    <div class="form-row">
      <label>Reihenfolge<input name="sort_order" type="number" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    </div>
    <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
  </form>

  <h2>Alle Hörproben</h2>
  <table class="list-table">
    <thead><tr><th>#</th><th>Titel</th><th>Komponist</th><th>Dauer</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['track_num']) ?></td>
        <td><?= h($r['title_de']) ?></td>
        <td><?= h($r['composer_de']) ?></td>
        <td><?= h($r['duration']) ?></td>
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
    <?php if (!$rows): ?><tr><td colspan="6" class="subtle">Noch keine Hörproben.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body></html>
