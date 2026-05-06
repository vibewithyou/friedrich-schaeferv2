<?php
require_once __DIR__ . '/_data.php';
$videos = fetch_videos(3);   // nur die 3 neuesten/featured auf Startseite
$audios = fetch_audio(4);    // 4 Hörproben
$photos = fetch_gallery(5);  // 5 Galeriebilder
$press  = fetch_press(4);
// Konzerte: nur kommende anzeigen (heute oder in Zukunft)
$today = date('Y-m-d');
$all_concerts = fetch_concerts();
$concerts = array_slice(array_filter($all_concerts, fn($c) => $c['concert_date'] >= $today), 0, 5);
$months_de_short = [1=>'Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Friedrich Schäfer — Pianist</title>
<link rel="icon" type="image/svg+xml" href="logo.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">

<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>

<style>
  :root {
    --black: #090909;
    --off-black: #111111;
    --dark: #181818;
    --mid: #2a2a2a;
    --gold: oklch(72% 0.085 72);
    --gold-dim: oklch(62% 0.065 72);
    --ivory: oklch(94% 0.018 80);
    --ivory-dim: oklch(78% 0.018 80);
    --white: #f4f0e8;
    --serif: 'Cormorant Garamond', Georgia, serif;
    --sans: 'Outfit', Helvetica Neue, Arial, sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  html { scroll-behavior: smooth; }

  body {
    background: var(--black);
    color: var(--ivory);
    font-family: var(--serif);
    overflow-x: hidden;
  }

  /* ─── NAVIGATION ─────────────────────────────────────────── */
  nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 60px;
    transition: background 0.5s, padding 0.4s;
  }

  nav.scrolled {
    background: rgba(9,9,9,0.95);
    backdrop-filter: blur(12px);
    padding: 18px 60px;
    border-bottom: 1px solid rgba(200,169,110,0.12);
  }

  .nav-logo {
    font-family: var(--serif);
    font-size: 14px;
    font-weight: 300;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--gold);
    text-decoration: none;
  }

  .nav-links {
    display: flex;
    gap: 36px;
    list-style: none;
    align-items: center;
  }

  .nav-links a {
    font-family: var(--sans);
    font-size: 11px;
    font-weight: 400;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ivory-dim);
    text-decoration: none;
    transition: color 0.3s;
    position: relative;
  }

  .nav-links a::after {
    content: '';
    position: absolute;
    bottom: -3px; left: 0;
    width: 0; height: 1px;
    background: var(--gold);
    transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .nav-links a:hover { color: var(--gold); }
  .nav-links a:hover::after { width: 100%; }

  .lang-toggle {
    display: flex;
    gap: 4px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 2px;
    padding: 4px 6px;
    cursor: pointer;
  }

  .lang-toggle button {
    font-family: var(--sans);
    font-size: 10px;
    letter-spacing: 0.1em;
    background: none;
    border: none;
    color: var(--ivory-dim);
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 1px;
    transition: all 0.2s;
  }

  .lang-toggle button.active {
    background: var(--gold);
    color: var(--black);
    font-weight: 500;
  }

  /* ─── HERO ───────────────────────────────────────────────── */
  #hero {
    position: relative;
    height: 100vh;
    min-height: 700px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    overflow: hidden;
    padding-bottom: 80px;
  }

  .hero-slides {
    position: absolute;
    inset: 0;
    z-index: 0;
  }

  .hero-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 1.6s ease;
    background-size: cover;
    background-position: center;
  }

  .hero-slide.active { opacity: 1; }

  /* Video slide */
  .hero-slide-video {
    background: #000;
  }
  .hero-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.55) contrast(1.05) saturate(0.9);
  }
  .hero-video-vignette {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
      radial-gradient(ellipse 80% 90% at 50% 50%,
        rgba(9,9,9,0) 0%,
        rgba(9,9,9,0.25) 55%,
        rgba(9,9,9,0.75) 85%,
        rgba(9,9,9,1) 100%),
      linear-gradient(to bottom,
        rgba(9,9,9,0.35) 0%,
        rgba(9,9,9,0) 30%,
        rgba(9,9,9,0) 70%,
        rgba(9,9,9,0.6) 100%);
  }

  .hero-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 4;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1px solid rgba(200,169,110,0.25);
    background: rgba(9,9,9,0.4);
    backdrop-filter: blur(6px);
    color: var(--gold);
    font-family: var(--serif);
    font-size: 28px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    opacity: 0;
    animation: fadeIn 1s ease 2.4s forwards;
  }
  .hero-arrow:hover {
    background: rgba(200,169,110,0.12);
    border-color: var(--gold);
  }
  .hero-arrow-prev { left: 32px; }
  .hero-arrow-next { right: 32px; }
  @media (max-width: 700px) {
    .hero-arrow { width: 38px; height: 38px; font-size: 22px; }
    .hero-arrow-prev { left: 12px; }
    .hero-arrow-next { right: 12px; }
  }

  /* Audio indicator */
  .hero-audio-toggle {
    position: absolute;
    bottom: 32px;
    left: 60px;
    z-index: 4;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(9,9,9,0.5);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(200,169,110,0.2);
    padding: 8px 14px;
    cursor: pointer;
    transition: all 0.3s;
    opacity: 0;
    animation: fadeIn 1s ease 2.6s forwards;
  }
  .hero-audio-toggle:hover {
    border-color: rgba(200,169,110,0.5);
    background: rgba(9,9,9,0.7);
  }
  .hero-audio-toggle .audio-bars {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 12px;
  }
  .hero-audio-toggle .audio-bars span {
    display: block;
    width: 2px;
    background: var(--gold);
    border-radius: 1px;
    animation: audioBar 1.2s ease-in-out infinite;
  }
  .hero-audio-toggle.muted .audio-bars span {
    animation: none;
    opacity: 0.4;
  }
  .hero-audio-toggle .audio-bars span:nth-child(1) { height: 4px; animation-delay: 0s; }
  .hero-audio-toggle .audio-bars span:nth-child(2) { height: 10px; animation-delay: 0.15s; }
  .hero-audio-toggle .audio-bars span:nth-child(3) { height: 6px; animation-delay: 0.3s; }
  .hero-audio-toggle .audio-bars span:nth-child(4) { height: 12px; animation-delay: 0.45s; }
  @keyframes audioBar {
    0%, 100% { transform: scaleY(0.5); }
    50% { transform: scaleY(1); }
  }
  .hero-audio-toggle .audio-label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ivory-dim);
  }

  /* Placeholder slides with atmospheric gradients */
  .hero-slide:nth-child(1) {
    background: linear-gradient(160deg, #0a0806 0%, #1a1208 40%, #0d0d0d 100%);
  }
  .hero-slide:nth-child(2) {
    background: linear-gradient(200deg, #050510 0%, #0f0d1a 40%, #080808 100%);
  }
  .hero-slide:nth-child(3) {
    background: linear-gradient(140deg, #08060a 0%, #150d10 50%, #0a0a0a 100%);
  }

  .hero-slide-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  .slide-ph-box {
    width: 320px;
    height: 460px;
    border: 1px dashed rgba(200,169,110,0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    opacity: 0.35;
  }

  .slide-ph-box span {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gold);
  }

  .slide-ph-box small {
    font-family: var(--sans);
    font-size: 9px;
    color: var(--ivory-dim);
    opacity: 0.6;
  }

  .hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
      rgba(9,9,9,0.2) 0%,
      rgba(9,9,9,0.1) 40%,
      rgba(9,9,9,0.75) 100%
    );
    z-index: 1;
  }

  .hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
  }

  .hero-title-wrapper {
    overflow: hidden;
  }

  .hero-name {
    font-family: var(--serif);
    font-size: clamp(60px, 10vw, 130px);
    font-weight: 300;
    letter-spacing: 0.06em;
    line-height: 0.92;
    color: var(--ivory);
    display: block;
    transform: translateY(100%);
    animation: slideUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
  }

  .hero-name em {
    font-style: italic;
    color: var(--gold);
  }

  .hero-subtitle {
    font-family: var(--sans);
    font-size: 11px;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--gold);
    opacity: 0;
    animation: fadeIn 1s ease 1.2s forwards;
  }

  .hero-line {
    width: 1px;
    height: 0px;
    background: var(--gold);
    margin: 0 auto;
    animation: lineGrow 1s ease 1s forwards;
  }

  .hero-quote {
    font-family: var(--serif);
    font-style: italic;
    font-size: 15px;
    color: var(--ivory-dim);
    letter-spacing: 0.03em;
    opacity: 0;
    animation: fadeIn 1s ease 1.6s forwards;
    max-width: 440px;
    line-height: 1.6;
  }

  /* Floating elements */
  .hero-floating {
    position: absolute;
    z-index: 2;
    pointer-events: none;
  }

  .hero-float-1 {
    top: 22%;
    left: 8%;
    font-family: var(--serif);
    font-size: 11px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--gold);
    opacity: 0;
    writing-mode: vertical-rl;
    animation: fadeIn 1.5s ease 2s forwards;
  }

  .hero-float-2 {
    top: 30%;
    right: 8%;
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(244,240,232,0.35);
    opacity: 0;
    animation: fadeIn 1.5s ease 2.4s forwards;
    text-align: right;
    line-height: 2;
  }

  .hero-float-3 {
    bottom: 22%;
    left: 6%;
    font-family: var(--serif);
    font-size: 42px;
    font-weight: 300;
    color: rgba(200,169,110,0.06);
    letter-spacing: -0.02em;
    opacity: 0;
    animation: fadeIn 2s ease 1.8s forwards;
  }

  .hero-dots {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    gap: 8px;
  }

  .hero-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: rgba(200,169,110,0.3);
    cursor: pointer;
    transition: background 0.3s, transform 0.3s;
    border: none;
  }

  .hero-dot.active {
    background: var(--gold);
    transform: scale(1.4);
  }

  .scroll-hint {
    position: absolute;
    bottom: 30px;
    right: 60px;
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0;
    animation: fadeIn 1s ease 3s forwards;
  }

  .scroll-hint span {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: rgba(244,240,232,0.4);
  }

  .scroll-line {
    width: 1px;
    height: 40px;
    background: linear-gradient(to bottom, rgba(200,169,110,0.4), transparent);
    animation: scrollPulse 2s ease-in-out 3s infinite;
  }

  @keyframes slideUp {
    to { transform: translateY(0); }
  }
  @keyframes fadeIn {
    to { opacity: 1; }
  }
  @keyframes lineGrow {
    to { height: 50px; }
  }
  @keyframes scrollPulse {
    0%, 100% { opacity: 0.4; transform: scaleY(1); }
    50% { opacity: 1; transform: scaleY(1.1); }
  }

  /* ─── SECTION BASE ───────────────────────────────────────── */
  section {
    padding: 120px 60px;
    max-width: 1300px;
    margin: 0 auto;
  }

  .section-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(200,169,110,0.2), transparent);
    margin: 0;
  }

  .section-label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--gold);
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 48px;
  }

  .section-label::after {
    content: '';
    display: block;
    width: 48px;
    height: 1px;
    background: var(--gold-dim);
  }

  .section-title {
    font-family: var(--serif);
    font-size: clamp(40px, 5vw, 72px);
    font-weight: 300;
    line-height: 1.1;
    letter-spacing: -0.01em;
    color: var(--ivory);
    margin-bottom: 24px;
  }

  .section-title em {
    font-style: italic;
    color: var(--gold);
  }

  /* ─── BIOGRAPHY ──────────────────────────────────────────── */
  #biography {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 100px;
    align-items: start;
    padding: 140px 60px;
    max-width: 1300px;
    margin: 0 auto;
  }

  .bio-image-col {
    position: sticky;
    top: 100px;
  }

  .bio-photo-placeholder {
    width: 100%;
    aspect-ratio: 3/4;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    position: relative;
    overflow: visible;
    background: transparent;
  }

  .bio-portrait {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* Soft organic feather into the dark background */
    -webkit-mask-image:
      radial-gradient(ellipse 95% 98% at 50% 50%, #000 65%, rgba(0,0,0,0.7) 85%, transparent 100%),
      linear-gradient(to bottom, transparent 0%, #000 7%, #000 93%, transparent 100%),
      linear-gradient(to right, transparent 0%, #000 6%, #000 94%, transparent 100%);
    -webkit-mask-composite: source-in;
            mask-image:
      radial-gradient(ellipse 95% 98% at 50% 50%, #000 65%, rgba(0,0,0,0.7) 85%, transparent 100%),
      linear-gradient(to bottom, transparent 0%, #000 7%, #000 93%, transparent 100%),
      linear-gradient(to right, transparent 0%, #000 6%, #000 94%, transparent 100%);
    mask-composite: intersect;
    filter: contrast(1.02) saturate(0.92);
  }

  .bio-portrait-glow {
    position: absolute;
    inset: -10%;
    background: radial-gradient(ellipse 50% 50% at 42% 45%, rgba(200,169,110,0.05) 0%, transparent 60%);
    pointer-events: none;
    z-index: -1;
  }

  .bio-photo-caption {
    position: absolute;
    bottom: 14px;
    left: 0;
    right: 0;
    text-align: center;
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgba(200,169,110,0.55);
    z-index: 2;
    pointer-events: none;
  }

  .ph-label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: rgba(200,169,110,0.4);
  }

  .ph-desc {
    font-family: var(--sans);
    font-size: 9px;
    color: rgba(244,240,232,0.25);
    text-align: center;
    line-height: 1.8;
  }

  .bio-text-col {}

  .bio-text p {
    font-family: var(--serif);
    font-size: 19px;
    font-weight: 300;
    line-height: 1.85;
    color: var(--ivory-dim);
    margin-bottom: 24px;
  }

  .bio-text p:first-child {
    font-size: 22px;
    color: var(--ivory);
    line-height: 1.7;
  }

  .bio-awards {
    margin-top: 48px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .award-item {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  .award-year {
    font-family: var(--sans);
    font-size: 11px;
    color: var(--gold);
    letter-spacing: 0.1em;
    min-width: 44px;
    margin-top: 3px;
  }

  .award-text {
    font-family: var(--sans);
    font-size: 13px;
    color: var(--ivory-dim);
    line-height: 1.6;
    letter-spacing: 0.02em;
  }

  /* ─── CONCERTS ───────────────────────────────────────────── */
  #concerts {
    background: var(--off-black);
    max-width: 100%;
    padding: 120px 0;
  }

  .concerts-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 60px;
  }

  .concert-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin-top: 60px;
  }

  .concert-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 40px;
    align-items: center;
    padding: 28px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    cursor: pointer;
    transition: background 0.3s;
    position: relative;
  }

  .concert-item::before {
    content: '';
    position: absolute;
    left: -60px;
    right: -60px;
    top: 0;
    bottom: 0;
    background: rgba(200,169,110,0.03);
    opacity: 0;
    transition: opacity 0.3s;
  }

  .concert-item:hover::before { opacity: 1; }

  .concert-date-block {
    text-align: right;
  }

  .concert-day {
    font-family: var(--serif);
    font-size: 36px;
    font-weight: 300;
    color: var(--gold);
    line-height: 1;
  }

  .concert-month {
    font-family: var(--sans);
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ivory-dim);
    margin-top: 4px;
  }

  .concert-info h3 {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 400;
    color: var(--ivory);
    margin-bottom: 4px;
  }

  .concert-info p {
    font-family: var(--sans);
    font-size: 12px;
    color: var(--ivory-dim);
    letter-spacing: 0.05em;
  }

  .concert-location {
    text-align: right;
  }

  .concert-city {
    font-family: var(--serif);
    font-size: 16px;
    font-weight: 300;
    color: var(--ivory-dim);
    display: block;
  }

  .concert-venue {
    font-family: var(--sans);
    font-size: 10px;
    color: rgba(244,240,232,0.35);
    letter-spacing: 0.08em;
    margin-top: 2px;
  }

  .concerts-all-link {
    margin-top: 52px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-family: var(--sans);
    font-size: 11px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--gold);
    text-decoration: none;
    transition: gap 0.3s;
  }

  .concerts-all-link:hover { gap: 20px; }

  .concerts-all-link::after {
    content: '→';
    font-size: 14px;
  }

  /* ─── VIDEOS ─────────────────────────────────────────────── */
  #videos {
    padding: 120px 60px;
    max-width: 1300px;
    margin: 0 auto;
  }

  .video-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2px;
    margin-top: 60px;
  }

  .video-item {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    background: var(--dark);
  }

  .video-item:first-child {
    grid-column: span 2;
  }

  .video-thumb {
    width: 100%;
    aspect-ratio: 16/9;
    background: linear-gradient(135deg, #111 0%, #0d0d0d 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    position: relative;
    transition: transform 0.5s ease;
  }

  .video-item:hover .video-thumb { transform: scale(1.02); }

  .video-ph-stripes {
    position: absolute;
    inset: 0;
    background-image: repeating-linear-gradient(
      -45deg,
      transparent 0px,
      transparent 18px,
      rgba(200,169,110,0.025) 18px,
      rgba(200,169,110,0.025) 19px
    );
  }

  .video-play-btn {
    width: 64px;
    height: 64px;
    border: 1px solid rgba(200,169,110,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
    transition: background 0.3s, border-color 0.3s;
  }

  .video-item:hover .video-play-btn {
    background: rgba(200,169,110,0.15);
    border-color: var(--gold);
  }

  .video-play-btn svg {
    width: 18px;
    height: 18px;
    fill: var(--gold);
    margin-left: 4px;
  }

  .video-ph-label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(200,169,110,0.35);
    position: relative;
    z-index: 1;
  }

  .video-caption {
    padding: 16px 20px;
    background: rgba(15,15,15,0.95);
  }

  .video-caption h3 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 400;
    color: var(--ivory);
    margin-bottom: 4px;
  }

  .video-caption p {
    font-family: var(--sans);
    font-size: 11px;
    color: var(--ivory-dim);
    letter-spacing: 0.05em;
  }

  /* YouTube embed placeholder */
  .youtube-placeholder {
    width: 100%;
    aspect-ratio: 16/9;
    background: #0d0d0d;
    border: 1px dashed rgba(200,169,110,0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
  }

  .youtube-placeholder .yt-icon {
    width: 52px;
    height: 36px;
    background: rgba(200,169,110,0.12);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .youtube-placeholder .yt-ph-text {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.2em;
    color: rgba(200,169,110,0.4);
    text-transform: uppercase;
  }

  /* ─── AUDIO ──────────────────────────────────────────────── */
  #audio {
    background: var(--off-black);
    max-width: 100%;
    padding: 120px 0;
  }

  .audio-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 60px;
  }

  .audio-tracks {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin-top: 60px;
  }

  /* ──────────── SEE-MORE BUTTON ──────────── */
  .see-more-wrap {
    display: flex;
    justify-content: center;
    margin-top: 60px;
  }
  .see-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    padding: 16px 32px;
    border: 1px solid rgba(200,169,110,0.4);
    color: var(--gold);
    text-decoration: none;
    font-family: var(--sans);
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    transition: all 0.4s cubic-bezier(0.2,0.8,0.2,1);
    background: transparent;
  }
  .see-more-btn:hover {
    background: rgba(200,169,110,0.08);
    border-color: var(--gold);
    transform: translateY(-2px);
  }
  .see-more-btn span {
    transition: transform 0.4s;
  }
  .see-more-btn:hover span {
    transform: translateX(6px);
  }

  .audio-track {
    display: grid;
    grid-template-columns: 48px 1fr 120px 48px;
    gap: 24px;
    align-items: center;
    padding: 22px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    cursor: pointer;
    transition: background 0.3s;
    position: relative;
  }

  .audio-track::before {
    content: '';
    position: absolute;
    left: -60px;
    right: -60px;
    top: 0;
    bottom: 0;
    background: rgba(200,169,110,0.03);
    opacity: 0;
    transition: opacity 0.3s;
  }

  .audio-track:hover::before { opacity: 1; }

  .track-num {
    font-family: var(--sans);
    font-size: 12px;
    color: rgba(244,240,232,0.2);
    text-align: center;
    letter-spacing: 0.05em;
  }

  .track-info h4 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 400;
    color: var(--ivory);
    margin-bottom: 2px;
  }

  .track-info span {
    font-family: var(--sans);
    font-size: 11px;
    color: var(--ivory-dim);
    letter-spacing: 0.05em;
  }

  .track-waveform {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 24px;
    opacity: 0.3;
  }

  .track-waveform span {
    display: block;
    width: 2px;
    background: var(--gold);
    border-radius: 1px;
  }

  .track-duration {
    font-family: var(--sans);
    font-size: 11px;
    color: rgba(244,240,232,0.3);
    text-align: right;
    letter-spacing: 0.08em;
  }

  .track-play {
    width: 32px;
    height: 32px;
    border: 1px solid rgba(200,169,110,0.3);
    border-radius: 50%;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
  }

  .track-play:hover {
    background: rgba(200,169,110,0.1);
    border-color: var(--gold);
  }

  .track-play svg {
    width: 10px;
    height: 10px;
    fill: var(--gold);
    margin-left: 2px;
  }

  .audio-ph-note {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(200,169,110,0.3);
    margin-top: 32px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .audio-ph-note::before {
    content: '';
    display: block;
    width: 24px;
    height: 1px;
    background: rgba(200,169,110,0.3);
  }

  /* ─── PRESS ──────────────────────────────────────────────── */
  #press {
    padding: 120px 60px;
    max-width: 1300px;
    margin: 0 auto;
  }

  .press-quotes {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    margin-top: 60px;
  }

  .press-quote {
    padding: 40px 36px;
    border-top: 1px solid rgba(200,169,110,0.25);
    background: linear-gradient(to bottom, rgba(200,169,110,0.025) 0%, transparent 100%);
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .press-quote blockquote {
    font-family: var(--serif);
    font-size: 19px;
    font-style: italic;
    font-weight: 300;
    line-height: 1.7;
    color: var(--ivory);
    flex: 1;
  }

  .press-source {
    font-family: var(--sans);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gold);
  }

  .press-logos {
    display: flex;
    gap: 48px;
    align-items: center;
    margin-top: 80px;
    padding-top: 48px;
    border-top: 1px solid rgba(255,255,255,0.06);
    flex-wrap: wrap;
  }

  .press-logo-ph {
    height: 24px;
    min-width: 80px;
    background: rgba(255,255,255,0.06);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .press-logo-ph span {
    font-family: var(--sans);
    font-size: 8px;
    letter-spacing: 0.15em;
    color: rgba(244,240,232,0.25);
    text-transform: uppercase;
  }

  /* ─── GALLERY ────────────────────────────────────────────── */
  #gallery {
    background: var(--off-black);
    max-width: 100%;
    padding: 120px 0;
  }

  .gallery-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 60px;
  }

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-template-rows: repeat(2, 280px);
    gap: 2px;
    margin-top: 60px;
  }

  .gallery-item {
    overflow: hidden;
    position: relative;
    cursor: pointer;
  }

  .gallery-item:nth-child(1) { grid-column: span 5; grid-row: span 2; }
  .gallery-item:nth-child(2) { grid-column: span 4; }
  .gallery-item:nth-child(3) { grid-column: span 3; }
  .gallery-item:nth-child(4) { grid-column: span 3; }
  .gallery-item:nth-child(5) { grid-column: span 4; }

  .gallery-ph {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    position: relative;
    overflow: hidden;
    transition: transform 0.5s ease;
  }

  .gallery-item:hover .gallery-ph { transform: scale(1.04); }

  .gallery-ph-stripes {
    position: absolute;
    inset: 0;
    background-image: repeating-linear-gradient(
      -45deg,
      transparent 0px,
      transparent 22px,
      rgba(200,169,110,0.02) 22px,
      rgba(200,169,110,0.02) 23px
    );
  }

  .gallery-ph:nth-child(odd) { background: #0e0e0e; }
  .gallery-ph:nth-child(even) { background: #131313; }

  .gallery-ph-text {
    position: relative;
    z-index: 1;
    font-family: var(--sans);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: rgba(200,169,110,0.25);
    text-align: center;
  }

  .gallery-ph-icon {
    position: relative;
    z-index: 1;
    width: 32px;
    height: 32px;
    opacity: 0.2;
  }

  .gallery-item:hover::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(200,169,110,0.06);
    pointer-events: none;
  }

  /* ─── CONTACT ────────────────────────────────────────────── */
  #contact {
    padding: 140px 60px;
    max-width: 1300px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 100px;
    align-items: start;
  }

  .contact-left h2 {
    font-family: var(--serif);
    font-size: clamp(42px, 5vw, 68px);
    font-weight: 300;
    line-height: 1.1;
    color: var(--ivory);
    margin-bottom: 32px;
  }

  .contact-left h2 em {
    font-style: italic;
    color: var(--gold);
  }

  .contact-left p {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 300;
    line-height: 1.8;
    color: var(--ivory-dim);
    margin-bottom: 20px;
  }

  .contact-blocks {
    display: flex;
    flex-direction: column;
    gap: 32px;
    margin-top: 52px;
  }

  .contact-block-label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 8px;
  }

  .contact-block-text {
    font-family: var(--serif);
    font-size: 17px;
    font-weight: 300;
    color: var(--ivory);
    line-height: 1.7;
  }

  .contact-block-text a {
    color: var(--ivory);
    text-decoration: none;
    border-bottom: 1px solid rgba(200,169,110,0.3);
    transition: border-color 0.3s, color 0.3s;
  }

  .contact-block-text a:hover {
    color: var(--gold);
    border-color: var(--gold);
  }

  .contact-right {}

  .contact-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .form-field label {
    font-family: var(--sans);
    font-size: 9px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--gold-dim);
  }

  .form-field input,
  .form-field textarea,
  .form-field select {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0;
    color: var(--ivory);
    font-family: var(--serif);
    font-size: 16px;
    font-weight: 300;
    padding: 14px 18px;
    outline: none;
    transition: border-color 0.3s;
    width: 100%;
  }

  .form-field input:focus,
  .form-field textarea:focus {
    border-color: rgba(200,169,110,0.5);
    background: rgba(200,169,110,0.02);
  }

  .form-field textarea {
    height: 140px;
    resize: vertical;
    line-height: 1.6;
  }

  .form-submit {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: none;
    border: 1px solid rgba(200,169,110,0.5);
    color: var(--gold);
    font-family: var(--sans);
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    padding: 16px 36px;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 8px;
  }

  .form-submit:hover {
    background: rgba(200,169,110,0.08);
    border-color: var(--gold);
    gap: 20px;
  }

  /* ─── FOOTER ─────────────────────────────────────────────── */
  footer {
    background: var(--black);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 60px;
    max-width: 100%;
  }

  .footer-inner {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
  }

  .footer-name {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 300;
    color: var(--gold);
    letter-spacing: 0.06em;
  }

  .footer-social {
    display: flex;
    gap: 28px;
    list-style: none;
  }

  .footer-social a {
    font-family: var(--sans);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--ivory-dim);
    text-decoration: none;
    transition: color 0.3s;
  }

  .footer-social a:hover { color: var(--gold); }

  .footer-copy {
    font-family: var(--sans);
    font-size: 10px;
    color: rgba(244,240,232,0.2);
    letter-spacing: 0.05em;
  }

  /* ─── SCROLL ANIMATIONS ─────────────────────────────────── */

  /* Base reveal state */
  .reveal {
    opacity: 0;
    transform: translateY(36px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .reveal.visible {
    opacity: 1;
    transform: translateY(0);
  }

  /* Slide from left */
  .reveal-left {
    opacity: 0;
    transform: translateX(-40px);
    transition: opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .reveal-left.visible {
    opacity: 1;
    transform: translateX(0);
  }

  /* Slide from right */
  .reveal-right {
    opacity: 0;
    transform: translateX(40px);
    transition: opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .reveal-right.visible {
    opacity: 1;
    transform: translateX(0);
  }

  /* Scale reveal */
  .reveal-scale {
    opacity: 0;
    transform: scale(0.96);
    transition: opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .reveal-scale.visible {
    opacity: 1;
    transform: scale(1);
  }

  /* Stagger delays */
  .stagger-1 { transition-delay: 0.05s; }
  .stagger-2 { transition-delay: 0.12s; }
  .stagger-3 { transition-delay: 0.19s; }
  .stagger-4 { transition-delay: 0.26s; }
  .stagger-5 { transition-delay: 0.33s; }

  /* Section title word-by-word reveal */
  .title-word {
    display: inline-block;
    overflow: hidden;
    vertical-align: bottom;
  }
  .title-word-inner {
    display: inline-block;
    transform: translateY(110%);
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .title-reveal-visible .title-word-inner {
    transform: translateY(0);
  }

  /* Cursor glow */
  #cursor-glow {
    position: fixed;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,169,110,0.06) 0%, transparent 70%);
    pointer-events: none;
    z-index: 9999;
    transform: translate(-50%, -50%);
    transition: opacity 0.3s;
    opacity: 0;
  }

  /* Section divider animated */
  .section-divider {
    transform-origin: left;
    transform: scaleX(0);
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .section-divider.visible {
    transform: scaleX(1);
  }

  /* Concert item hover */
  .concert-item {
    transition: padding-left 0.3s ease;
  }
  .concert-item:hover {
    padding-left: 8px;
  }

  /* Press quote hover lift */
  .press-quote {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 0.4s ease;
  }
  .press-quote:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }

  /* Gallery item overlay reveal */
  .gallery-item::after {
    transition: opacity 0.4s ease !important;
  }

  /* Audio track hover */
  .audio-track .track-waveform {
    transition: opacity 0.3s;
  }
  .audio-track:hover .track-waveform {
    opacity: 1 !important;
  }

  /* Video item transition */
  .video-thumb {
    overflow: hidden;
  }

  /* Hero parallax layer */
  .hero-parallax {
    will-change: transform;
  }

  /* Line draw on section labels */
  .section-label::after {
    width: 0 !important;
    transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.4s;
  }
  .section-label.line-visible::after {
    width: 48px !important;
  }

  /* Gold line under bio */
  .bio-awards .award-item {
    opacity: 0;
    transform: translateX(-16px);
    transition: opacity 0.6s ease, transform 0.6s ease;
  }
  .bio-awards.visible .award-item:nth-child(1) { opacity: 1; transform: none; transition-delay: 0.1s; }
  .bio-awards.visible .award-item:nth-child(2) { opacity: 1; transform: none; transition-delay: 0.22s; }

  /* ─── MOBILE MENU (hamburger + side drawer) ────────────── */
  .mobile-menu-btn {
    display: none;
    position: relative;
    width: 36px;
    height: 36px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 1100;
  }
  .mobile-menu-btn span {
    position: absolute;
    left: 8px;
    right: 8px;
    height: 1px;
    background: var(--gold);
    transition: transform 0.35s ease, opacity 0.25s ease, top 0.35s ease;
  }
  .mobile-menu-btn span:nth-child(1) { top: 13px; }
  .mobile-menu-btn span:nth-child(2) { top: 18px; width: 14px; left: 14px; }
  .mobile-menu-btn span:nth-child(3) { top: 23px; }
  .mobile-menu-btn.open span:nth-child(1) { top: 18px; transform: rotate(45deg); }
  .mobile-menu-btn.open span:nth-child(2) { opacity: 0; }
  .mobile-menu-btn.open span:nth-child(3) { top: 18px; transform: rotate(-45deg); }

  .mobile-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(82vw, 340px);
    background: rgba(8, 8, 8, 0.96);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-left: 1px solid rgba(200, 169, 110, 0.15);
    transform: translateX(100%);
    transition: transform 0.45s cubic-bezier(0.5, 0, 0.2, 1);
    z-index: 1050;
    padding: 100px 36px 36px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    pointer-events: none;
  }
  .mobile-drawer.open {
    transform: translateX(0);
    pointer-events: auto;
  }
  .mobile-drawer a {
    color: var(--cream);
    text-decoration: none;
    font-family: 'Cormorant Garamond', serif;
    font-weight: 300;
    font-size: 22px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    letter-spacing: 0.5px;
    transition: color 0.25s ease, padding-left 0.3s ease;
    opacity: 0;
    transform: translateX(20px);
  }
  .mobile-drawer.open a {
    opacity: 1;
    transform: none;
    transition: opacity 0.4s ease, transform 0.4s ease, color 0.25s ease, padding-left 0.3s ease;
  }
  .mobile-drawer.open a:nth-child(1) { transition-delay: 0.10s; }
  .mobile-drawer.open a:nth-child(2) { transition-delay: 0.15s; }
  .mobile-drawer.open a:nth-child(3) { transition-delay: 0.20s; }
  .mobile-drawer.open a:nth-child(4) { transition-delay: 0.25s; }
  .mobile-drawer.open a:nth-child(5) { transition-delay: 0.30s; }
  .mobile-drawer.open a:nth-child(6) { transition-delay: 0.35s; }
  .mobile-drawer.open a:nth-child(7) { transition-delay: 0.40s; }
  .mobile-drawer a:hover, .mobile-drawer a:active {
    color: var(--gold);
    padding-left: 6px;
  }
  .mobile-drawer-foot {
    margin-top: auto;
    display: flex;
    gap: 8px;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.4s ease 0.5s, transform 0.4s ease 0.5s;
  }
  .mobile-drawer.open .mobile-drawer-foot {
    opacity: 1;
    transform: none;
  }
  .mobile-drawer-foot button {
    flex: 1;
    background: transparent;
    border: 1px solid rgba(200, 169, 110, 0.3);
    color: var(--cream);
    padding: 10px 0;
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease;
  }
  .mobile-drawer-foot button.active {
    background: rgba(200, 169, 110, 0.12);
    color: var(--gold);
  }

  .mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
    z-index: 1040;
  }
  .mobile-backdrop.open {
    opacity: 1;
    pointer-events: auto;
  }

  /* ─── MOBILE ─────────────────────────────────────────────── */
  @media (max-width: 900px) {
    nav { padding: 20px 24px; }
    nav.scrolled { padding: 14px 24px; }
    .nav-links { display: none; }
    .lang-toggle { display: none; }
    .mobile-menu-btn { display: block; }

    section { padding: 80px 24px; }

    #biography {
      grid-template-columns: 1fr;
      gap: 48px;
      padding: 80px 24px;
    }
    .bio-image-col { position: static; }

    .press-quotes { grid-template-columns: 1fr; }

    #contact {
      grid-template-columns: 1fr;
      gap: 60px;
      padding: 80px 24px;
    }

    .gallery-grid {
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto;
    }

    .gallery-item { grid-column: span 1 !important; grid-row: span 1 !important; }
    .gallery-item .gallery-ph { aspect-ratio: 4/3; height: auto; }

    #concerts, #audio, #gallery {
      padding: 80px 0;
    }

    .concerts-inner, .audio-inner, .gallery-inner {
      padding: 0 24px;
    }

    .concert-item {
      grid-template-columns: 60px 1fr;
    }

    .concert-location { display: none; }

    .footer-inner { flex-direction: column; text-align: center; }

    .video-grid { grid-template-columns: 1fr; }
    .video-item:first-child { grid-column: span 1; }
  }
