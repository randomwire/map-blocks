<?php
/**
 * Category Map Block
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner HTML (empty).
 * @var WP_Block $block      Block instance.
 */

$id = uniqid('mb_');
$classes = 'map_blocks';
if (!empty($attributes['align'])) {
    $classes .= ' align' . $attributes['align'];
}

// Bail if ACF isn't available.
if (!function_exists('get_field')) {
    return;
}

// Validate category ID.
$cat_id = absint(get_query_var('cat'));
if (empty($cat_id)) {
    return;
}

// Fetch posts with map data for the current category.
$args = array(
    'category' => $cat_id,
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

// Build GeoJSON features array for Supercluster.
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

?>

<?php if (!empty($markers_data)) { ?>
    <div id="<?php echo esc_attr($id); ?>"
         class="<?php echo esc_attr($classes); ?>"
         style="height:400px;border-radius:8px"
         role="application"
         aria-label="<?php echo esc_attr(__('Interactive map showing category locations', 'map-blocks')); ?>">
    </div>
    <noscript>
        <p><?php esc_html_e('Map requires JavaScript.', 'map-blocks'); ?></p>
        <a href="<?php echo esc_url(get_category_link($cat_id)); ?>">
            <?php esc_html_e('View category', 'map-blocks'); ?>
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
                const leafletmap = new Map('<?php echo esc_attr($id); ?>');

                new TileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1,
                    accessToken: '<?php echo esc_attr(map_blocks_get_mapbox_token()); ?>'
                }).addTo(leafletmap);

                // GeoJSON point data from PHP
                const points = <?php echo wp_json_encode($markers_data) ?: '[]'; ?>;

                // Fit the map to the bounds of the category's pins.
                if (points.length > 0) {
                    let minLat = Infinity, maxLat = -Infinity, minLng = Infinity, maxLng = -Infinity;
                    for (const f of points) {
                        const [lng, lat] = f.geometry.coordinates;
                        if (lat < minLat) minLat = lat;
                        if (lat > maxLat) maxLat = lat;
                        if (lng < minLng) minLng = lng;
                        if (lng > maxLng) maxLng = lng;
                    }
                    leafletmap.fitBounds([[minLat, minLng], [maxLat, maxLng]], { padding: [20, 20], maxZoom: 6 });
                } else {
                    leafletmap.setView([20, 0], 2);
                }

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

                            clusterMarker.on('click', () => {
                                const expansionZoom = index.getClusterExpansionZoom(clusterId);
                                leafletmap.setView([lat, lng], expansionZoom);
                            });

                            clusterMarker.addTo(markersLayer);
                        } else {
                            const popupHtml = `<a href="${feature.properties.url}">${feature.properties.title}</a>`;

                            if (zoom <= 2) {
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
<?php } ?>
