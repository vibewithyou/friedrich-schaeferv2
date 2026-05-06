<?php
require_once __DIR__ . '/../cms/db.php';

start_admin_session();
if (current_admin()) { header('Location: /admin/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    if (attempt_login($u, $p)) { header('Location: /admin/'); exit; }
    $error = 'Login fehlgeschlagen.';
}
?><!DOCTYPE html>
<html lang="de"><head>
<meta charset="UTF-8"><title>Admin Login</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/admin/admin.css">
</head><body class="login-body">
<form class="login-card" method="post">
  <h1>Friedrich Schäfer · Admin</h1>
  <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
  <label>Benutzername<input name="username" autofocus required></label>
  <label>Passwort<input name="password" type="password" required></label>
  <button type="submit">Anmelden</button>
</form>
</body></html>
