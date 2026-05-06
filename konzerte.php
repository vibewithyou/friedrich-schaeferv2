<?php
require_once __DIR__ . '/_data.php';
$page_title = 'Konzerte';
$page_label = 'Termine';
$page_h1 = 'Konzert<em>kalender</em>';
$page_sub = 'Kommende und vergangene Auftritte.';
$concerts = fetch_concerts();
$now = date('Y-m-d');
$upcoming = array_filter($concerts, fn($c) => $c['concert_date'] >= $now);
$past     = array_reverse(array_filter($concerts, fn($c) => $c['concert_date'] < $now));
$extra_css = '<style>
.concert-section { margin-bottom: 80px; }
.concert-section h2 {
  font-family: var(--serif); font-size: 28px; font-weight: 400;
  color: var(--gold); margin-bottom: 32px; padding-bottom: 14px;
  border-bottom: 1px solid rgba(200,169,110,0.2); letter-spacing: 0.05em;
}
.c-row {
  display: grid; grid-template-columns: 140px 1fr 1fr 1fr;
  gap: 24px; align-items: baseline; padding: 28px 0;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.c-row .date { color: var(--gold); font-family: var(--sans); font-size: 13px; letter-spacing: 0.15em; }
.c-row .title { font-family: var(--serif); font-size: 22px; line-height: 1.3; }
.c-row .venue { color: var(--ink-dim); font-family: var(--sans); font-size: 13px; }
.c-row .ticket {
  text-align: right;
}
.c-row .ticket a {
  color: var(--gold); text-decoration: none; font-family: var(--sans);
  font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase;
  border: 1px solid rgba(200,169,110,0.3); padding: 8px 16px; transition: all 0.3s;
}
.c-row .ticket a:hover { background: var(--gold); color: #000; }
.c-row.past { opacity: 0.55; }
@media (max-width: 768px) {
  .c-row { grid-template-columns: 1fr; gap: 6px; padding: 20px 0; }
  .c-row .ticket { text-align: left; margin-top: 8px; }
}
</style>';
include __DIR__ . '/_page-header.php';

function fmt_date($d, $de = true) {
  $months_de = [1=>'Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
  $t = strtotime($d);
  return date('d', $t) . '. ' . $months_de[(int)date('n', $t)] . ' ' . date('Y', $t);
}
?>

<?php if (empty($concerts)): ?>
  <div class="empty-state">Noch keine Konzerttermine veröffentlicht.</div>
<?php else: ?>

  <?php if (!empty($upcoming)): ?>
  <section class="concert-section">
    <h2>Kommende Konzerte</h2>
    <?php foreach ($upcoming as $c): ?>
      <div class="c-row">
        <div class="date"><?= fmt_date($c['concert_date']) ?><?= $c['time'] ? ' · ' . substr($c['time'], 0, 5) : '' ?></div>
        <div class="title"><?= h($c['title_de']) ?></div>
        <div class="venue">
          <?= h($c['venue']) ?><?php if ($c['city']): ?><br><?= h($c['city']) ?><?php endif; ?>
        </div>
        <div class="ticket">
          <?php if ($c['ticket_url']): ?>
            <a href="<?= h($c['ticket_url']) ?>" target="_blank" rel="noopener">Tickets →</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($past)): ?>
  <section class="concert-section">
    <h2>Vergangene Konzerte</h2>
    <?php foreach ($past as $c): ?>
      <div class="c-row past">
        <div class="date"><?= fmt_date($c['concert_date']) ?></div>
        <div class="title"><?= h($c['title_de']) ?></div>
        <div class="venue">
          <?= h($c['venue']) ?><?php if ($c['city']): ?><br><?= h($c['city']) ?><?php endif; ?>
        </div>
        <div class="ticket"></div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/_page-footer.php'; ?>
