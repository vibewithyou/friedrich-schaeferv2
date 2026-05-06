<?php
require_once __DIR__ . '/_data.php';
$page_title = 'Galerie';
$page_label = 'Galerie';
$page_h1 = 'Foto<em>galerie</em>';
$page_sub = 'Konzerte, Porträts, Backstage.';
$photos = fetch_gallery();
$extra_css = '<style>
.gal-grid {
  column-count: 3; column-gap: 16px;
}
.gal-item {
  break-inside: avoid; margin-bottom: 16px; cursor: pointer; position: relative;
  overflow: hidden; transition: transform 0.4s;
}
.gal-item:hover { transform: translateY(-3px); }
.gal-item img { width: 100%; height: auto; display: block; transition: transform 0.6s; }
.gal-item:hover img { transform: scale(1.04); }
.gal-cap {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 16px;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.85) 100%);
  color: var(--ink); font-family: var(--sans); font-size: 12px; letter-spacing: 0.05em;
  opacity: 0; transition: opacity 0.4s;
}
.gal-item:hover .gal-cap { opacity: 1; }
@media (max-width: 900px) { .gal-grid { column-count: 2; } }
@media (max-width: 540px) { .gal-grid { column-count: 1; } }
.lightbox {
  position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 1000;
  display: none; align-items: center; justify-content: center; padding: 40px;
}
.lightbox.open { display: flex; }
.lightbox img { max-width: 100%; max-height: 100%; }
.lightbox .close {
  position: absolute; top: 24px; right: 32px; color: var(--gold);
  font-size: 32px; cursor: pointer; background: none; border: none;
}
</style>';
include __DIR__ . '/_page-header.php';
?>

<?php if (empty($photos)): ?>
  <div class="empty-state">Noch keine Fotos veröffentlicht.</div>
<?php else: ?>
  <div class="gal-grid">
    <?php foreach ($photos as $p): ?>
    <figure class="gal-item" data-src="/<?= h($p['file_path']) ?>">
      <img src="/<?= h($p['file_path']) ?>" alt="<?= h($p['caption_de'] ?: 'Foto') ?>" loading="lazy">
      <?php if ($p['caption_de']): ?>
        <figcaption class="gal-cap"><?= h($p['caption_de']) ?></figcaption>
      <?php endif; ?>
    </figure>
    <?php endforeach; ?>
  </div>

  <div class="lightbox" id="lbox">
    <button class="close" id="lclose">×</button>
    <img id="limg" src="" alt="">
  </div>
  <script>
    const lbox = document.getElementById('lbox'), limg = document.getElementById('limg');
    document.querySelectorAll('.gal-item').forEach(it => {
      it.addEventListener('click', () => { limg.src = it.dataset.src; lbox.classList.add('open'); });
    });
    document.getElementById('lclose').onclick = () => lbox.classList.remove('open');
    lbox.addEventListener('click', e => { if (e.target === lbox) lbox.classList.remove('open'); });
  </script>
<?php endif; ?>

<?php include __DIR__ . '/_page-footer.php'; ?>
