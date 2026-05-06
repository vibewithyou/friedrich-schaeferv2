<?php
// admin/init.php — Einmalig nach dem Schema-Import aufrufen.
// Erzeugt den Admin-User "Friedrich-Schaefer" mit Passwort "start123".
// SOFORT NACH ERFOLGREICHEM AUFRUF DIESE DATEI VOM SERVER LÖSCHEN.

require_once __DIR__ . '/../cms/db.php';

$username = 'Friedrich-Schaefer';
$password = 'start123';

try {
    $pdo = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Bestehenden Eintrag aktualisieren oder anlegen
    $stmt = $pdo->prepare('SELECT id FROM admin WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row) {
        $up = $pdo->prepare('UPDATE admin SET password_hash = ? WHERE id = ?');
        $up->execute([$hash, $row['id']]);
        $msg = 'Admin-Benutzer "' . $username . '" aktualisiert.';
    } else {
        // Vorhandene Default-Admins entfernen
        $pdo->prepare('DELETE FROM admin WHERE username = ?')->execute(['admin']);
        $ins = $pdo->prepare('INSERT INTO admin (username, password_hash) VALUES (?, ?)');
        $ins->execute([$username, $hash]);
        $msg = 'Admin-Benutzer "' . $username . '" angelegt.';
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>Fehler: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    exit;
}
?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><title>CMS · Initialisierung</title>
<style>
  body { font-family: -apple-system, system-ui, sans-serif; background: #111; color: #eee;
         max-width: 640px; margin: 80px auto; padding: 32px; line-height: 1.6; }
  h1 { color: #c8a96e; font-weight: 400; }
  code { background: #222; padding: 2px 8px; border-radius: 3px; }
  .ok { color: #6ec891; }
  .warn { color: #e0b060; padding: 16px; background: #2a1f0e; border-left: 3px solid #c8a96e; }
  a { color: #c8a96e; }
</style></head><body>
<h1>CMS · Initialisierung erfolgreich</h1>
<p class="ok">✓ <?= htmlspecialchars($msg) ?></p>
<p>
  <strong>Login:</strong><br>
  Benutzer: <code><?= htmlspecialchars($username) ?></code><br>
  Passwort: <code><?= htmlspecialchars($password) ?></code>
</p>
<p class="warn">
  ⚠ Wichtig: Lösche jetzt die Datei <code>admin/init.php</code> vom Server,
  damit niemand sonst das Passwort zurücksetzen kann.
  <br>Anschließend unter <a href="/admin/login.php">/admin/login.php</a> einloggen
  und das Passwort über „Passwort ändern" auf einen eigenen Wert setzen.
</p>
</body></html>