</style>
</head>
<body>

<!-- Cursor glow -->
<div id="cursor-glow"></div>

<!-- ──────────── NAVIGATION ──────────── -->
<nav id="main-nav">
  <a href="#" class="nav-logo">Friedrich Schäfer</a>
  <ul class="nav-links" id="nav-links">
    <li><a href="#biography" data-de="Biografie" data-en="Biography">Biografie</a></li>
    <li><a href="#concerts" data-de="Konzerte" data-en="Concerts">Konzerte</a></li>
    <li><a href="#videos" data-de="Videos" data-en="Videos">Videos</a></li>
    <li><a href="#audio" data-de="Hörproben" data-en="Audio">Hörproben</a></li>
    <li><a href="#press" data-de="Presse" data-en="Press">Presse</a></li>
    <li><a href="#gallery" data-de="Galerie" data-en="Gallery">Galerie</a></li>
    <li><a href="#contact" data-de="Kontakt" data-en="Contact">Kontakt</a></li>
  </ul>
  <div class="lang-toggle">
    <button class="active" id="btn-de">DE</button>
    <button id="btn-en">EN</button>
  </div>
  <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menü">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-backdrop" id="mobile-backdrop"></div>
<aside class="mobile-drawer" id="mobile-drawer">
  <a href="#biography" data-de="Biografie" data-en="Biography">Biografie</a>
  <a href="#concerts" data-de="Konzerte" data-en="Concerts">Konzerte</a>
  <a href="#videos" data-de="Videos" data-en="Videos">Videos</a>
  <a href="#audio" data-de="Hörproben" data-en="Audio">Hörproben</a>
  <a href="#press" data-de="Presse" data-en="Press">Presse</a>
  <a href="#gallery" data-de="Galerie" data-en="Gallery">Galerie</a>
  <a href="#contact" data-de="Kontakt" data-en="Contact">Kontakt</a>
  <div class="mobile-drawer-foot">
    <button class="active" id="m-btn-de">DE</button>
    <button id="m-btn-en">EN</button>
  </div>
