<?php
// cms/db.php — PDO Singleton + Auth Helpers

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $cfg = require __DIR__ . '/config.php';
    $d = $cfg['db'];
    $dsn = "mysql:host={$d['host']};port={$d['port']};dbname={$d['name']};charset={$d['charset']}";
    $pdo = new PDO($dsn, $d['user'], $d['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function start_admin_session(): void {
    $cfg = require __DIR__ . '/config.php';
    $name = $cfg['session']['cookie_name'];
    if (session_status() === PHP_SESSION_NONE) {
        session_name($name);
        session_set_cookie_params([
            'lifetime' => $cfg['session']['lifetime'],
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function current_admin(): ?array {
    start_admin_session();
    if (empty($_SESSION['admin_id'])) return null;
    $stmt = db()->prepare('SELECT id, username FROM admin WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function require_admin(): array {
    $a = current_admin();
    if (!$a) {
        header('Location: /admin/login.php');
        exit;
    }
    return $a;
}

function attempt_login(string $username, string $password): bool {
    $stmt = db()->prepare('SELECT id, password_hash FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if (!$row) return false;
    if (!password_verify($password, $row['password_hash'])) return false;
    start_admin_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$row['id'];
    return true;
}

function logout(): void {
    start_admin_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function change_password(int $admin_id, string $current, string $new): string {
    $stmt = db()->prepare('SELECT password_hash FROM admin WHERE id = ?');
    $stmt->execute([$admin_id]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) {
        return 'Aktuelles Passwort ist nicht korrekt.';
    }
    if (strlen($new) < 8) return 'Neues Passwort muss mindestens 8 Zeichen haben.';
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = db()->prepare('UPDATE admin SET password_hash = ? WHERE id = ?');
    $stmt->execute([$hash, $admin_id]);
    return '';
}

function csrf_token(): string {
    start_admin_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function check_csrf(): void {
    start_admin_session();
    $t = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        exit('CSRF token ungültig');
    }
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
