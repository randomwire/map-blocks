# Changelog

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
