<?php
// admin/seed-demo-data.php — Beispieldaten ins CMS einpflegen
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../cms/db.php';
require_admin();

$action = $_GET['action'] ?? '';
$messages = [];
$err = null;

if ($action === 'run') {
  try {
    $pdo = db();

    // ── HÖRPROBEN ──
    $audio_seed = [
      ['Edvard Grieg — Zug der Zwerge', 'Edvard Grieg — March of the Dwarfs', 'Lyrische Stücke op. 54 Nr. 3', 'Lyric Pieces op. 54 No. 3', '3:42'],
      ['Carl Reinecke — Tarantelle', 'Carl Reinecke — Tarantella', 'Klavierstücke für die Jugend', 'Piano Pieces for Youth', '2:15'],
      ['Valery Gavrilin — Little Clock', 'Valery Gavrilin — Little Clock', 'Charakterstück', 'Character Piece', '1:58'],
      ['Daniel Gottlob Türk — The Storm', 'Daniel Gottlob Türk — The Storm', 'Klavierstück', 'Piano Piece', '2:34'],
    ];
    $stmt = $pdo->prepare("INSERT INTO audio_tracks (title_de, title_en, composer_de, composer_en, duration, file_path, sort_order, published) VALUES (?, ?, ?, ?, ?, '', ?, 1)");
    foreach ($audio_seed as $i => $row) { $row[] = $i + 1; $stmt->execute($row); }
    $messages[] = count($audio_seed) . ' Hörproben eingefügt.';

    // ── VIDEOS ──
    $video_seed = [
      ['Edvard Grieg — Zug der Zwerge, op. 54 Nr. 3', 'Edvard Grieg — March of the Dwarfs, op. 54 No. 3', 'Live-Aufnahme · Platzhalter', 'Live Recording · Placeholder', '3:42', 1],
      ['Carl Reinecke — Tarantelle', 'Carl Reinecke — Tarantella', 'Konzertmitschnitt · Platzhalter', 'Concert Recording · Placeholder', '2:15', 0],
      ['D. G. Türk — The Storm', 'D. G. Türk — The Storm', 'Studioaufnahme · Platzhalter', 'Studio Recording · Placeholder', '2:34', 0],
    ];
    $stmt = $pdo->prepare("INSERT INTO videos (title_de, title_en, caption_de, caption_en, duration, featured, file_path, poster_path, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, '', '', ?, 1)");
    foreach ($video_seed as $i => $row) { $row[] = $i + 1; $stmt->execute($row); }
    $messages[] = count($video_seed) . ' Video-Platzhalter eingefügt.';

    // ── GALERIE ── (file_path ist NOT NULL, also dummy "" für leere)
    $gallery_seed = [
      ['portrait.jpg', 'Friedrich Schäfer · Karlsbad', 'Friedrich Schäfer · Karlovy Vary'],
      ['', 'Konzertsaal', 'Concert Hall'],
      ['', 'Bühnenfoto', 'Stage Photo'],
      ['', 'Hände am Klavier', 'Hands on Piano'],
      ['', 'Probe / Studio', 'Rehearsal / Studio'],
    ];
    $stmt = $pdo->prepare("INSERT INTO gallery_images (file_path, caption_de, caption_en, sort_order, published) VALUES (?, ?, ?, ?, 1)");
    foreach ($gallery_seed as $i => $row) { $row[] = $i + 1; $stmt->execute($row); }
    $messages[] = count($gallery_seed) . ' Galerie-Einträge eingefügt (das erste nutzt portrait.jpg, die anderen müssen mit Bildern befüllt werden).';

    // ── PRESSESTIMMEN ── (source_de / source_en)
    $press_seed = [
      ['Eine pianistische Stimme von bemerkenswerter Reife und Ausdruckskraft — technische Brillanz verbunden mit echtem musikalischen Gespür.', 'A pianistic voice of remarkable maturity and expressiveness — technical brilliance combined with genuine musical sensitivity.', 'Presse · Platzhalter', 'Press · Placeholder'],
      ['Schäfer spielt mit einer erzählerischen Energie, die das Publikum von der ersten bis zur letzten Note in ihren Bann zieht.', 'Schäfer plays with a narrative energy that captivates the audience from the first to the last note.', 'Konzertbericht · Platzhalter', 'Concert review · Placeholder'],
      ['Lyrische Tiefe und virtuose Lebendigkeit vereinen sich in einem Spiel, das weit über das Alter des Pianisten hinausweist.', 'Lyrical depth and virtuosic vitality combine in a playing style that far exceeds the pianist\'s age.', 'Kritik · Platzhalter', 'Critique · Placeholder'],
    ];
    $stmt = $pdo->prepare("INSERT INTO press_quotes (quote_de, quote_en, source_de, source_en, sort_order, published) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($press_seed as $i => $row) { $row[] = $i + 1; $stmt->execute($row); }
    $messages[] = count($press_seed) . ' Pressestimmen eingefügt.';

    // ── KONZERTE ── (kein 'time', kein 'ticket_url' in Schema)
    $concerts_seed = [
      [date('Y-m-d', strtotime('+30 days')), 'Kammerkonzert', 'Chamber Concert', 'Grieg · Reinecke · Türk', 'Grieg · Reinecke · Türk', 'Freiberg', 'Dom St. Marien'],
      [date('Y-m-d', strtotime('+45 days')), 'Sommerkonzert', 'Summer Concert', 'Brahms · Chopin · Schubert', 'Brahms · Chopin · Schubert', 'Chemnitz', 'Städtische Musikschule'],
      [date('Y-m-d', strtotime('+75 days')), 'Solo-Recital', 'Solo Recital', 'Bach · Beethoven · Liszt', 'Bach · Beethoven · Liszt', 'Dresden', 'Schloss Pillnitz'],
      [date('Y-m-d', strtotime('+95 days')), 'Klavierabend', 'Piano Evening', 'Romantisches Programm', 'Romantic Programme', 'Leipzig', 'Mendelssohn-Haus'],
    ];
    $stmt = $pdo->prepare("INSERT INTO concerts (concert_date, title_de, title_en, program_de, program_en, city, venue, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    foreach ($concerts_seed as $i => $row) { $row[] = $i + 1; $stmt->execute($row); }
    $messages[] = count($concerts_seed) . ' Konzerte eingefügt.';

    $messages[] = 'Fertig! Du kannst alles jetzt im CMS bearbeiten oder löschen.';
  } catch (Throwable $e) {
    $err = $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><title>Beispieldaten einpflegen</title>
<style>
  body { font-family: system-ui, sans-serif; max-width: 720px; margin: 60px auto; padding: 0 20px; background: #fafafa; color: #222; line-height: 1.6; }
  h1 { font-weight: 400; border-bottom: 2px solid #c8a96e; padding-bottom: 12px; }
  .box { background: #fff; padding: 28px; border: 1px solid #e0e0e0; border-radius: 4px; margin: 24px 0; }
  .btn { display: inline-block; background: #c8a96e; color: #000; padding: 12px 28px; text-decoration: none; border-radius: 3px; font-weight: 500; }
  .btn:hover { background: #b09455; }
  .btn-back { background: #444; color: #fff; }
  ul { padding-left: 20px; } ul li { margin: 6px 0; }
  .ok { color: #2a7a3a; } .err { color: #b00; background: #ffeaea; padding: 14px; border-left: 4px solid #b00; }
  .warn { background: #fff8e1; border-left: 4px solid #c8a96e; padding: 14px 18px; margin: 16px 0; }
  pre { background: #f5f5f5; padding: 12px; overflow: auto; font-size: 12px; }
</style></head><body>
<h1>📦 Beispieldaten einpflegen</h1>
<?php if ($err): ?>
  <div class="box"><div class="err"><strong>Fehler:</strong><pre><?= htmlspecialchars($err) ?></pre></div>
    <p><a href="?" class="btn btn-back">← Zurück</a></p></div>
<?php elseif ($action === 'run'): ?>
  <div class="box"><h2 class="ok">✓ Erfolgreich</h2>
    <ul><?php foreach ($messages as $m): ?><li><?= h($m) ?></li><?php endforeach; ?></ul>
    <p style="margin-top:24px;"><a href="/admin/" class="btn">→ Zurück zum CMS</a>
    <a href="/" class="btn btn-back" style="margin-left:8px;">→ Startseite</a></p></div>
<?php else: ?>
  <div class="box">
    <p>Fügt Original-Platzhalterdaten ein:</p>
    <ul><li>4 Hörproben</li><li>3 Video-Platzhalter (ohne Datei)</li><li>5 Galerie-Einträge</li><li>3 Pressestimmen</li><li>4 Konzerttermine</li></ul>
    <div class="warn">⚠️ Nur einmal ausführen, sonst Duplikate.</div>
    <p style="margin-top:24px;"><a href="?action=run" class="btn">▶ Jetzt einpflegen</a>
    <a href="/admin/" class="btn btn-back" style="margin-left:8px;">Abbrechen</a></p></div>
<?php endif; ?>
</body></html>
