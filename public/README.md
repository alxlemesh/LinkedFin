# LinkedFin PHP App

A self-contained PHP 8+ application with a LinkedIn-inspired profile page, MySQL/mysqli data storage, and a login screen.

## Features

| Feature | Details |
|---|---|
| **Login screen** | Username + password, bcrypt hashed, session-based auth |
| **Default credentials** | `root` / `lbhtrnjh` |
| **Profile page** | Banner, round pfp overlapping the banner, name, headline, location, bio, connections |
| **Activity wall** | Feed of posts with 👍 like / 💬 comment / 🔁 share counts |
| **Sidebar** | "People you may know" widget |
| **Edit profile** | Update name, headline, location, bio — saved to MySQL |
| **Profile picture upload** | JPEG / PNG / GIF, max **8 MB**, min **200 × 200 px** |
| **Banner upload** | JPEG / PNG / GIF, max **8 MB**, min **400 × 100 px** (recommended 1584 × 396) |
| **Server-side validation** | File type via `getimagesize()`, file size, image dimensions |
| **Security** | Prepared statements throughout, PHP execution blocked in `uploads/`, data directory blocked |

## Structure

```
public/
├── index.php             # Redirects to login or profile
├── login.php             # Sign-in page
├── auth.php              # Login / logout handler
├── profile.php           # Main profile page (auth required)
├── update_profile.php    # Edit profile + upload forms (auth required)
├── process_upload.php    # Form handler — info update + image uploads
├── config.php            # DB connection configuration
├── db.php                # mysqli connection helper
├── setup_db.php          # One-time DB setup + seed script
├── schema.sql            # Schema reference
├── img/
│   └── defaults.php      # Generates placeholder avatar/banner via PHP GD
├── css/
│   └── style.css         # LinkedFin teal styles
├── js/
│   └── app.js            # Client-side preview & like toggle
├── data/
│   └── profile.json      # Legacy reference data
└── uploads/              # Saved uploaded images
```

## Requirements

- PHP 8.0+ with GD and mysqli extensions
- MySQL 8+ (or MariaDB)
- Apache / Nginx (or `php -S` dev server)

## Quick start

```bash
# 1. Run the one-time DB setup (adjust socket/host as needed):
DB_SOCKET=/run/mysqld/mysqld.sock php public/setup_db.php

# 2. Serve the app
cd public
DB_SOCKET=/run/mysqld/mysqld.sock DB_USER=root DB_PASS="" DB_NAME=linkedfin php -S localhost:8080

# 3. Open http://localhost:8080
#    Sign in with:  root / lbhtrnjh
```

## Image constraints

| Image | Max size | Min dimensions | Recommended |
|---|---|---|---|
| Profile picture | 8 MB | 200 × 200 px | 400 × 400 px |
| Banner | 8 MB | 400 × 100 px | 1584 × 396 px |
| Allowed formats | — | — | JPEG, PNG, GIF |