</aside>

<!-- ──────────── HERO ──────────── -->
<section id="hero">
  <div class="hero-slides" id="hero-slides">
    <!-- Slide 1 — Hero video -->
    <div class="hero-slide active hero-slide-video">
      <video id="hero-video"
             class="hero-video"
             src="hero-video.mp4"
             playsinline
             muted
             preload="auto"></video>
      <div class="hero-video-vignette"></div>
    </div>
    <!-- Slide 2 -->
    <div class="hero-slide">
      <div class="hero-slide-placeholder">
        <div class="slide-ph-box">
          <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
            <rect x="2" y="14" width="36" height="20" rx="1" stroke="rgba(200,169,110,0.4)" fill="none"/>
            <rect x="8" y="8" width="5" height="10" rx="1" fill="rgba(200,169,110,0.2)"/>
            <rect x="16" y="8" width="5" height="10" rx="1" fill="rgba(200,169,110,0.2)"/>
            <rect x="24" y="8" width="5" height="10" rx="1" fill="rgba(200,169,110,0.2)"/>
          </svg>
          <span>Flügel Foto</span>
          <small>Konzertflügel · Atmosphärisch</small>
        </div>
      </div>
    </div>
    <!-- Slide 3 -->
    <div class="hero-slide">
      <div class="hero-slide-placeholder">
        <div class="slide-ph-box">
          <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
            <rect x="1" y="1" width="38" height="38" rx="2" stroke="rgba(200,169,110,0.4)" fill="none"/>
            <path d="M8 32 L8 20 L15 25 L22 15 L29 22 L36 32" stroke="rgba(200,169,110,0.4)" fill="none"/>
          </svg>
          <span>Konzert Foto</span>
          <small>Live Auftritt · Konzertsaal</small>
        </div>
      </div>
    </div>
  </div>

  <div class="hero-overlay"></div>

  <!-- Floating decorative elements -->
  <div class="hero-floating hero-float-1" id="hero-float-1">Pianist</div>
  <div class="hero-floating hero-float-2" id="hero-float-2">
    Carl Bechstein<br>Wettbewerb<br>2014 · 2017
  </div>
  <div class="hero-floating hero-float-3">FS</div>

  <!-- Main name -->
  <div class="hero-content" id="hero-content">
    <div class="hero-title-wrapper">
      <span class="hero-name" id="hero-name">Friedrich <em>Schäfer</em></span>
    </div>
    <div class="hero-line"></div>
    <div class="hero-subtitle" data-de="Pianist" data-en="Pianist">Pianist</div>
    <div class="hero-quote" id="hero-quote">
      <span data-de="Musik, die erzählt — klar, lebendig, klassisch." data-en="Music that speaks — clear, vivid, classical.">Musik, die erzählt — klar, lebendig, klassisch.</span>
    </div>
  </div>

  <!-- Slide nav arrows -->
  <button class="hero-arrow hero-arrow-prev" id="hero-prev" aria-label="Previous">‹</button>
  <button class="hero-arrow hero-arrow-next" id="hero-next" aria-label="Next">›</button>

  <!-- Slide dots -->
  <div class="hero-dots" id="hero-dots"></div>

  <!-- Audio toggle -->
  <button class="hero-audio-toggle" id="hero-audio-toggle" aria-label="Toggle audio">
    <div class="audio-bars">
      <span></span><span></span><span></span><span></span>
    </div>
    <span class="audio-label" id="hero-audio-label" data-de="Ton aus" data-en="Sound off">Ton aus</span>
  </button>

  <!-- Scroll hint -->
  <div class="scroll-hint">
    <span data-de="Entdecken" data-en="Explore">Entdecken</span>
    <div class="scroll-line"></div>
  </div>
