# Map Blocks

Gutenberg blocks for displaying maps using Advanced Custom Fields and Leaflet.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Advanced Custom Fields (Pro or Free), 6.0+
- Mapbox API token (free at [mapbox.com](https://mapbox.com))
- Google Maps JavaScript API key (free tier at [Google Cloud Console](https://console.cloud.google.com/google/maps-apis))

## Blocks Included

- **Post Map** — display a map with a single pin for an individual post
- **Category Map** — show all posts in a category on a clustered map (renders on category archive pages)
- **Archive Map** — show every post that has a location on a single clustered map

## Setup

All three blocks render their pins from ACF fields, so the bulk of setup is wiring up ACF correctly.
The blocks read **specific field names** (`map`, `zoom_level`) — these must match exactly, or the blocks
render nothing.

### 1. Prerequisites: two API keys

| Key | Used for | Where to add it |
|-----|----------|-----------------|
| **Mapbox access token** | Map tiles on the front end | Settings → General → "Mapbox Access Token" |
| **Google Maps API key** | The map picker inside the ACF Google Map field (admin only) | A small code snippet, see below |

The Mapbox token is configured in the WordPress admin; an admin notice will remind you if it is missing.

The Google Maps API key is an ACF requirement, not a Map Blocks one — **without it the Google Map field
in the editor shows a blank box and you can't place pins.** Add it with a snippet in your theme's
`functions.php` or a code-snippets plugin:

```php
add_action('acf/init', function () {
    acf_update_setting('google_api_key', 'YOUR_GOOGLE_MAPS_API_KEY');
});
```

Your Google Maps API key needs the **Maps JavaScript API** and **Geocoding API** enabled in the
Google Cloud Console.

### 2. Required ACF fields

Create these fields (field group setup is walked through below, or import the ready-made groups —
see [Quick start with the ACF import](#quick-start-with-the-acf-import)):

| Block | ACF field name | Field type | Assign the field group to | Notes |
|-------|----------------|-----------|---------------------------|-------|
| Post Map | `map` | Google Map | Post type → Post | One pin per post |
| Archive Map | `map` | Google Map | Post type → Post | Shows all posts that have a `map` value |
| Category Map | `map` | Google Map | Taxonomy → Category | The map's **center point** for the category |
| Category Map | `zoom_level` | Number | Taxonomy → Category | Initial zoom (e.g. `10`) |

The Category Map also reads the per-post `map` field to plot each post, and only displays on a
**category archive page**.

### 3. Step-by-step walkthrough

1. Install and activate **Advanced Custom Fields** (free or Pro) and **Map Blocks**.
2. Go to **Settings → General**, paste your **Mapbox Access Token**, and save.
3. Add the **Google Maps API key** snippet (see [Prerequisites](#1-prerequisites-two-api-keys)).
4. **Post / Archive maps** — Create a field group (e.g. "Post Location"):
   - Add a field, type **Google Map**, field name **`map`**.
   - Set Location rule: **Post Type** is equal to **Post**.
   - Publish.
5. **Category maps** — Create a second field group (e.g. "Category Map"):
   - Add a **Google Map** field named **`map`** (the category's center point).
   - Add a **Number** field named **`zoom_level`** (e.g. `10`).
   - Set Location rule: **Taxonomy** is equal to **Category**.
   - Publish. Then edit a category (Posts → Categories) and set its center + zoom.
6. **Geocode your content** — Edit a post, find the **Map** field, search an address to drop a pin, and update.
7. **Add a block** — In the editor, insert a block and search "map". Choose Post Map (single post),
   Category Map (on a category archive), or Archive Map (anywhere you want all locations).

> **Nothing showing up?** Confirm the field name is exactly `map`, that posts actually have a pin saved,
> and that both API keys are set. The Category Map only renders on a category archive URL.

### Quick start with the ACF import

To skip manual field creation, import the bundled field groups:

1. Go to **ACF → Tools → Import Field Groups**.
2. Upload [`acf-export.json`](acf-export.json) from this repo.
3. Click **Import File**.

This creates the "Post Location" (post `map`) and "Category Map" (category `map` + `zoom_level`)
groups configured exactly as the blocks expect. You still need to set both API keys (step 2–3 above).

## Installation

See [readme.txt](readme.txt) for end-user installation instructions.

## License

GPL-2.0-or-later
