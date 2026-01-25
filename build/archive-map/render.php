<?php
/**
 * Archive Map Block
 *
 * Displays all posts with a location on a map with marker clustering.
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
if (!empty($block['align'])) {
    $classes .= ' align' . $block['align'];
}

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

// Build GeoJSON features array for Supercluster
$markers_data = array();
foreach ($myposts as $post) {
    $post_location = get_field('map', $post);
    if ($post_location && is_array($post_location) && isset($post_location['lat']) && isset($post_location['lng'])) {
        $markers_data[] = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(
                    floatval($post_location['lng']),  // GeoJSON uses [lng, lat]
                    floatval($post_location['lat'])
                )
            ),
            'properties' => array(
                'title' => esc_html(get_the_title($post)),
                'url' => esc_url(get_permalink($post))
            )
        );
    }
}

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
        import { Map, TileLayer, Marker, Icon, DivIcon, FeatureGroup } from '<?php echo esc_url(map_blocks_get_leaflet_url()); ?>';
        import Supercluster from '<?php echo esc_url(map_blocks_get_supercluster_url()); ?>';

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

                // GeoJSON point data from PHP
                const points = <?php echo wp_json_encode($markers_data); ?>;

                // Initialize Supercluster
                const index = new Supercluster({
                    radius: 60,
                    maxZoom: 16,
                    minPoints: 2
                });
                index.load(points);

                // Layer group for markers
                const markersLayer = new FeatureGroup().addTo(leafletmap);

                // Get cluster size class
                function getClusterClass(count) {
                    if (count < 10) return 'map-blocks-cluster-small';
                    if (count < 100) return 'map-blocks-cluster-medium';
                    return 'map-blocks-cluster-large';
                }

                // Update clusters on map move/zoom
                function updateClusters() {
                    markersLayer.clearLayers();

                    const bounds = leafletmap.getBounds();
                    const bbox = [
                        bounds.getWest(),
                        bounds.getSouth(),
                        bounds.getEast(),
                        bounds.getNorth()
                    ];
                    const zoom = Math.floor(leafletmap.getZoom());

                    const clusters = index.getClusters(bbox, zoom);

                    clusters.forEach(feature => {
                        const [lng, lat] = feature.geometry.coordinates;

                        if (feature.properties.cluster) {
                            // Cluster marker
                            const count = feature.properties.point_count;
                            const clusterId = feature.properties.cluster_id;
                            const sizeClass = getClusterClass(count);

                            const clusterIcon = new DivIcon({
                                html: `<div class="map-blocks-cluster ${sizeClass}">${feature.properties.point_count_abbreviated}</div>`,
                                className: '',
                                iconSize: [40, 40],
                                iconAnchor: [20, 20]
                            });

                            const clusterMarker = new Marker([lat, lng], { icon: clusterIcon });

                            // Click to zoom into cluster
                            clusterMarker.on('click', () => {
                                const expansionZoom = index.getClusterExpansionZoom(clusterId);
                                leafletmap.setView([lat, lng], expansionZoom);
                            });

                            clusterMarker.addTo(markersLayer);
                        } else {
                            // Individual marker
                            const popupHtml = `<a href="${feature.properties.url}">${feature.properties.title}</a>`;

                            if (zoom <= 2) {
                                // At low zoom, use cluster style for visual consistency
                                const singleIcon = new DivIcon({
                                    html: `<div class="map-blocks-cluster map-blocks-cluster-small">1</div>`,
                                    className: '',
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16]
                                });
                                const marker = new Marker([lat, lng], { icon: singleIcon });
                                marker.bindPopup(popupHtml);
                                marker.addTo(markersLayer);
                            } else {
                                // Normal pin marker at higher zoom
                                const marker = new Marker([lat, lng]);
                                marker.bindPopup(popupHtml);
                                marker.addTo(markersLayer);
                            }
                        }
                    });
                }

                leafletmap.on('moveend', updateClusters);
                updateClusters();

            } catch (error) {
                console.error('Map Blocks: Failed to initialize map', error);
            }
        }
    </script>