</section>

<div class="section-divider"></div>

<!-- ──────────── BIOGRAPHY ──────────── -->
<div id="biography">
  <div class="bio-image-col reveal-left">
    <div class="bio-photo-placeholder">
      <div class="bio-portrait-glow"></div>
      <img class="bio-portrait" src="portrait.jpg" alt="Friedrich Schäfer Portrait" loading="lazy">
      <div class="bio-photo-caption" data-de="Friedrich Schäfer · Karlsbad" data-en="Friedrich Schäfer · Karlovy Vary">Friedrich Schäfer · Karlsbad</div>
    </div>
  </div>
  <div class="bio-text-col">
    <div class="section-label">
      <span data-de="Biografie" data-en="Biography">Biografie</span>
    </div>
    <h2 class="section-title" data-de="Junger Konzert&shy;pianist aus <em>Mittelsachsen</em>" data-en="A young concert pianist from <em>Saxony</em>">
      Junger Konzert&shy;pianist aus <em>Mittelsachsen</em>
    </h2>
    <div class="bio-text" id="bio-text">
      <p data-de="Friedrich Schäfer ist ein klassischer Pianist aus Mittelsachsen mit einer außergewöhnlichen musikalischen Reife und einem breiten Repertoire, das von der Wiener Klassik bis zur romantischen Klavierliteratur reicht." data-en="Friedrich Schäfer is a classical pianist from central Saxony with exceptional musical maturity and a broad repertoire spanning from the Viennese classical era to romantic piano literature.">
        Friedrich Schäfer ist ein klassischer Pianist aus Mittelsachsen mit einer außergewöhnlichen musikalischen Reife und einem breiten Repertoire, das von der Wiener Klassik bis zur romantischen Klavierliteratur reicht.
      </p>
      <p data-de="Sein Spiel verbindet pianistische Präzision mit lyrischer Tiefe und erzählerischer Energie — Qualitäten, die bereits in seiner frühen Wettbewerbsbiografie sichtbar werden. Beim renommierten Carl-Bechstein-Wettbewerb wurde er sowohl 2014 als auch 2017 ausgezeichnet." data-en="His playing combines pianistic precision with lyrical depth and narrative energy — qualities already visible in his early competition biography. He received prizes at the prestigious Carl Bechstein Competition in both 2014 and 2017.">
        Sein Spiel verbindet pianistische Präzision mit lyrischer Tiefe und erzählerischer Energie — Qualitäten, die bereits in seiner frühen Wettbewerbsbiografie sichtbar werden. Beim renommierten Carl-Bechstein-Wettbewerb wurde er sowohl 2014 als auch 2017 ausgezeichnet.
      </p>
      <p data-de="Sein Repertoire umfasst charaktervolle Miniaturen ebenso wie große Konzertwerke. Komponisten wie Edvard Grieg, Carl Reinecke, Valery Gavrilin und Daniel Gottlob Türk prägen seinen musikalischen Weg — Musik mit Szene, Bewegung und atmosphärischem Klang." data-en="His repertoire encompasses expressive miniatures as well as major concert works. Composers such as Edvard Grieg, Carl Reinecke, Valery Gavrilin and Daniel Gottlob Türk shape his musical path — music with scene, movement and atmospheric sound.">
        Sein Repertoire umfasst charaktervolle Miniaturen ebenso wie große Konzertwerke. Komponisten wie Edvard Grieg, Carl Reinecke, Valery Gavrilin und Daniel Gottlob Türk prägen seinen musikalischen Weg — Musik mit Szene, Bewegung und atmosphärischem Klang.
      </p>
    </div>
    <div class="bio-awards">
      <div class="award-item">
        <div class="award-year">2017</div>
        <div class="award-text" data-de="Bärenreiter-Preis, Carl-Bechstein-Klavierwettbewerb · Altersgruppe II" data-en="Bärenreiter Prize, Carl Bechstein Piano Competition · Age Group II">Bärenreiter-Preis, Carl-Bechstein-Klavierwettbewerb · Altersgruppe II</div>
      </div>
      <div class="award-item">
        <div class="award-year">2014</div>
        <div class="award-text" data-de="Bärenreiter-Preis, Carl-Bechstein-Klavierduowettbewerb · Altersgruppe I" data-en="Bärenreiter Prize, Carl Bechstein Piano Duo Competition · Age Group I">Bärenreiter-Preis, Carl-Bechstein-Klavierduowettbewerb · Altersgruppe I</div>
      </div>
    </div>
  </div>
