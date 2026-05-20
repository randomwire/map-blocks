=== Map Blocks ===
Contributors: randomwire
Tags: gutenberg, blocks, maps, leaflet, acf
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg blocks for displaying maps using Advanced Custom Fields and Leaflet.

== Description ==

Map Blocks provides three custom Gutenberg blocks for displaying interactive maps:

* **Post Map** - Display a map for a single post with ACF location field
* **Category Map** - Show all posts in a category on a map
* **Archive Map** - Display all posts with location data on a map

**Requirements:**

* Advanced Custom Fields (ACF) Pro or Free
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

== Screenshots ==

1. Post Map block displaying a single location
2. Category Map showing multiple posts
3. Settings page for Mapbox token

== Changelog ==

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
