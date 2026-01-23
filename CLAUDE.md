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
2. **Category Map** (`map-blocks/cat-map`) - Displays pins for all posts in a category that have map data

### Data Flow
- Blocks use ACF rendering mode with PHP templates
- Map data comes from ACF Google Map fields with structure: `{address, lat, lng}`
- Category maps fetch center/zoom from category-level ACF fields (`category_{id}` prefix)
- Posts are queried with `meta_query` to filter only those with map data

## Development Notes

### No Build System
This plugin has no npm/webpack build process. Leaflet is pre-bundled in `/lib`. Edit PHP templates directly.

### ACF Field Requirements
- Posts need an ACF field named `map` (Google Map type)
- Categories need ACF fields: `map` (center point) and `zoom_level`

### Block Registration
Blocks are registered via `block.json` files that reference:
- `style`: Pre-registered `lib-css-map-blocks` handle
- `acf.renderTemplate`: PHP file for server-side rendering

### Map Rendering Pattern
Templates generate unique IDs with `uniqid('mb_')` to support multiple map instances per page. Leaflet is loaded as an ES module via inline `<script type="module">` blocks that import from the URL returned by `map_blocks_get_leaflet_url()`.

**Leaflet 2.0 ESM syntax:**
```javascript
import { Map, TileLayer, Marker } from 'leaflet-url';
const map = new Map("id").setView([lat, lng], zoom);
new TileLayer(url, options).addTo(map);
new Marker([lat, lng]).addTo(map).bindPopup(content);
```