</div>

<div class="section-divider"></div>

<!-- ──────────── CONCERTS ──────────── -->
<div id="concerts">
  <div class="concerts-inner">
    <div class="section-label">
      <span data-de="Konzerte" data-en="Concerts">Konzerte</span>
    </div>
    <h2 class="section-title" data-de="Kommende <em>Auftritte</em>" data-en="Upcoming <em>Performances</em>">Kommende <em>Auftritte</em></h2>

    <div class="concert-list" id="concert-list">
      <?php if (empty($concerts)): ?>
        <p style="text-align:center; opacity:0.4; font-family:var(--sans); font-size:13px;" data-de="Noch keine Konzerttermine veröffentlicht." data-en="No concerts published yet.">Noch keine Konzerttermine veröffentlicht.</p>
      <?php else: foreach ($concerts as $i => $c):
        $t = strtotime($c['concert_date']);
        $day = date('d', $t);
        $mon = $months_de_short[(int)date('n', $t)];
      ?>
        <div class="concert-item reveal" style="transition-delay:<?= $i * 0.07 ?>s">
          <div class="concert-date-block">
            <div class="concert-day"><?= $day ?></div>
            <div class="concert-month"><?= $mon ?></div>
          </div>
          <div class="concert-info">
            <h3 data-de="<?= h($c['title_de']) ?>" data-en="<?= h($c['title_en'] ?: $c['title_de']) ?>"><?= h($c['title_de']) ?></h3>
            <?php if ($c['program_de']): ?>
            <p data-de="<?= h($c['program_de']) ?>" data-en="<?= h($c['program_en'] ?: $c['program_de']) ?>"><?= h($c['program_de']) ?></p>
            <?php endif; ?>
          </div>
          <div class="concert-location">
            <span class="concert-city"><?= h($c['city']) ?></span>
            <div class="concert-venue"><?= h($c['venue']) ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <a href="/konzerte.php" class="concerts-all-link">
      <span data-de="Alle Konzerte" data-en="All Concerts">Alle Konzerte</span>
    </a>
  </div>
</div>

<div class="section-divider"></div>

<!-- ──────────── VIDEOS ──────────── -->
<section id="videos">
  <div class="section-label">
    <span data-de="Videos" data-en="Videos">Videos</span>
  </div>
  <h2 class="section-title" data-de="Film &amp; <em>Konzertmitschnitte</em>" data-en="Film &amp; <em>Concert Recordings</em>">Film &amp; <em>Konzertmitschnitte</em></h2>

  <div class="video-grid">
    <?php if (empty($videos)): ?>
      <p style="grid-column:1/-1; text-align:center; opacity:0.4; font-family:var(--sans); font-size:13px;" data-de="Noch keine Videos veröffentlicht." data-en="No videos published yet.">Noch keine Videos veröffentlicht.</p>
    <?php else: foreach ($videos as $i => $v): ?>
      <div class="video-item">
        <?php if ($i === 0): ?>
        <div class="video-thumb video-thumb-large" data-video="<?= h($v['file_path']) ?>"<?php if ($v['poster_path']): ?> style="background-image:url('/<?= h($v['poster_path']) ?>'); background-size:cover; background-position:center;"<?php endif; ?>>
          <div class="video-ph-stripes"></div>
          <div class="video-play-btn"><svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg></div>
        </div>
        <?php else: ?>
        <div class="video-thumb" data-video="<?= h($v['file_path']) ?>"<?php if ($v['poster_path']): ?> style="background-image:url('/<?= h($v['poster_path']) ?>'); background-size:cover; background-position:center;"<?php endif; ?>>
          <div class="video-ph-stripes"></div>
          <div class="video-play-btn"><svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg></div>
        </div>
        <?php endif; ?>
        <div class="video-caption">
          <h3 data-de="<?= h($v['title_de']) ?>" data-en="<?= h($v['title_en'] ?: $v['title_de']) ?>"><?= h($v['title_de']) ?></h3>
          <?php if ($v['caption_de']): ?>
          <p data-de="<?= h($v['caption_de']) ?>" data-en="<?= h($v['caption_en'] ?: $v['caption_de']) ?>"><?= h($v['caption_de']) ?><?= $v['duration'] ? ' · ' . h($v['duration']) : '' ?></p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <?php if (count($videos) >= 3): ?>
  <div class="see-more-wrap">
    <a href="/videos.php" class="see-more-btn" data-de="Alle Videos ansehen" data-en="See all videos">Alle Videos ansehen <span>→</span></a>
  </div>
  <?php endif; ?>
