<?php
require_once __DIR__ . '/_data.php';
$page_title = 'Hörproben';
$page_label = 'Hörproben';
$page_h1 = '<em>Klang</em> erleben';
$page_sub = 'Aufnahmen aus Konzerten und Studio.';
$audios = fetch_audio();
$extra_css = '<style>
.audio-list { display: flex; flex-direction: column; }
.audio-row {
  display: grid; grid-template-columns: 60px 1fr 120px 60px;
  gap: 24px; align-items: center; padding: 24px 0;
  border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer;
  transition: background 0.3s;
}
.audio-row:hover { background: rgba(200,169,110,0.03); }
.audio-row .num { font-family: var(--sans); font-size: 11px; color: var(--gold); letter-spacing: 0.2em; }
.audio-row h4 { font-family: var(--serif); font-size: 22px; font-weight: 400; margin-bottom: 4px; }
.audio-row .composer { color: var(--ink-dim); font-family: var(--sans); font-size: 12px; }
.audio-row .dur { font-family: var(--sans); font-size: 12px; color: var(--ink-dim); text-align: right; }
.audio-row .play {
  width: 40px; height: 40px; border: 1px solid rgba(200,169,110,0.4);
  background: transparent; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.3s;
}
.audio-row .play:hover, .audio-row.playing .play { background: var(--gold); }
.audio-row .play svg { width: 12px; height: 12px; fill: var(--gold); }
.audio-row .play:hover svg, .audio-row.playing .play svg { fill: #000; }
.audio-row.playing { background: rgba(200,169,110,0.05); }
@media (max-width: 640px) {
  .audio-row { grid-template-columns: 40px 1fr 50px; }
  .audio-row .dur { display: none; }
}
</style>';
include __DIR__ . '/_page-header.php';
?>

<?php if (empty($audios)): ?>
  <div class="empty-state">Noch keine Hörproben veröffentlicht.</div>
<?php else: ?>
  <div class="audio-list">
    <?php foreach ($audios as $i => $a): ?>
    <div class="audio-row" data-audio="/<?= h($a['file_path']) ?>">
      <div class="num"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
      <div>
        <h4><?= h($a['title_de']) ?></h4>
        <div class="composer"><?= h($a['composer_de']) ?></div>
      </div>
      <div class="dur"><?= h($a['duration'] ?: '') ?></div>
      <button class="play" type="button"><svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg></button>
    </div>
    <?php endforeach; ?>
  </div>

  <audio id="aplayer" style="display:none;"></audio>
  <script>
    const aplayer = document.getElementById('aplayer');
    let currentRow = null;
    document.querySelectorAll('.audio-row').forEach(row => {
      row.addEventListener('click', () => {
        if (currentRow === row) {
          if (aplayer.paused) aplayer.play(); else aplayer.pause();
        } else {
          if (currentRow) currentRow.classList.remove('playing');
          currentRow = row;
          aplayer.src = row.dataset.audio;
          aplayer.play();
          row.classList.add('playing');
        }
      });
    });
    aplayer.addEventListener('pause', () => currentRow && currentRow.classList.remove('playing'));
    aplayer.addEventListener('play',  () => currentRow && currentRow.classList.add('playing'));
    aplayer.addEventListener('ended', () => currentRow && currentRow.classList.remove('playing'));
  </script>
<?php endif; ?>

<?php include __DIR__ . '/_page-footer.php'; ?>
