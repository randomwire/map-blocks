<?php
/**
 * Archive Map Block
 *
 * Displays all posts with a location on a map, without category filtering.
 *
 * Required ACF fields for this block:
 * - map (Google Map): Center point for the map
 * - zoom_level (Number): Initial zoom level
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 * @param   array $context The context provided to the block by the post or its parent block.
 */

$id = uniqid('mb_');
$classes = 'map_blocks';

// Initialize defaults (Rome center as fallback).
$map_zoom = 1;
$map_lat = 41.90;
$map_lng = 12.50;
$map_address = 'Rome';

// Load block-level map values if set.
$map_data = get_field('map');
if ($map_data && is_array($map_data)) {
    $map_lat = isset($map_data['lat']) ? floatval($map_data['lat']) : $map_lat;
    $map_lng = isset($map_data['lng']) ? floatval($map_data['lng']) : $map_lng;
    $map_address = isset($map_data['address']) ? sanitize_text_field($map_data['address']) : $map_address;
}

//$zoom_data = get_field('zoom_level');
//if ($zoom_data) {
//    $map_zoom = absint($zoom_data);
//}

// Fetch all posts with map data (no category filter).
$args = array(
    'numberposts' => apply_filters('map_blocks_posts_limit', 2000),
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => 'map',
            'compare' => 'EXISTS'
        )
    )
);
$myposts = get_posts($args);

// Build noscript fallback URL.
$noscript_url = 'https://maps.google.com/maps?q=' . urlencode($map_address);
?>

<div id="<?php echo esc_attr($id); ?>"
         class="<?php echo esc_attr($classes); ?>"
         style="height:400px"
         role="application"
         aria-label="<?php echo esc_attr(__('Interactive map showing all post locations', 'map-blocks')); ?>">
    </div>
    <noscript>
        <p><?php esc_html_e('Map requires JavaScript.', 'map-blocks'); ?></p>
        <a href="<?php echo esc_url($noscript_url); ?>">
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
                const leafletmap = new Map('<?php echo esc_attr($id); ?>').setView([<?php echo floatval($map_lat); ?>, <?php echo floatval($map_lng); ?>], <?php echo absint($map_zoom); ?>);

                new TileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1,
                    accessToken: '<?php echo esc_attr(map_blocks_get_mapbox_token()); ?>'
                }).addTo(leafletmap);

                <?php
                foreach ($myposts as $post) {
                    setup_postdata($post);
                    $post_location = get_field('map', $post);

                    if ($post_location && is_array($post_location) && isset($post_location['lat']) && isset($post_location['lng'])) {
                        $post_lat = floatval($post_location['lat']);
                        $post_lng = floatval($post_location['lng']);
                        $post_title = esc_html(get_the_title($post));
                        $post_url = esc_url(get_permalink($post));
                        $popup_html = '<a href="' . $post_url . '">' . $post_title . '</a>';
                        ?>
                        new Marker([<?php echo $post_lat; ?>, <?php echo $post_lng; ?>]).addTo(leafletmap)
                            .bindPopup(<?php echo wp_json_encode($popup_html); ?>);
                        <?php
                    }
                }
                wp_reset_postdata();
                ?>
            } catch (error) {
                console.error('Map Blocks: Failed to initialize map', error);
            }
        }
    </script>
