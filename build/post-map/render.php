<?php
/**
 * Post Map Block
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

$id = uniqid('mb_');
$classes = 'map_blocks';
if (!empty($block['align'])) {
    $classes .= ' align' . $block['align'];
}

// Initialize defaults.
$map = null;
$map_address = '';
$map_lat = 0;
$map_lng = 0;

// Load values and validate.
$map_data = get_field('map', $post_id);
if ($map_data && is_array($map_data)) {
    $map = $map_data;
    $map_address = isset($map_data['address']) ? sanitize_text_field($map_data['address']) : '';
    $map_lat = isset($map_data['lat']) ? floatval($map_data['lat']) : 0;
    $map_lng = isset($map_data['lng']) ? floatval($map_data['lng']) : 0;
}

// Build popup HTML for JSON encoding.
$popup_url = 'https://maps.google.com/maps?q=' . urlencode($map_address);
$popup_html = '<a href="' . esc_url($popup_url) . '" target="_blank">' . esc_html($map_address) . '</a>';
?>

<?php if (!empty($map)) { ?>

    <div id="<?php echo esc_attr($id); ?>"
         class="<?php echo esc_attr($classes); ?>"
         style="height:300px;border-radius:8px"
         role="application"
         aria-label="<?php echo esc_attr(__('Interactive map', 'map-blocks')); ?>">
    </div>
    <noscript>
        <p><?php esc_html_e('Map requires JavaScript.', 'map-blocks'); ?></p>
        <a href="<?php echo esc_url($popup_url); ?>">
            <?php esc_html_e('View in Google Maps', 'map-blocks'); ?>
        </a>
    </noscript>

    <script type="module">
        import { Map, TileLayer, Marker, Icon } from '<?php echo esc_url(map_blocks_get_leaflet_url()); ?>';

        // Fix default marker icon path for ES module loading
        Icon.Default.prototype.options.imagePath = '<?php echo esc_url(plugins_url('/lib/images/', dirname(__FILE__, 2))); ?>';

        const mapElement = document.getElementById('<?php echo esc_attr($id); ?>');
        if (!mapElement) {
            console.error('Map Blocks: Map element not found');
        } else {
            try {
                const leafletmap = new Map('<?php echo esc_attr($id); ?>').setView([<?php echo floatval($map_lat); ?>, <?php echo floatval($map_lng); ?>], 16);

                new TileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1,
                    accessToken: '<?php echo esc_attr(map_blocks_get_mapbox_token()); ?>'
                }).addTo(leafletmap);

                const marker = new Marker([<?php echo floatval($map_lat); ?>, <?php echo floatval($map_lng); ?>]).addTo(leafletmap);
                marker.bindPopup(<?php echo wp_json_encode($popup_html); ?>);
            } catch (error) {
                console.error('Map Blocks: Failed to initialize map', error);
            }
        }
    </script>

<?php } ?>
