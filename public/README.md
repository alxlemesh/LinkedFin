# LinkedIn-like PHP Profile App

A native PHP application with a LinkedIn-inspired profile page.

## Features

| Feature | Details |
|---|---|
| **Profile page** | Banner, round profile picture (pfp) overlapping the banner, name, headline, location, bio, connections |
| **Activity wall** | Feed of posts with 👍 like / 💬 comment / 🔁 share reactions |
| **Sidebar** | "People you may know" widget |
| **Edit profile** | Update name, headline, location, bio |
| **Profile picture upload** | JPEG / PNG / GIF, max **8 MB**, min **200 × 200 px** |
| **Banner upload** | JPEG / PNG / GIF, max **8 MB**, min **400 × 100 px** (recommended 1584 × 396) |
| **Server-side validation** | File type (via `getimagesize`), file size, image dimensions |
| **Security** | PHP execution blocked inside `uploads/`, data directory blocked, safe random filenames |

## Structure

```
public/
├── index.php             # Redirects to /profile.php
├── profile.php           # Main profile page
├── update_profile.php    # Edit profile + upload forms
├── process_upload.php    # Form handler (info update + image uploads)
├── img/
│   └── defaults.php      # Generates placeholder avatar/banner via PHP GD
├── css/
│   └── style.css         # LinkedIn-inspired styles
├── js/
│   └── app.js            # Client-side preview & like toggle
├── data/
│   └── profile.json      # Profile data (name, headline, posts, …)
└── uploads/              # Saved uploaded images
```

## Requirements

- PHP 8.0+ with GD extension
- Apache / Nginx (or `php -S` dev server)

## Quick start

```bash
cd public
php -S localhost:8080
# open http://localhost:8080
```

## Image constraints

| Image | Max size | Min dimensions | Recommended |
|---|---|---|---|
| Profile picture | 8 MB | 200 × 200 px | 400 × 400 px |
| Banner | 8 MB | 400 × 100 px | 1584 × 396 px |
| Allowed formats | — | — | JPEG, PNG, GIF |
