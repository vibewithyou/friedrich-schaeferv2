<?php
require_once __DIR__ . '/../cms/db.php';
$admin = require_admin();
$msg = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $err = change_password($admin['id'], $_POST['current'] ?? '', $_POST['new'] ?? '');
    if (!$err) $msg = 'Passwort wurde aktualisiert.';
}
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin · Passwort</title>
<link rel="stylesheet" href="/admin/admin.css">
</head><body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="admin-main">
  <h1>Passwort ändern</h1>
  <?php if ($msg): ?><div class="ok"><?= h($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="error"><?= h($err) ?></div><?php endif; ?>
  <form method="post" class="form-card">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <label>Aktuelles Passwort<input name="current" type="password" required></label>
    <label>Neues Passwort (min. 8 Zeichen)<input name="new" type="password" required minlength="8"></label>
    <button type="submit">Speichern</button>
  </form>
</main>
</body></html>