</section>

<div class="section-divider"></div>

<!-- ──────────── AUDIO ──────────── -->
<div id="audio">
  <div class="audio-inner">
    <div class="section-label">
      <span data-de="Hörproben" data-en="Audio Samples">Hörproben</span>
    </div>
    <h2 class="section-title" data-de="<em>Klang</em> erleben" data-en="Experience the <em>Sound</em>"><em>Klang</em> erleben</h2>

    <div class="audio-tracks" id="audio-tracks">
      <?php if (empty($audios)): ?>
        <div class="audio-ph-note"><span data-de="Noch keine Hörproben veröffentlicht." data-en="No audio samples published yet.">Noch keine Hörproben veröffentlicht.</span></div>
      <?php else: foreach ($audios as $i => $a): ?>
        <div class="audio-track reveal" style="transition-delay:<?= $i * 0.08 ?>s" data-audio="/<?= h($a['file_path']) ?>">
          <div class="track-num"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
          <div class="track-info">
            <h4 data-de="<?= h($a['title_de']) ?>" data-en="<?= h($a['title_en'] ?: $a['title_de']) ?>"><?= h($a['title_de']) ?></h4>
            <span data-de="<?= h($a['composer_de']) ?>" data-en="<?= h($a['composer_en'] ?: $a['composer_de']) ?>"><?= h($a['composer_de']) ?></span>
          </div>
          <div class="track-waveform">
            <?php for ($w = 0; $w < 20; $w++): $h = 8 + (($i*7 + $w*3) % 16); ?>
              <span style="height:<?= $h ?>px"></span>
            <?php endfor; ?>
          </div>
          <div class="track-duration"><?= h($a['duration'] ?: '') ?></div>
          <button class="track-play" type="button"><svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg></button>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <?php if (count($audios) >= 4): ?>
    <div class="see-more-wrap">
      <a href="/hoerproben.php" class="see-more-btn" data-de="Alle Hörproben anhören" data-en="Listen to all audio samples">Alle Hörproben anhören <span>→</span></a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="section-divider"></div>

<!-- ──────────── PRESS ──────────── -->
<section id="press">
  <div class="section-label">
    <span data-de="Presse" data-en="Press">Presse</span>
  </div>
  <h2 class="section-title" data-de="Was man <em>sagt</em>" data-en="What they <em>say</em>">Was man <em>sagt</em></h2>

  <div class="press-quotes">
    <?php if (empty($press)): ?>
      <p style="opacity:0.4; text-align:center; font-family:var(--sans); font-size:13px;" data-de="Noch keine Pressestimmen veröffentlicht." data-en="No press quotes yet.">Noch keine Pressestimmen veröffentlicht.</p>
    <?php else: foreach ($press as $pq): ?>
    <div class="press-quote">
      <blockquote data-de="&ldquo;<?= h($pq['quote_de']) ?>&rdquo;" data-en="&ldquo;<?= h($pq['quote_en'] ?: $pq['quote_de']) ?>&rdquo;">
        &ldquo;<?= h($pq['quote_de']) ?>&rdquo;
      </blockquote>
      <div class="press-source"><?= h($pq['source'] ?: '—') ?></div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="press-logos">
    <div class="press-logo-ph"><span>Zeitung</span></div>
    <div class="press-logo-ph"><span>Magazin</span></div>
    <div class="press-logo-ph"><span>Radio</span></div>
    <div class="press-logo-ph"><span>Online</span></div>
    <div class="press-logo-ph"><span>Programm</span></div>
  </div>
</section>

<div class="section-divider"></div>

<!-- ──────────── GALLERY ──────────── -->
<div id="gallery">
  <div class="gallery-inner">
    <div class="section-label">
      <span data-de="Galerie" data-en="Gallery">Galerie</span>
    </div>
    <h2 class="section-title" data-de="Foto<em>galerie</em>" data-en="Photo<em>gallery</em>">Foto<em>galerie</em></h2>

    <div class="gallery-grid">
      <?php if (empty($photos)): ?>
        <p style="grid-column:1/-1; text-align:center; opacity:0.4; font-family:var(--sans); font-size:13px;" data-de="Noch keine Fotos veröffentlicht." data-en="No photos published yet.">Noch keine Fotos veröffentlicht.</p>
      <?php else: foreach ($photos as $i => $p): ?>
        <div class="gallery-item">
          <div class="gallery-ph" style="background:#0d0d0d; padding: 0;">
            <img src="/<?= h($p['file_path']) ?>" alt="<?= h($p['caption_de'] ?: 'Foto') ?>"
                 style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0; transition: transform 0.7s ease;">
            <div style="position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,0.05) 0%, rgba(0,0,0,0.55) 100%); z-index:1;"></div>
            <?php if ($p['caption_de']): ?>
            <div class="gallery-ph-text" data-de="<?= h($p['caption_de']) ?>" data-en="<?= h($p['caption_en'] ?: $p['caption_de']) ?>" style="position:absolute; bottom:18px; left:18px; right:18px; z-index:2; text-align:left; font-size: 11px;"><?= h($p['caption_de']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <?php if (count($photos) >= 5): ?>
    <div class="see-more-wrap">
      <a href="/galerie.php" class="see-more-btn" data-de="Gesamte Galerie ansehen" data-en="View full gallery">Gesamte Galerie ansehen <span>→</span></a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="section-divider"></div>

<!-- ──────────── CONTACT ──────────── -->
<div id="contact">
  <div class="contact-left">
    <div class="section-label">
      <span data-de="Kontakt" data-en="Contact">Kontakt</span>
    </div>
    <h2>
      <span data-de="Booking &amp; <em>Anfragen</em>" data-en="Booking &amp; <em>Enquiries</em>">Booking &amp; <em>Anfragen</em></span>
    </h2>
    <p data-de="Für Konzertanfragen, Engagements und alle weiteren Anliegen stehen wir Ihnen gerne zur Verfügung." data-en="For concert enquiries, engagements and all other matters, we are happy to assist you.">
      Für Konzertanfragen, Engagements und alle weiteren Anliegen stehen wir Ihnen gerne zur Verfügung.
    </p>

    <div class="contact-blocks">
      <div>
        <div class="contact-block-label" data-de="E-Mail" data-en="Email">E-Mail</div>
        <div class="contact-block-text">
          <a href="mailto:kontakt@fjs-pianist.de">kontakt@fjs-pianist.de</a>
        </div>
      </div>
      <div>
        <div class="contact-block-label" data-de="Management" data-en="Management">Management</div>
        <div class="contact-block-text">
          <a href="mailto:management@fjs-pianist.de">management@fjs-pianist.de</a>
        </div>
      </div>
      <div>
        <div class="contact-block-label" data-de="Social Media" data-en="Social Media">Social Media</div>
        <div class="contact-block-text">
          <a href="#">Instagram</a> &nbsp;·&nbsp; <a href="#">YouTube</a> &nbsp;·&nbsp; <a href="#">Facebook</a>
        </div>
      </div>
    </div>
  </div>

  <div class="contact-right">
    <form class="contact-form" onsubmit="return false;">
      <div class="form-field">
        <label data-de="Ihr Name" data-en="Your Name">Ihr Name</label>
        <input type="text" placeholder="">
      </div>
      <div class="form-field">
        <label data-de="E-Mail-Adresse" data-en="Email Address">E-Mail-Adresse</label>
        <input type="email" placeholder="">
      </div>
      <div class="form-field">
        <label data-de="Betreff" data-en="Subject">Betreff</label>
        <input type="text" placeholder="">
      </div>
      <div class="form-field">
        <label data-de="Ihre Nachricht" data-en="Your Message">Ihre Nachricht</label>
        <textarea placeholder=""></textarea>
      </div>
      <button class="form-submit" type="submit">
        <span data-de="Nachricht senden" data-en="Send Message">Nachricht senden</span>
        <span>→</span>
      </button>
    </form>
  </div>
</div>

<!-- ──────────── FOOTER ──────────── -->
<footer>
  <div class="footer-inner">
    <div class="footer-name">Friedrich Schäfer</div>
    <ul class="footer-social">
      <li><a href="#" data-de="Instagram" data-en="Instagram">Instagram</a></li>
      <li><a href="#" data-de="YouTube" data-en="YouTube">YouTube</a></li>
      <li><a href="#" data-de="Facebook" data-en="Facebook">Facebook</a></li>
    </ul>
    <div class="footer-copy">
      <span data-de="© 2026 Friedrich Schäfer" data-en="© 2026 Friedrich Schäfer">© 2026 Friedrich Schäfer</span>
      &nbsp;·&nbsp;
      <a href="impressum.html" style="color:rgba(244,240,232,0.2);text-decoration:none;border-bottom:1px solid rgba(200,169,110,0.15);transition:color 0.3s;" onmouseover="this.style.color='oklch(72% 0.085 72)'" onmouseout="this.style.color='rgba(244,240,232,0.2)'" data-de="Impressum" data-en="Imprint">Impressum</a>
      &nbsp;·&nbsp;
      <a href="datenschutz.html" style="color:rgba(244,240,232,0.2);text-decoration:none;border-bottom:1px solid rgba(200,169,110,0.15);transition:color 0.3s;" onmouseover="this.style.color='oklch(72% 0.085 72)'" onmouseout="this.style.color='rgba(244,240,232,0.2)'" data-de="Datenschutz" data-en="Privacy">Datenschutz</a>
    </div>
  </div>
</footer>

<!-- ──────────── REACT TWEAKS ──────────── -->
<script type="text/babel" src="tweaks-panel.jsx"></script>

<script>
// Tweak defaults (host reads/writes this block on disk)
const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "colorScheme": "gold-black",
  "heroFont": "cormorant",
  "showFloatingElements": true,
  "accentColor": "gold"
}/*EDITMODE-END*/;

