# Friedrich Schäfer · CMS

Schlankes PHP/MySQL-CMS für die Website. Login unter `https://deinedomain.tld/admin/`.

---

## Was du auf dem Server tun musst (Schritt für Schritt)

### 1. Dateien hochladen
Lade den **gesamten Projektordner** (alle Dateien aus dem Download) in das Webroot deiner Domain (z. B. `/var/www/fjs-pianist.de/`).
Wichtig: Auch die Ordner `cms/`, `admin/` und `uploads/` müssen mit hoch.

### 2. Datenbank importieren
Du hast bereits eine MySQL/MariaDB-Datenbank `friedrichdb` mit User `friedrichdbuser`. Importiere das Schema:

```bash
mysql -u friedrichdbuser -p friedrichdb < cms/schema.sql
```

Oder über phpMyAdmin: DB `friedrichdb` auswählen → Importieren → `cms/schema.sql` hochladen.

Das legt alle Tabellen an (Admin, Sessions, Inhalte, Konzerte, Videos, Hörproben, Presse, Galerie, Hero-Slides) und füllt die Standardtexte (Bio, Kontakt-E-Mails) ein.

### 3. Admin-User anlegen (einmalig)
Rufe im Browser **einmal** auf:

```
https://deinedomain.tld/admin/init.php
```

Das legt den Account an:
- **Benutzer:** `Friedrich-Schaefer`
- **Passwort:** `start123`

> ⚠ **Sofort danach `admin/init.php` vom Server löschen**, damit niemand sonst das Passwort zurücksetzen kann.

### 4. Upload-Ordner mit Schreibrechten
```bash
mkdir -p uploads/audio uploads/videos uploads/gallery uploads/hero
chmod -R 755 uploads
```
(Auf den meisten Hostings reicht es, dass die Ordner per FTP existieren.)

### 5. HTTPS sicherstellen
Die Login-Cookies sind als `Secure` gesetzt — `/admin/` muss zwingend per HTTPS erreichbar sein.

### 6. Einloggen und Passwort ändern
- `https://deinedomain.tld/admin/login.php`
- mit `Friedrich-Schaefer` / `start123` einloggen
- direkt unter „Passwort ändern" ein eigenes, starkes Passwort setzen

---

## DB-Zugangsdaten (in `cms/config.php` bereits eingetragen)
```
Host:     127.0.0.1
DB-Name:  friedrichdb
DB-User:  friedrichdbuser
DB-Pass:  fjWVT9Iy7j5ecItEClfi
```

Sollten sich die Daten ändern, anpassen in `cms/config.php`.

---

## Verfügbare CMS-Bereiche
- **Texte** — Hero-Quote, Bio-Absätze, Kontaktdaten (DE/EN)
- **Hero-Slides** — Bilder/Videos für die Startseite, Reihenfolge
- **Konzerte** — Termine inkl. Ort, Programm, Veröffentlichungsstatus
- **Videos** — Eigene Konzertmitschnitte (kein YouTube-Embed)
- **Hörproben** — Audio-Dateien mit Komponist/Titel/Dauer
- **Presse** — Zitate inkl. Quelle (DE/EN)
- **Galerie** — Fotostrecke
- **Passwort** — Zugangsdaten ändern

Es gibt **keine** Funktion zum Anlegen weiterer Admin-Konten — bewusst nur ein Single-User-Setup.

## Sicherheit
- Login via `password_hash()` (bcrypt)
- CSRF-Token bei jedem Formular
- HttpOnly + SameSite=Lax Session-Cookies
- Vorbereitete Statements (PDO) gegen SQL-Injection
- `.htaccess` schützt `cms/config.php` und `cms/db.php` vor direktem Zugriff
