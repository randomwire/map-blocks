# Changelog

## 2.2.1

- Archive Map: fit the initial view to the actual marker bounds instead of a fixed Rome center, eliminating a thin grey strip that appeared above the tiles at low zoom. Adds `maxBounds` and `minZoom` to keep the viewport inside Mercator tile coverage when panning or zooming out.

## 2.2.0

- Append plugin version to bundled Leaflet and Supercluster URLs so cache-busting works on upgrade.
- Show an admin notice when the Mapbox access token has not been configured.
- Archive Map: render nothing when there are no geocoded posts (matches Post Map and Category Map behaviour).
- Category Map: honour a stored `zoom_level` of 0.
- Post Map: add `rel="noopener noreferrer"` to the popup link.
- Fall back to an empty marker set if JSON encoding of post data fails.

## 2.1.1

- Update bundled KDBush from 4.0.2 to 4.1.0 (performance improvements in radius search).
- Tested with WordPress 7.0.

## 2.1.0

- Category Map now uses marker clustering (Supercluster), matching Archive Map behaviour.

## 2.0.0

- Remove ACF Pro dependency: blocks now register via WordPress core's native server-side render and work with the free Advanced Custom Fields plugin.
- In-editor preview replaced with a static placeholder; frontend output is unchanged.

## 1.2.1 - 2026-02-14

- Fix map tiles not rendering by moving border-radius to CSS stylesheet

## 1.2.0 - 2026-01-25

- Add marker clustering support with Supercluster.js library
- Fix block.json align property to use proper `supports` wrapper

## 1.1.0 - 2026-01-24

- Archive Map: Use cluster-style icons for individual markers at low zoom levels (≤ 2) for visual consistency

## 1.0.0 - 2026-01-23

- Initial public release
- Post Map block for single post locations
- Category Map block for category-based maps
- Archive Map block for all posts with locations
- Mapbox integration with token stored in Settings
- Leaflet.js 2.0.0-alpha.1 bundled
- ACF Google Map field integration
- Proper output escaping for security
- Accessibility: role and aria-label attributes
- Noscript fallback with Google Maps link
- Filterable post limit via `map_blocks_posts_limit`