// Mount is defined inside tweaks-panel.jsx (same Babel scope as TweaksPanel/useTweaks)
function waitAndMount() {
  if (typeof window.mountFriedrichTweaks === 'function') {
    window.mountFriedrichTweaks();
  } else {
    setTimeout(waitAndMount, 50);
  }
}
waitAndMount();
</script>

<div id="tweaks-root"></div>

<script>
// ──────────── INTERSECTION OBSERVER (global, used by render fns) ────────────
const io = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el = entry.target;
    if (el.classList.contains('reveal') ||
        el.classList.contains('reveal-left') ||
        el.classList.contains('reveal-right') ||
        el.classList.contains('reveal-scale')) {
      el.classList.add('visible');
    }
    if (el.classList.contains('section-divider')) el.classList.add('visible');
    if (el.classList.contains('section-label'))   el.classList.add('line-visible');
    if (el.classList.contains('bio-awards'))       el.classList.add('visible');
    if (el.classList.contains('section-title')) {
      wrapTitleWords(el);
      requestAnimationFrame(() => el.classList.add('title-reveal-visible'));
    }
    io.unobserve(el);
  });
}, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

// ──────────── HERO SLIDESHOW ────────────
const slides = document.querySelectorAll('.hero-slide');
const dotsContainer = document.getElementById('hero-dots');
let currentSlide = 0;

slides.forEach((_, i) => {
  const dot = document.createElement('button');
  dot.className = 'hero-dot' + (i === 0 ? ' active' : '');
  dot.addEventListener('click', () => goToSlide(i));
  dotsContainer.appendChild(dot);
});

function goToSlide(n) {
  slides[currentSlide].classList.remove('active');
  dotsContainer.children[currentSlide].classList.remove('active');
  currentSlide = n;
  slides[currentSlide].classList.add('active');
  dotsContainer.children[currentSlide].classList.add('active');
}

// Manual nav arrows
const heroPrev = document.getElementById('hero-prev');
const heroNext = document.getElementById('hero-next');
if (heroPrev) heroPrev.addEventListener('click', () => {
  resetAutoAdvance();
  goToSlide((currentSlide - 1 + slides.length) % slides.length);
});
if (heroNext) heroNext.addEventListener('click', () => {
  resetAutoAdvance();
  goToSlide((currentSlide + 1) % slides.length);
});

// Auto-advance: video slide waits for video end, others wait 6s
let autoAdvanceTimer = null;
function resetAutoAdvance() {
  if (autoAdvanceTimer) { clearTimeout(autoAdvanceTimer); autoAdvanceTimer = null; }
}
function scheduleNext() {
  resetAutoAdvance();
  const cur = slides[currentSlide];
  if (cur && cur.classList.contains('hero-slide-video')) {
    // Wait for video to end (handled by 'ended' listener below)
    return;
  }
  autoAdvanceTimer = setTimeout(() => {
    goToSlide((currentSlide + 1) % slides.length);
  }, 6000);
}

// Wrap goToSlide to schedule next
const _goToSlide = goToSlide;
goToSlide = function(n) {
  _goToSlide(n);
  scheduleNext();
};
scheduleNext();

// ──────────── HERO VIDEO + AUDIO ────────────
const heroVideo = document.getElementById('hero-video');
const audioToggle = document.getElementById('hero-audio-toggle');
const audioLabel = document.getElementById('hero-audio-label');

const SOFT_VOLUME = 0.18; // dezent im hintergrund
let userMuted = false;    // Default: Ton an. Browser blockt evtl. den Autoplay-mit-Sound,
                          // dann fallen wir auf muted zurück, der User kann es per Klick einschalten.

if (heroVideo) {
  heroVideo.volume = SOFT_VOLUME;
  // Versuch 1: direkt mit Ton starten
  heroVideo.muted = false;
  const tryWithSound = heroVideo.play();
  if (tryWithSound && tryWithSound.then) {
    tryWithSound.then(() => {
      // klappt: Toggle als "Ton an" anzeigen
      audioToggle.classList.remove('muted');
      audioLabel.textContent = currentLang === 'en' ? 'Sound off' : 'Ton aus';
      audioLabel.setAttribute('data-de', 'Ton aus');
      audioLabel.setAttribute('data-en', 'Sound off');
    }).catch(() => {
      // Browser hat Ton blockiert — stumm starten und User auf "Ton an" hinweisen
      heroVideo.muted = true;
      heroVideo.play().catch(() => {});
      audioToggle.classList.add('muted');
      audioLabel.textContent = currentLang === 'en' ? 'Sound on' : 'Ton an';
      audioLabel.setAttribute('data-de', 'Ton an');
      audioLabel.setAttribute('data-en', 'Sound on');
      userMuted = true;
      // Beim ersten Klick irgendwo auf der Seite Ton aktivieren (gilt als User-Gesture)
      const enableOnGesture = () => {
        if (heroVideo.muted && !userMuted) return;
        if (heroVideo.muted) setAudioOn(true);
        document.removeEventListener('click', enableOnGesture);
        document.removeEventListener('touchstart', enableOnGesture);
      };
      // hier NICHT auto-enabling — nur wenn User explizit klickt; bewusst nichts tun
    });
  }

  // When video ends naturally, advance to next slide
  heroVideo.addEventListener('ended', () => {
    if (slides[currentSlide] && slides[currentSlide].classList.contains('hero-slide-video')) {
      goToSlide((currentSlide + 1) % slides.length);
    }
  });
}

function setAudioOn(on) {
  if (!heroVideo) return;
  if (on) {
    heroVideo.muted = false;
    heroVideo.volume = SOFT_VOLUME;
    audioToggle.classList.remove('muted');
    audioLabel.textContent = currentLang === 'en' ? 'Sound off' : 'Ton aus';
    audioLabel.setAttribute('data-de', 'Ton aus');
    audioLabel.setAttribute('data-en', 'Sound off');
    heroVideo.play().catch(() => {});
  } else {
    heroVideo.muted = true;
    audioToggle.classList.add('muted');
    audioLabel.textContent = currentLang === 'en' ? 'Sound on' : 'Ton an';
    audioLabel.setAttribute('data-de', 'Ton an');
    audioLabel.setAttribute('data-en', 'Sound on');
  }
  userMuted = !on;
}

if (audioToggle) {
  audioToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    setAudioOn(heroVideo.muted);
  });
}

// Track scroll: ONLY mute when user explicitly scrolls back to top of hero (so audio restarts).
// Audio should NOT mute just because user scrolled past hero — only when:
//   1. User clicks toggle off
//   2. An <audio> hörprobe starts playing
//   3. Another <video> on the page starts playing
//   4. Video ended naturally
let wasInHero = true;
let lastScrollY = window.scrollY;

window.addEventListener('scroll', () => {
  if (!heroVideo) return;
  const scrollY = window.scrollY;
  const heroHeight = document.getElementById('hero').offsetHeight;
  const inHero = scrollY < heroHeight - 80;
  const scrollingUp = scrollY < lastScrollY;

  // Returned to very top of hero → restart playback from beginning
  if (!wasInHero && inHero && scrollY < 40 && scrollingUp) {
    try { heroVideo.currentTime = 0; } catch(e){}
    heroVideo.play().catch(() => {});
  }

  wasInHero = inHero;
  lastScrollY = scrollY;
}, { passive: true });

// Pause hero video sound when ANY other media on the page starts playing
document.addEventListener('play', (e) => {
  const t = e.target;
  if (!t || t === heroVideo) return;
  if (t.tagName === 'AUDIO' || t.tagName === 'VIDEO') {
    if (heroVideo && !heroVideo.muted) setAudioOn(false);
  }
}, true);

// ──────────── NAV SCROLL ────────────
const nav = document.getElementById('main-nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 60);
});

// ──────────── MOBILE MENU ────────────
const mobileBtn = document.getElementById('mobile-menu-btn');
const mobileDrawer = document.getElementById('mobile-drawer');
const mobileBackdrop = document.getElementById('mobile-backdrop');
function setMobileMenu(open) {
  if (!mobileBtn) return;
  mobileBtn.classList.toggle('open', open);
  mobileDrawer.classList.toggle('open', open);
  mobileBackdrop.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}
if (mobileBtn) {
  mobileBtn.addEventListener('click', () => {
    setMobileMenu(!mobileDrawer.classList.contains('open'));
  });
  mobileBackdrop.addEventListener('click', () => setMobileMenu(false));
  mobileDrawer.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => setMobileMenu(false));
  });
  // Lang buttons in drawer mirror main toggles
  const mDe = document.getElementById('m-btn-de');
  const mEn = document.getElementById('m-btn-en');
  if (mDe) mDe.addEventListener('click', () => {
    document.getElementById('btn-de').click();
    mDe.classList.add('active'); mEn.classList.remove('active');
  });
  if (mEn) mEn.addEventListener('click', () => {
    document.getElementById('btn-en').click();
    mEn.classList.add('active'); mDe.classList.remove('active');
  });
}

// ──────────── CONCERT DATA ────────────
const concertsDE = [
  { day: '12', month: 'Jun', title: 'Kammerkonzert', program: 'Grieg · Reinecke · Türk', city: 'Freiberg', venue: 'Dom St. Marien' },
  { day: '24', month: 'Jun', title: 'Sommerkonzert', program: 'Brahms · Chopin · Schubert', city: 'Chemnitz', venue: 'Städtische Musikschule' },
  { day: '08', month: 'Jul', title: 'Klavierabend', program: 'Soloprogramm · Platzhalter', city: 'Dresden', venue: 'Hochschule für Musik' },
  { day: '20', month: 'Sep', title: 'Herbstkonzert', program: 'Gavrilin · Reinecke · Brahms', city: 'Leipzig', venue: 'Mendelssohn-Haus' },
];

