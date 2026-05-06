<?php
// _page-header.php — shared header/nav for sub-pages
require_once __DIR__ . '/_data.php';
$page_title = $page_title ?? 'Friedrich Schäfer';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?> · Friedrich Schäfer</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --gold: #c8a96e;
    --bg: #0a0a0a;
    --bg-2: #111111;
    --ink: #f4f0e8;
    --ink-dim: rgba(244,240,232,0.6);
    --serif: 'Cormorant Garamond', Georgia, serif;
    --sans: 'Inter', system-ui, sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--bg);
    color: var(--ink);
    font-family: var(--serif);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
  }
  a { color: inherit; }

  .subpage-nav {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(10,10,10,0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 22px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: var(--sans);
  }
  .subpage-nav a.brand {
    text-decoration: none;
    font-family: var(--serif);
    font-size: 18px;
    letter-spacing: 0.15em;
    color: var(--ink);
  }
  .subpage-nav a.brand em { color: var(--gold); font-style: normal; }
  .subpage-nav .back {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gold);
    padding: 10px 20px;
    border: 1px solid rgba(200,169,110,0.3);
    transition: all 0.3s;
  }
  .subpage-nav .back:hover {
    background: rgba(200,169,110,0.08);
    border-color: var(--gold);
  }

  .subpage-header {
    padding: 100px 60px 60px;
    max-width: 1300px;
    margin: 0 auto;
    text-align: center;
  }
  .subpage-label {
    display: inline-block;
    padding: 8px 16px;
    border: 1px solid rgba(200,169,110,0.3);
    color: var(--gold);
    font-family: var(--sans);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin-bottom: 28px;
  }
  .subpage-title {
    font-size: clamp(48px, 8vw, 96px);
    font-weight: 300;
    line-height: 1;
    letter-spacing: -0.02em;
    margin-bottom: 24px;
  }
  .subpage-title em { color: var(--gold); font-style: italic; }
  .subpage-sub {
    color: var(--ink-dim);
    font-size: 18px;
    max-width: 600px;
    margin: 0 auto;
  }

  .subpage-content {
    max-width: 1300px;
    margin: 0 auto;
    padding: 40px 60px 120px;
  }

  .empty-state {
    text-align: center;
    padding: 100px 20px;
    color: var(--ink-dim);
    font-family: var(--sans);
    font-size: 14px;
  }

  .subpage-footer {
    border-top: 1px solid rgba(255,255,255,0.05);
    padding: 40px 60px;
    text-align: center;
    color: var(--ink-dim);
    font-family: var(--sans);
    font-size: 11px;
    letter-spacing: 0.15em;
  }

  @media (max-width: 768px) {
    .subpage-nav { padding: 16px 20px; }
    .subpage-header { padding: 60px 20px 40px; }
    .subpage-content { padding: 20px 20px 80px; }
  }
</style>
<?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>

<nav class="subpage-nav">
  <a href="/" class="brand">Friedrich <em>Schäfer</em></a>
  <a href="/" class="back">← Zur Startseite</a>
</nav>

<header class="subpage-header">
  <div class="subpage-label"><?= h($page_label ?? '') ?></div>
  <h1 class="subpage-title"><?= $page_h1 ?? '' ?></h1>
  <?php if (!empty($page_sub)): ?>
  <p class="subpage-sub"><?= h($page_sub) ?></p>
  <?php endif; ?>
</header>

<main class="subpage-content">
