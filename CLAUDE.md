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
2. **Category Map** (`map-blocks/cat-map`) - Displays clustered pins for all posts in a category that have map data; only renders on a category archive page
3. **Archive Map** (`map-blocks/archive-map`) - Displays clustered pins for every published post that has map data; auto-fits the view to the marker bounds

### Data Flow
- Blocks use server-side `render.php` templates (WordPress core native render, no ACF Pro dependency)
- Map data comes from ACF Google Map fields with structure: `{address, lat, lng}`
- Post Map and Archive Map read the per-post `map` field; Archive Map can also read an optional `map` field on the host page as a center
- Category Map fetches center/zoom from category-level ACF fields via `get_field('map', 'category_{id}')` and `get_field('zoom_level', 'category_{id}')`
- Category Map and Archive Map query posts with `meta_query` (key `map`, `compare => EXISTS`) to filter only those with map data
- Category Map and Archive Map cluster markers client-side with the bundled Supercluster library

## Development Notes

### No Build System
This plugin has no npm/webpack build process. Leaflet is pre-bundled in `/lib`. Edit PHP templates directly.

### ACF Field Requirements
- Posts need an ACF field named `map` (Google Map type)
- Categories need ACF fields: `map` (center point) and `zoom_level`
- ACF's Google Map field requires a Google Maps JavaScript API key (set via `acf_update_setting('google_api_key', ...)` on `acf/init`) or the editor picker is blank
- `acf-export.json` (repo root) ships importable field groups matching these requirements

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
