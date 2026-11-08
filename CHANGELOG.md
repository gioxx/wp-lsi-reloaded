# Changelog

All notable changes to this project are documented in this file.

## [1.1.0] - 2026-11-08

### Added
- Icons: Ko-fi, Patreon, Buy Me a Coffee, Substack, Medium, GitLab (glyph already in the font, now offered), Codeberg, Signal, Pixelfed, PeerTube, Lemmy, Apple Podcasts, Kick, Letterboxd, Goodreads, Strava, plus Swarm, Apple Music and Wellfound (from [Simple Icons](https://simpleicons.org/) 16.35.0, CC0).
- Widget form: one-click remove button per icon and drag and drop reordering (jQuery UI Sortable, bundled with WordPress).
- Widget form works in the Customizer too (scripts were only loaded on the Widgets screen).
- `lsir_icon_aliases` filter and `lsir_get_icon_rows()` helper.
- `asset/build-css.py`: builds `css/style.css`, `css/style-min.css` and `fonts/` from `asset/plugin.css` and the Fontello export, and fails if any plugin icon has no glyph. This prevents a repeat of the 1.0.0 regression.

### Changed
- Legacy ids now render as their current brand: `twitter` as X, `foursquare` as Swarm, `itunes` as Apple Music, `angellist` as Wellfound. Stored widgets switch automatically and are saved under the new id on the next save.
- Saving the widget compacts the icon list: rows without a URL are dropped and the rest are renumbered in order, so removed icons no longer leave empty slots.
- Icon rows are read from the stored data rather than assumed from the number of available icons, so instances saved by older versions or the original plugin are always read in full.
- Glyph CSS classes for 500px and Snapchat now match the plugin ids directly (`lsicon-fivehundredpx`, `lsicon-snapchat`), no aliases needed.
- Stored URLs are saved with `esc_url_raw()` (escaped on output) instead of `esc_url()`.

### Removed
- Skype (service closed in May 2025), Digg (relaunched as an AI news aggregator without user profiles) and Bloglovin' (offline, "coming soon" placeholder). Rows using them are skipped on the front end and dropped on the next save.
- Unused glyphs from the font (Delicious, Vine, StumbleUpon, Yahoo, Skype, Digg, Foursquare, iTunes, AngelList, Twitter, Bloglovin').
- `js/scripts.js`, which was never loaded.

### Fixed
- "Open links in new window" was ignored the first time it was checked on a widget (the save routine read the old value instead of the submitted one).
- Field placeholders never updated when choosing an icon (`.attr('value')` on a `<select>` returns undefined).
- Admin scripts were re-bound on every form render; now printed once and bound with delegated events.

## [1.0.1] - 2026-11-08

### Fixed
- Icons stacked vertically instead of in a single row. Regenerating the Fontello font for 1.0.0 overwrote `css/style.css` and `css/style-min.css` with Fontello's stock CSS, dropping the widget layout rules (`.lsi-social-icons`, `li`, `li a`) and all Tooltipster styles. Restored them from the original plugin.
- Restored the original glyph box (`width`/`line-height: 1.2em`, no extra side margins) so icons render with the same size and spacing as the original plugin.

## [1.0.0] - 2026-07-19

Independent fork of [Lightweight Social Icons](https://wordpress.org/plugins/lightweight-social-icons/) 1.1.2, renamed to Lightweight Social Icons Reloaded.

### Added
- Discord, WhatsApp, TikTok, Threads and Bluesky icons.
- `ABSPATH` guard, `Requires at least`/`Requires PHP` headers, `Release Asset: true` / `GitHub Plugin URI` / `GitHub Branch` headers for Git Updater support.
- Admin notice if the original Lightweight Social Icons plugin is still active alongside this fork.

### Fixed
- The 500px icon never rendered: the plugin looked for a `lsicon-fivehundredpx` CSS class that never existed (Fontello generates `lsicon-500px`).
- The Snapchat icon never rendered: the plugin looked for a plain `lsicon-snapchat` class, but only `lsicon-snapchat-ghost`/`lsicon-snapchat-square` existed.
- Several unescaped output strings in the widget admin form (`background`, `color`, `background_hover`, `color_hover`, field ids/names).
- Wrong text domain (`lsi` instead of the plugin's own) on the "Add Icon" button.

### Removed
- Delicious, Vine, StumbleUpon and Yahoo icons (all long defunct).
- `load_plugin_textdomain()` call (WordPress auto-loads translations for wp.org-hosted plugins since 4.6).

### Changed
- Widget class renamed to `LSIR_Widget` and all `lsi_*` functions renamed to `lsir_*`, to avoid collisions if the original plugin is still active. The widget's `id_base` stays `lsi_Widget` on purpose: WordPress stores widget settings under `widget_{id_base}` and sidebar placements reference the same key, so anyone migrating from the original plugin inherits their widget instance, settings and sidebar position with zero migration code.