const concertsEN = [
  { day: '12', month: 'Jun', title: 'Chamber Concert', program: 'Grieg · Reinecke · Türk', city: 'Freiberg', venue: 'Dom St. Marien' },
  { day: '24', month: 'Jun', title: 'Summer Concert', program: 'Brahms · Chopin · Schubert', city: 'Chemnitz', venue: 'Municipal Music School' },
  { day: '08', month: 'Jul', title: 'Piano Recital', program: 'Solo Programme · Placeholder', city: 'Dresden', venue: 'Hochschule für Musik' },
  { day: '20', month: 'Sep', title: 'Autumn Concert', program: 'Gavrilin · Reinecke · Brahms', city: 'Leipzig', venue: 'Mendelssohn-Haus' },
];

function renderConcerts(lang) {
  const list = document.getElementById('concert-list');
  const data = lang === 'de' ? concertsDE : concertsEN;
  list.innerHTML = data.map((c, i) => `
    <div class="concert-item reveal" style="transition-delay:${i * 0.07}s">
      <div class="concert-date-block">
        <div class="concert-day">${c.day}</div>
        <div class="concert-month">${c.month}</div>
      </div>
      <div class="concert-info">
        <h3>${c.title}</h3>
        <p>${c.program}</p>
      </div>
      <div class="concert-location">
        <span class="concert-city">${c.city}</span>
        <div class="concert-venue">${c.venue}</div>
      </div>
    </div>
  `).join('');
  // observe newly created items
  list.querySelectorAll('.concert-item').forEach(el => io.observe(el));
}

// ──────────── AUDIO TRACKS ────────────
const tracksDE = [
  { num: '01', title: 'Edvard Grieg — Zug der Zwerge', composer: 'Lyrische Stücke op. 54 Nr. 3', dur: '3:42' },
  { num: '02', title: 'Carl Reinecke — Tarantelle', composer: 'Klavierstücke für die Jugend', dur: '2:15' },
  { num: '03', title: 'Valery Gavrilin — Little Clock', composer: 'Charakterstück', dur: '1:58' },
  { num: '04', title: 'Daniel Gottlob Türk — The Storm', composer: 'Klavierstück', dur: '2:34' },
  { num: '05', title: 'Hörprobe · Platzhalter', composer: 'Weitere Aufnahmen folgen', dur: '—' },
];

const tracksEN = [
  { num: '01', title: 'Edvard Grieg — March of the Dwarfs', composer: 'Lyric Pieces op. 54 No. 3', dur: '3:42' },
  { num: '02', title: 'Carl Reinecke — Tarantella', composer: 'Piano Pieces for Youth', dur: '2:15' },
  { num: '03', title: 'Valery Gavrilin — Little Clock', composer: 'Character Piece', dur: '1:58' },
  { num: '04', title: 'Daniel Gottlob Türk — The Storm', composer: 'Piano Piece', dur: '2:34' },
  { num: '05', title: 'Audio Sample · Placeholder', composer: 'More recordings to follow', dur: '—' },
];

const waveHeights = [[8,14,20,12,18,10,22,14,8,16,20,10,16,12,18,8,14,22,10,16],
                     [12,18,8,22,14,10,20,16,12,18,8,16,22,10,14,20,8,16,12,18],
                     [16,10,22,8,18,14,12,20,10,16,22,8,14,18,10,20,12,16,8,22],
                     [10,20,14,18,8,16,22,12,18,10,20,14,8,16,12,22,10,18,16,8],
                     [14,8,18,12,20,10,16,22,8,14,18,12,20,8,16,10,22,14,8,18]];

function renderTracks(lang) {
  const container = document.getElementById('audio-tracks');
  const data = lang === 'de' ? tracksDE : tracksEN;
  container.innerHTML = data.map((t, i) => `
    <div class="audio-track reveal" style="transition-delay:${i * 0.08}s">
      <div class="track-num">${t.num}</div>
      <div class="track-info">
        <h4>${t.title}</h4>
        <span>${t.composer}</span>
      </div>
      <div class="track-waveform">
        ${waveHeights[i].map(h => `<span style="height:${h}px"></span>`).join('')}
      </div>
      <div class="track-duration">${t.dur}</div>
      <button class="track-play">
        <svg viewBox="0 0 16 16"><path d="M4 2 L14 8 L4 14 Z"/></svg>
      </button>
    </div>
  `).join('');
  container.querySelectorAll('.audio-track').forEach(el => io.observe(el));
}

// ──────────── CURSOR GLOW ────────────
const cursorGlow = document.getElementById('cursor-glow');
let mouseX = 0, mouseY = 0, glowX = 0, glowY = 0;
let glowRaf;

document.addEventListener('mousemove', e => {
  mouseX = e.clientX;
  mouseY = e.clientY;
  cursorGlow.style.opacity = '1';
  if (!glowRaf) glowRaf = requestAnimationFrame(animateGlow);
});

document.addEventListener('mouseleave', () => {
  cursorGlow.style.opacity = '0';
});

function animateGlow() {
  glowX += (mouseX - glowX) * 0.08;
  glowY += (mouseY - glowY) * 0.08;
  cursorGlow.style.left = glowX + 'px';
  cursorGlow.style.top = glowY + 'px';
  glowRaf = requestAnimationFrame(animateGlow);
}

// ──────────── HERO PARALLAX ────────────
const heroContent = document.getElementById('hero-content');
const heroSlides = document.getElementById('hero-slides');
const heroFloats = document.querySelectorAll('.hero-floating');

window.addEventListener('scroll', () => {
  const scrollY = window.scrollY;
  if (scrollY < window.innerHeight) {
    // Content moves up faster = parallax depth
    if (heroContent) {
      heroContent.style.transform = `translateY(${scrollY * 0.25}px)`;
      heroContent.style.opacity = 1 - (scrollY / (window.innerHeight * 0.6));
    }
    // Background slides move slower
    if (heroSlides) {
      heroSlides.style.transform = `translateY(${scrollY * 0.15}px)`;
    }
    // Floating elements drift at different speeds
    heroFloats.forEach((el, i) => {
      const speed = [0.12, 0.18, 0.08][i] || 0.1;
      el.style.transform = `translateY(${scrollY * speed}px)`;
    });
  }
}, { passive: true });

// ──────────── SECTION TITLE WORD REVEAL ────────────
function wrapTitleWords(el) {
  if (!el || el.dataset.wordWrapped) return;
  el.dataset.wordWrapped = '1';

  // Walk child nodes to handle mixed text + <em>
  const fragment = document.createDocumentFragment();
  el.childNodes.forEach(node => {
    if (node.nodeType === 3) { // text node
      node.textContent.split(/(\s+)/).forEach(part => {
        if (/^\s+$/.test(part)) {
          fragment.appendChild(document.createTextNode(part));
        } else if (part) {
          const wrap = document.createElement('span');
          wrap.className = 'title-word';
          wrap.innerHTML = `<span class="title-word-inner">${part}</span>`;
          fragment.appendChild(wrap);
        }
      });
    } else if (node.nodeType === 1 && node.tagName === 'EM') {
      const wrap = document.createElement('span');
      wrap.className = 'title-word';
      wrap.innerHTML = `<span class="title-word-inner"><em>${node.textContent}</em></span>`;
      fragment.appendChild(wrap);
    } else {
      fragment.appendChild(node.cloneNode(true));
    }
  });
  el.innerHTML = '';
  el.appendChild(fragment);
}

// Observe everything
document.querySelectorAll('.section-divider').forEach(el => io.observe(el));
document.querySelectorAll('.section-label').forEach(el => io.observe(el));
document.querySelectorAll('.section-title').forEach(el => io.observe(el));
document.querySelectorAll('.bio-awards').forEach(el => io.observe(el));

// Add reveal classes programmatically to key elements
const revealTargets = [
  ['.bio-text-col', 'reveal-right'],
  ['.bio-image-col', 'reveal-left'],
  ['.hero-quote', null],
  ['.press-quote', 'reveal-scale'],
  ['.gallery-item', 'reveal-scale'],
  ['.video-item', 'reveal'],
  ['.contact-left', 'reveal-left'],
  ['.contact-right', 'reveal-right'],
  ['.concerts-all-link', 'reveal'],
  ['.audio-ph-note', 'reveal'],
  ['.press-logos', 'reveal'],
  ['.youtube-placeholder', 'reveal-scale'],
];

revealTargets.forEach(([selector, cls]) => {
  document.querySelectorAll(selector).forEach((el, i) => {
    const c = cls || 'reveal';
    if (!el.classList.contains(c)) el.classList.add(c);
    if (i > 0) el.classList.add(`stagger-${Math.min(i, 5)}`);
    io.observe(el);
  });
});

// ──────────── LANGUAGE SWITCH ────────────
let currentLang = 'de';

function applyLang(lang) {
  currentLang = lang;
  document.documentElement.lang = lang;

  document.querySelectorAll('[data-de][data-en]').forEach(el => {
    const text = lang === 'de' ? el.getAttribute('data-de') : el.getAttribute('data-en');
    if (text) el.innerHTML = text;
  });

  document.getElementById('btn-de').classList.toggle('active', lang === 'de');
  document.getElementById('btn-en').classList.toggle('active', lang === 'en');

  // renderConcerts(lang); // disabled — concerts now rendered server-side from DB
  // renderTracks(lang); // disabled — audio tracks now rendered server-side from DB
  document.querySelectorAll('#concert-list .concert-item, #audio-tracks .audio-track').forEach(el => io.observe(el));

  // Update all-concerts link text
  const allLink = document.querySelector('.concerts-all-link span');
  if (allLink) allLink.textContent = lang === 'de' ? 'Alle Konzerte' : 'All Concerts';
}

document.getElementById('btn-de').addEventListener('click', () => applyLang('de'));
document.getElementById('btn-en').addEventListener('click', () => applyLang('en'));

// Init
applyLang('de');
</script>
</body>
</html>
