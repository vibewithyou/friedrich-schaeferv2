<?php
require_once __DIR__ . '/../cms/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $a = $_POST['action'] ?? '';
    if ($a === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'quote_de'  => trim($_POST['quote_de']),
            'quote_en'  => trim($_POST['quote_en'] ?? ''),
            'source_de' => trim($_POST['source_de'] ?? ''),
            'source_en' => trim($_POST['source_en'] ?? ''),
            'published' => isset($_POST['published']) ? 1 : 0,
            'sort_order'=> (int)($_POST['sort_order'] ?? 0),
        ];
        if ($id > 0) {
            $data['id'] = $id;
            db()->prepare("UPDATE press_quotes SET quote_de=:quote_de,quote_en=:quote_en,source_de=:source_de,source_en=:source_en,published=:published,sort_order=:sort_order WHERE id=:id")->execute($data);
        } else {
            db()->prepare("INSERT INTO press_quotes (quote_de,quote_en,source_de,source_en,published,sort_order) VALUES (:quote_de,:quote_en,:source_de,:source_en,:published,:sort_order)")->execute($data);
        }
    } elseif ($a === 'delete') {
        db()->prepare("DELETE FROM press_quotes WHERE id=?")->execute([(int)$_POST['id']]);
    }
    header('Location: /admin/press.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM press_quotes WHERE id=?"); $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$rows = db()->query("SELECT * FROM press_quotes ORDER BY sort_order, id")->fetchAll();
?><!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><title>Admin · Presse</title><link rel="stylesheet" href="/admin/admin.css"></head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Presse</h1>
  <h2><?= $edit ? 'Bearbeiten' : 'Neues Zitat' ?></h2>
  <form method="post" class="form-card" style="max-width:880px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <label>Zitat (DE)<textarea name="quote_de" rows="4" required><?= h($edit['quote_de'] ?? '') ?></textarea></label>
      <label>Zitat (EN)<textarea name="quote_en" rows="4"><?= h($edit['quote_en'] ?? '') ?></textarea></label>
    </div>
    <div class="form-row">
      <label>Quelle (DE)<input name="source_de" value="<?= h($edit['source_de'] ?? '') ?>"></label>
      <label>Quelle (EN)<input name="source_en" value="<?= h($edit['source_en'] ?? '') ?>"></label>
    </div>
    <div class="form-row">
      <label>Reihenfolge<input name="sort_order" type="number" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <label style="flex-direction:row;gap:8px;align-items:center;"><input type="checkbox" name="published" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>> Veröffentlicht</label>
    </div>
    <button type="submit"><?= $edit ? 'Aktualisieren' : 'Anlegen' ?></button>
  </form>
  <h2>Alle Zitate</h2>
  <table class="list-table">
    <thead><tr><th>Zitat</th><th>Quelle</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td style="max-width:480px;"><?= h(mb_substr($r['quote_de'],0,140)) ?><?= mb_strlen($r['quote_de'])>140?'…':'' ?></td>
        <td><?= h($r['source_de']) ?></td>
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
    <?php if (!$rows): ?><tr><td colspan="4" class="subtle">Noch keine Zitate.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body></html>
