<?php
require_once __DIR__ . '/_data.php';
$page_title = 'Videos';
$page_label = 'Videos';
$page_h1 = 'Film &amp; <em>Konzertmitschnitte</em>';
$page_sub = 'Eine Auswahl von Konzertaufnahmen, Studio-Mitschnitten und Filmen.';
$videos = fetch_videos();
$extra_css = '<style>
.video-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 50px 32px; }
.video-card { display: flex; flex-direction: column; gap: 18px; }
.video-card .thumb {
  position: relative; aspect-ratio: 16/9; background: #0d0d0d; overflow: hidden; cursor: pointer;
  border: 1px solid rgba(255,255,255,0.06); transition: all 0.5s;
}
.video-card .thumb:hover { border-color: rgba(200,169,110,0.4); transform: translateY(-4px); }
.video-card .thumb img { width: 100%; height: 100%; object-fit: cover; }
.video-card .play {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.5) 100%);
  opacity: 0.85; transition: opacity 0.4s;
}
.video-card .thumb:hover .play { opacity: 1; }
.play svg { width: 56px; height: 56px; fill: var(--gold); filter: drop-shadow(0 4px 16px rgba(0,0,0,0.5)); }
.video-card h3 { font-family: var(--serif); font-size: 22px; font-weight: 400; line-height: 1.3; }
.video-card p { color: var(--ink-dim); font-family: var(--sans); font-size: 12px; letter-spacing: 0.05em; }
.video-modal {
  position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 1000;
  display: none; align-items: center; justify-content: center; padding: 40px;
}
.video-modal.open { display: flex; }
.video-modal video { max-width: 100%; max-height: 100%; }
.video-modal .close {
  position: absolute; top: 24px; right: 32px; color: var(--gold);
  font-size: 32px; cursor: pointer; background: none; border: none;
}
</style>';
include __DIR__ . '/_page-header.php';
?>

<?php if (empty($videos)): ?>
  <div class="empty-state">Noch keine Videos veröffentlicht.</div>
<?php else: ?>
  <div class="video-list">
    <?php foreach ($videos as $v): ?>
    <article class="video-card">
      <div class="thumb" data-video="/<?= h($v['file_path']) ?>">
        <?php if ($v['poster_path']): ?>
          <img src="/<?= h($v['poster_path']) ?>" alt="<?= h($v['title_de']) ?>">
        <?php endif; ?>
        <div class="play">
          <svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg>
        </div>
      </div>
      <div>
        <h3><?= h($v['title_de']) ?></h3>
        <?php if ($v['caption_de'] || $v['duration']): ?>
        <p><?= h($v['caption_de']) ?><?= $v['duration'] ? ' · ' . h($v['duration']) : '' ?></p>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <div class="video-modal" id="vmodal">
    <button class="close" id="vclose">×</button>
    <video id="vplayer" controls></video>
  </div>

  <script>
    const modal = document.getElementById('vmodal');
    const player = document.getElementById('vplayer');
    document.querySelectorAll('.thumb').forEach(t => {
      t.addEventListener('click', () => {
        player.src = t.dataset.video;
        modal.classList.add('open');
        player.play();
      });
    });
    document.getElementById('vclose').onclick = () => {
      modal.classList.remove('open');
      player.pause();
      player.src = '';
    };
    modal.addEventListener('click', e => { if (e.target === modal) document.getElementById('vclose').click(); });
  </script>
<?php endif; ?>

<?php include __DIR__ . '/_page-footer.php'; ?>
