<?php
require_once __DIR__ . '/../cms/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'concert_date' => $_POST['concert_date'],
            'title_de'     => trim($_POST['title_de']),
            'title_en'     => trim($_POST['title_en'] ?? ''),
            'program_de'   => trim($_POST['program_de'] ?? ''),
            'program_en'   => trim($_POST['program_en'] ?? ''),
            'city'         => trim($_POST['city'] ?? ''),
            'venue'        => trim($_POST['venue'] ?? ''),
            'published'    => isset($_POST['published']) ? 1 : 0,
        ];
        if ($id > 0) {
            $sql = "UPDATE concerts SET concert_date=:concert_date,title_de=:title_de,title_en=:title_en,program_de=:program_de,program_en=:program_en,city=:city,venue=:venue,published=:published WHERE id=:id";
            $data['id'] = $id;
            db()->prepare($sql)->execute($data);
        } else {
            $sql = "INSERT INTO concerts (concert_date,title_de,title_en,program_de,program_en,city,venue,published) VALUES (:concert_date,:title_de,:title_en,:program_de,:program_en,:city,:venue,:published)";
            db()->prepare($sql)->execute($data);
        }
    } elseif ($action === 'delete') {
        db()->prepare("DELETE FROM concerts WHERE id=?")->execute([(int)$_POST['id']]);
    }
    header('Location: /admin/concerts.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM concerts WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM concerts ORDER BY concert_date DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Konzerte</title>
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Konzerte</h1>
  <h2><?= $edit ? 'Bearbeiten' : 'Neuer Eintrag' ?></h2>
  <form method="post" class="form-card">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <label>Datum<input name="concert_date" type="date" required value="<?= h($edit['concert_date'] ?? '') ?>"></label>
      <label>Stadt<input name="city" value="<?= h($edit['city'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Titel (DE)<input name="title_de" required value="<?= h($edit['title_de'] ?? '') ?>"></label>
      <label>Titel (EN)<input name="title_en" value="<?= h($edit['title_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Programm (DE)<input name="program_de" value="<?= h($edit['program_de'] ?? '') ?>"></label>
      <label>Programm (EN)<input name="program_en" value="<?= h($edit['program_en'] ?? '') ?>"></label>
    </div>
    <label>Ort/Saal<input name="venue" value="<?= h($edit['venue'] ?? '') ?>"></label>
    <label style="flex-direction:row;align-items:center;gap:10px;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    <div style="display:flex;gap:10px;">
      <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
      <?php if ($edit): ?><a class="btn-secondary" href="/admin/concerts.php" style="padding:12px 18px;">Abbrechen</a><?php endif; ?>
    </div>
  </form>

  <h2>Alle Konzerte</h2>
  <table class="list-table">
    <thead><tr><th>Datum</th><th>Titel</th><th>Stadt</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['concert_date']) ?></td>
        <td><?= h($r['title_de']) ?></td>
        <td><?= h($r['city']) ?></td>
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
    <?php if (!$rows): ?><tr><td colspan="5" class="subtle">Keine Konzerte vorhanden.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body></html>
