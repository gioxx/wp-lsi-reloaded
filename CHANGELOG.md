# Changelog

All notable changes to this project are documented in this file.

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
