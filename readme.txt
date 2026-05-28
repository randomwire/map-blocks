=== Map Blocks ===
Contributors: randomwire
Donate link: https://ko-fi.com/randomwire
Tags: gutenberg, blocks, maps, leaflet, acf
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.2.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg blocks for displaying maps using Advanced Custom Fields and Leaflet.

== Description ==

Map Blocks provides three custom Gutenberg blocks for displaying interactive maps:

* **Post Map** - Display a map for a single post with ACF location field
* **Category Map** - Show all posts in a category on a map
* **Archive Map** - Display all posts with location data on a map

**Requirements:**

* Advanced Custom Fields (free or Pro), 6.0+
* ACF field group with Google Map field type
* Mapbox API token (free tier available at mapbox.com)

Uses Leaflet.js for fast, mobile-friendly maps with no Google Maps dependency.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/map-blocks/`
2. Activate the plugin
3. Go to Settings > General and enter your Mapbox API token
4. Ensure your posts have ACF location fields configured
5. Add blocks via the block editor (search for "map")

== Frequently Asked Questions ==

= Do I need a Mapbox account? =

Yes. Sign up for free at mapbox.com. The free tier includes 50,000 map loads per month.

= Which ACF field type should I use? =

Use the Google Map field type in ACF. The plugin reads latitude and longitude from this field.

= Does this work without ACF? =

No, Advanced Custom Fields is required. The plugin reads location data from ACF fields.

== External services ==

This plugin loads map tiles from Mapbox so it can render interactive maps on the front end.

When a visitor views a page containing a Map Blocks block, the visitor's browser requests raster map tiles from `https://api.mapbox.com/` using the Mapbox access token you configured under Settings > General. Each tile request includes the requesting page's referrer and the visitor's IP address (handled by Mapbox, not by this plugin). No personal data from your WordPress install is transmitted by the plugin itself. Tiles are only requested when a block is rendered; no calls are made from the WordPress admin.

Mapbox is a third-party service operated by Mapbox, Inc.

* Terms of service: https://www.mapbox.com/legal/tos
* Privacy policy: https://www.mapbox.com/legal/privacy

== Screenshots ==

1. Post Map block displaying a single location
2. Category Map showing multiple posts
3. Settings page for Mapbox token

== Changelog ==

= 2.2.2 =
* Add a Donate link to the plugin's row on the Plugins admin page, matching other Randomwire plugins.

= 2.2.1 =
* Archive Map: fit the initial view to the actual marker bounds instead of a fixed Rome center, eliminating a thin grey strip that appeared above the tiles at low zoom. Adds `maxBounds` and `minZoom` to keep the viewport inside Mercator tile coverage when panning or zooming out.

= 2.2.0 =
* Append plugin version to bundled Leaflet and Supercluster URLs so cache-busting works on upgrade.
* Show an admin notice when the Mapbox access token has not been configured.
* Archive Map: render nothing when there are no geocoded posts (matches Post Map and Category Map behaviour).
* Category Map: honour a stored `zoom_level` of 0.
* Post Map: add `rel="noopener noreferrer"` to the popup link.
* Fall back to an empty marker set if JSON encoding of post data fails.

= 2.1.1 =
* Update bundled KDBush from 4.0.2 to 4.1.0 (performance improvements in radius search).
* Tested with WordPress 7.0.

= 2.1.0 =
* Category Map now uses marker clustering (Supercluster), matching Archive Map behaviour.

= 2.0.0 =
* Remove ACF Pro dependency: blocks now register via WordPress core's native server-side render and work with the free Advanced Custom Fields plugin.
* In-editor preview replaced with a static placeholder; frontend output is unchanged.

= 1.2.1 =
* Fix map tiles not rendering by moving border-radius to CSS stylesheet

= 1.2.0 =
* Add marker clustering support with Supercluster.js library
* Fix block.json align property to use proper supports wrapper

= 1.1.0 =
* Archive Map: Use cluster-style icons for individual markers at low zoom levels

= 1.0.0 =
* Initial public release
* Post Map block
* Category Map block
* Archive Map block
* Mapbox integration
* Leaflet.js bundled

== Upgrade Notice ==

= 1.0.0 =
First stable release.
