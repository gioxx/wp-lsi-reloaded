# Lightweight Social Icons Reloaded

Independent fork of [Lightweight Social Icons](https://wordpress.org/plugins/lightweight-social-icons/) by Thomas Usborne / GeneratePress, which had seen no development in years. Reloaded is a clean restart: same widget, rebuilt and modernized.

What changed from the original 1.1.2:

- Fixed the 500px and Snapchat icons, which never rendered due to a mismatched CSS class name.
- Removed long-dead services: Delicious, Vine, StumbleUpon, Yahoo, Skype, Digg, Bloglovin'.
- Added icons for services people actually use today: Bluesky, Threads, Discord, WhatsApp, TikTok, Signal, Amazon, Ko-fi, Patreon, Buy Me a Coffee, Substack, Medium, GitLab, Codeberg, Pixelfed, PeerTube, Lemmy, Apple Podcasts, Kick, Letterboxd, Goodreads, Strava.
- Outdated brands switch automatically: Twitter shows as X, Foursquare as Swarm, iTunes as Apple Music, AngelList as Wellfound.
- Widget form: remove an icon with one click, reorder by drag and drop, no empty slots left behind; works in the Customizer too.
- Brought the plugin up to current WordPress.org Plugin Check standards (missing `ABSPATH` guard, missing `Requires at least`/`Requires PHP` headers, several unescaped output strings).
- Renamed internals (`LSIR_Widget`, `lsir_*` functions) to avoid collisions with the original plugin, while keeping the same widget storage key so settings migrate automatically.

## Migrating from the original Lightweight Social Icons

Install and activate this plugin, then deactivate the original. Your widget instance, configured icons, colors and sidebar placement carry over automatically — both plugins store widget data under the same key on purpose.

## Requesting new icons

Open an Issue.

## Adding icons (maintainers)

`css/style.css`, `css/style-min.css` and `fonts/` are **generated**, never edit them by hand.

1. Import `asset/fontello-*/config.json` at [fontello.com](https://fontello.com), add the glyph (brand icons: SVG from [Simple Icons](https://simpleicons.org), CC0) with a CSS name equal to the plugin icon id, and download the export.
2. Replace the `asset/fontello-*` folder with the new export (keep exactly one).
3. Add the icon to `lsir_icons()` in the plugin file.
4. Run `python3 asset/build-css.py`. It rebuilds the stylesheets from `asset/plugin.css` (widget layout, tooltips) plus the glyph rules, copies the font files, and fails if any plugin icon has no glyph.

## License

This plugin is licensed under the [GPLv2 or later](LICENSE).

It bundles third-party assets under their own licenses:

- **Icon font** (`fonts/`, generated with [Fontello](https://fontello.com)): glyphs from [Font Awesome](https://fontawesome.com) and Brandico (SIL OFL 1.1) and Zocial (MIT); custom brand glyphs (Mastodon, X, Discord, TikTok, Threads, Bluesky and all icons added in 1.1.0) from [Simple Icons](https://simpleicons.org) (CC0). All brand icons are trademarks of their respective owners.
- **Tooltips** (`js/jquery.tooltipster.min.js`): [Tooltipster](https://iamceege.github.io/tooltipster/), MIT License.

---

**Original plugin credits**: Thomas Usborne, [GeneratePress](https://generatepress.com)
