<?php
// public/_data.php — Helper-Funktionen, um Inhalte aus der DB zu lesen
require_once __DIR__ . '/cms/db.php';

function fetch_videos(int $limit = null, bool $featured_first = true): array {
    $sql = "SELECT * FROM videos WHERE published = 1 ORDER BY "
         . ($featured_first ? "featured DESC, " : "")
         . "sort_order, id DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

function fetch_audio(int $limit = null): array {
    $sql = "SELECT * FROM audio_tracks WHERE published = 1 ORDER BY sort_order, id";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

function fetch_gallery(int $limit = null): array {
    $sql = "SELECT * FROM gallery_images WHERE published = 1 ORDER BY sort_order, id";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

function fetch_press(int $limit = null): array {
    $sql = "SELECT * FROM press_quotes WHERE published = 1 ORDER BY sort_order, id";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

function fetch_concerts(int $limit = null): array {
    $sql = "SELECT * FROM concerts WHERE published = 1 ORDER BY concert_date ASC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

function fetch_hero_slides(): array {
    return db()->query("SELECT * FROM hero_slides WHERE published = 1 ORDER BY sort_order, id")->fetchAll();
}

function fetch_content(string $key, string $lang = 'de'): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query("SELECT * FROM content_blocks")->fetchAll() as $r) {
            $cache[$r['key_name']] = $r;
        }
    }
    if (!isset($cache[$key])) return '';
    return $cache[$key][$lang === 'en' ? 'value_en' : 'value_de'] ?? '';
}

function format_duration(?string $d): string {
    return $d ? $d : '';
}
