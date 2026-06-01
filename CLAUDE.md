# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Map Blocks is a WordPress plugin that provides Gutenberg blocks for displaying maps using Leaflet.js and Advanced Custom Fields (ACF). The plugin integrates with Mapbox for tile layers.

## Architecture

### Plugin Structure
- `map-blocks.php` - Main plugin file; registers blocks and enqueues Leaflet CSS
- `build/` - Block definitions with `block.json` configs and PHP render templates
- `lib/` - Bundled Leaflet.js ESM library (v2.0.0-alpha.1)

### Blocks
1. **Post Map** (`map-blocks/post-map`) - Displays a single map pin for an individual post using the ACF `map` field
2. **Category Map** (`map-blocks/cat-map`) - Displays clustered pins for all posts in a category that have map data; only renders on a category archive page and auto-focuses the initial view on the densest area of pins
3. **Archive Map** (`map-blocks/archive-map`) - Displays clustered pins for every published post that has map data; auto-fits the view to the marker bounds

### Data Flow
- Blocks use server-side `render.php` templates (WordPress core native render, no ACF Pro dependency)
- Map data comes from ACF Google Map fields with structure: `{address, lat, lng}`
- Post Map and Archive Map read the per-post `map` field; Archive Map can also read an optional `map` field on the host page as a center
- Category Map and Archive Map query posts with `meta_query` (key `map`, `compare => EXISTS`) to filter only those with map data
- Category Map and Archive Map cluster markers client-side with the bundled Supercluster library
- Archive Map fits the initial view to the bounds of all loaded markers
- Category Map auto-focuses the initial view on the densest area of its posts' pins: it trims statistical outliers (median + MAD) and fits the bounds of the survivors, so one far-flung related post (e.g. a London restaurant in a "Tokyo" category) doesn't zoom the whole map out. All pins still load and remain reachable by zooming/panning out — only the opening viewport is affected. No per-category center/zoom configuration is needed.

## Development Notes

### No Build System
This plugin has no npm/webpack build process. Leaflet is pre-bundled in `/lib`. Edit PHP templates directly.

### ACF Field Requirements
- Posts need an ACF field named `map` (Google Map type) — this single per-post field powers all three blocks
- Category Map and Archive Map need no taxonomy- or page-level fields; the legacy `map`/`zoom_level` category group is unused as of 2.3.2
- ACF's Google Map field requires a Google Maps JavaScript API key (set via `acf_update_setting('google_api_key', ...)` on `acf/init`) or the editor picker is blank
- `acf-export.json` (repo root) ships an importable "Post Location" field group matching these requirements (plus the now-unused legacy "Category Map" group)

### Block Registration
Blocks are registered via `block.json` files that reference:
- `style`: Pre-registered `lib-css-map-blocks` handle (Category/Archive maps also use `lib-css-map-blocks-cluster`)
- `render`: PHP file (`render.php`) for server-side rendering

### Map Rendering Pattern
Templates generate unique IDs with `uniqid('mb_')` to support multiple map instances per page. Leaflet is loaded as an ES module via inline `<script type="module">` blocks that import from the URL returned by `map_blocks_get_leaflet_url()`.

**Leaflet 2.0 ESM syntax:**
```javascript
import { Map, TileLayer, Marker } from 'leaflet-url';
const map = new Map("id").setView([lat, lng], zoom);
new TileLayer(url, options).addTo(map);
new Marker([lat, lng]).addTo(map).bindPopup(content);
```

**Initial view & popups (per block):**
- Post Map uses a fixed `new Map(id).setView([lat,lng], 16)`. Category Map and Archive Map create the map with no initial view and call `fitBounds()` instead: Archive Map fits all markers (`maxZoom: 6`); Category Map fits the densest subset after trimming outliers via median + MAD (`maxZoom: 16`).
- The Mapbox `TileLayer` is capped at `maxZoom: 20` in all three blocks.
- In the clustered blocks (Category/Archive), individual-pin popups are opened on the **map** (`new Popup({ offset: [1,-34] }).setLatLng(...).openOn(map)`), **not** bound to the marker. `updateClusters()` rebuilds every marker on `moveend`, and popup autoPan also fires `moveend` — so a marker-bound popup would be destroyed the instant it opened near an edge. The `offset` matches the default marker `popupAnchor` so the popup still floats above the pin. Keep this pattern if you touch the popup code.
