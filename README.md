# Lightweight Social Icons Reloaded

Independent fork of [Lightweight Social Icons](https://wordpress.org/plugins/lightweight-social-icons/) by Thomas Usborne / GeneratePress, which had seen no development in years. Reloaded is a clean restart: same widget, rebuilt and modernized.

What changed from the original 1.1.2:

- Fixed the 500px and Snapchat icons, which never rendered due to a mismatched CSS class name.
- Removed long-dead services: Delicious, Vine, StumbleUpon, Yahoo.
- Added icons for services people actually use today: Discord, WhatsApp, TikTok, Threads, Bluesky.
- Brought the plugin up to current WordPress.org Plugin Check standards (missing `ABSPATH` guard, missing `Requires at least`/`Requires PHP` headers, several unescaped output strings).
- Renamed internals (`LSIR_Widget`, `lsir_*` functions) to avoid collisions with the original plugin, while keeping the same widget storage key so settings migrate automatically.

## Migrating from the original Lightweight Social Icons

Install and activate this plugin, then deactivate the original. Your widget instance, configured icons, colors and sidebar placement carry over automatically — both plugins store widget data under the same key on purpose.

## Requesting new icons

Open an Issue. The `asset/` folder contains the full Fontello configuration (`config.json`) — import it at [fontello.com](https://fontello.com) to see the current icon selection, add more, and export a new webfont.

## License

This plugin is licensed under the [GPLv2 or later](LICENSE).

It bundles third-party assets under their own licenses:

- **Icon font** (`fonts/`, generated with [Fontello](https://fontello.com)): [Font Awesome](https://fontawesome.com) glyphs, Font License SIL OFL 1.1, Code License MIT. Discord, WhatsApp, TikTok, Threads and Bluesky glyphs from [Simple Icons](https://simpleicons.org) (CC0). All brand icons are trademarks of their respective owners.
- **Tooltips** (`js/jquery.tooltipster.min.js`): [Tooltipster](https://iamceege.github.io/tooltipster/), MIT License.

---

**Original plugin credits**: Thomas Usborne, [GeneratePress](https://generatepress.com)
