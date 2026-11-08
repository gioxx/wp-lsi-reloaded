=== Lightweight Social Icons Reloaded ===
Contributors: gioxx
Tags: social icons, social media, social networking, social profiles, social icon widget
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add simple, customizable social icon buttons to your widget areas. Independent fork of Lightweight Social Icons, rebuilt and modernized.

== Description ==

Lightweight Social Icons Reloaded is an easy to use, lightweight social icon widget which lets you display your favorite social profile icons.

The icons use an icon font, meaning you can choose the size, border radius (roundness), color and hover color of your icons.

This plugin is an independent fork of [Lightweight Social Icons](https://wordpress.org/plugins/lightweight-social-icons/) by Thomas Usborne / GeneratePress, which had not seen any development in years. Reloaded fixes long-standing bugs (the 500px and Snapchat icons never rendered due to a mismatched CSS class), removes long-dead services (Delicious, Vine, StumbleUpon, Yahoo), and adds icons for services people actually use today: Discord, WhatsApp, TikTok, Threads and Bluesky.

If you're migrating from the original Lightweight Social Icons plugin, simply deactivate it and activate this one: your widget settings, icon choices and colors carry over automatically, no reconfiguration needed.

= Features include: =

* Choose your own order
* Icon size
* Border radius
* Background color
* Text/icon color
* Background color on hover
* Text/icon color on hover
* Open links in new window
* Enable/disable tooltips
* Alignment of icons

= Included icons: =

* 500px
* AngelList
* Bandcamp
* Behance
* BitBucket
* Blog Lovin'
* Bluesky
* Codepen
* Contact (email)
* DeviantArt
* Digg
* Discord
* Dribbble
* Dropbox
* Facebook
* Flickr
* Foursquare
* Github
* Houzz
* Instagram
* iTunes
* JSFiddle
* Last.fm
* LinkedIn
* Mastodon
* Mixcloud
* Newsletter (paper plane)
* Phone
* Pinterest
* Reddit
* RSS
* Skype
* Snapchat
* Soundcloud
* Spotify
* Stack Overflow
* Steam
* Telegram
* Threads
* TikTok
* Trip Advisor
* Tumblr
* Twitch
* Twitter
* Vimeo
* VK
* WhatsApp
* WordPress
* X
* Xing
* Yelp
* YouTube

== Installation ==

1. Go to "Plugins > Add New" in your Dashboard and search for: Lightweight Social Icons Reloaded
2. Or download the .zip from WordPress.org / GitHub, and upload the folder to the `/wp-content/plugins/` directory via FTP.

= Migrating from the original Lightweight Social Icons =

1. Install and activate Lightweight Social Icons Reloaded.
2. Deactivate the original Lightweight Social Icons plugin.
3. That's it. Your widget instance, colors, and configured icons are inherited automatically (both plugins share the same widget storage key).

== Frequently Asked Questions ==

= How do I add a widget with my icons? =

* Make sure Lightweight Social Icons Reloaded is activated.
* Navigate to "Appearance > Widgets" and look for the "Lightweight Social Icons Reloaded" widget.
* Drag the widget into your desired widget area.

Once the widget is added to a widget area, you can expand it and fill in your profiles.

= What options does Lightweight Social Icons Reloaded have? =

* Choose your own order
* Title (optional): Choose a title for your widget.
* Icon size: Choose the size of the icon font.
* Border radius: Choose the border radius of the icon. Border radius refers to the "roundness" of the button.
* Background color: The background color of the button.
* Text Color: The icon color.
* Background hover color: The background color of the button when you hover over it.
* Text hover color: The color of the icon when you hover over the button.
* Open link in a new window?: Choose to open all of the links in a new window/tab.
* Alignment: Choose to align the button to the left, center or right.

= How do I add my social profile URLs? =

In the select box on the left, choose your desired social media site.

To the right of the select box, enter in the URL of your profile on that specific social media site.

The "Skype" option takes your Skype username.

The "Phone" option takes your phone number.

= Can I assign different colors to each icon? =

Not at this time (without custom CSS of course).

= Can I display my icons with a shortcode? =

Not by default, but you can use a plugin like this: https://wordpress.org/plugins/widget-shortcode/

It allows you to add the widget to an arbitrary widget area, and then you can call that widget using a shortcode elsewhere in your website.

== Screenshots ==

1. Set your options and choose which social profiles you want to show off.
2. All of the icons in a widget with tooltips enabled.
3. Icons with a different color.

== Changelog ==

= 1.0.1 =
* Fixed icons stacking vertically instead of in a single row: the 1.0.0 stylesheet had lost the widget layout and tooltip rules when the icon font was regenerated.
* Restored the original icon box size (1.2em), so icons look exactly as in the original plugin.

= 1.0.0 =
* Independent fork of Lightweight Social Icons 1.1.2, renamed to Lightweight Social Icons Reloaded.
* Fixed the 500px and Snapchat icons, which never rendered due to a mismatched CSS class name.
* Removed Delicious, Vine, StumbleUpon and Yahoo (all long defunct).
* Added Discord, WhatsApp, TikTok, Threads and Bluesky.
* Widget settings, icon choices and sidebar placement are inherited automatically from the original Lightweight Social Icons plugin.
* Added missing `ABSPATH` guard, `Requires at least`/`Requires PHP` headers, and fixed several unescaped output strings ahead of WordPress.org submission.

== Upgrade Notice ==

= 1.0.1 =
Fixes icons displaying vertically instead of in a row. Recommended for everyone on 1.0.0.

= 1.0.0 =
First release of the independent fork. Deactivate the original Lightweight Social Icons plugin after installing this one; your settings carry over automatically.
