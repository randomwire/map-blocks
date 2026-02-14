<?php
/**
 * Plugin Name:       Map Blocks
 * Plugin URI:        https://randomwire.com/plugins/map-blocks/
 * Description:       Gutenberg blocks for displaying maps using Advanced Custom Fields and Leaflet.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            David Gilbert
 * Author URI:        https://randomwire.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       map-blocks
 */


//  Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

function map_blocks_register() {
	// Enqueue lib assets
	$lib_style_path = '/lib/leaflet.css';
	$lib_version = '2.0.0-alpha.1';

	wp_register_style( 'lib-css-map-blocks', plugins_url($lib_style_path, __FILE__), array(), $lib_version );

	// Register cluster CSS
	wp_register_style(
		'lib-css-map-blocks-cluster',
		plugins_url('/lib/cluster.css', __FILE__),
		array('lib-css-map-blocks'),
		'1.0.0'
	);

	// Register blocks
	register_block_type( __DIR__ . '/build/post-map');
	register_block_type( __DIR__ . '/build/cat-map');
	register_block_type( __DIR__ . '/build/archive-map');
}

function map_blocks_get_leaflet_url() {
	return plugins_url('/lib/leaflet.js', __FILE__);
}

function map_blocks_get_supercluster_url() {
	return plugins_url('/lib/supercluster.js', __FILE__);
}

/**
 * Register settings for Mapbox API token.
 */
function map_blocks_register_settings() {
	register_setting('general', 'map_blocks_mapbox_token', array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	));

	add_settings_field(
		'map_blocks_mapbox_token',
		__('Mapbox Access Token', 'map-blocks'),
		'map_blocks_token_field_callback',
		'general'
	);
}

/**
 * Render the Mapbox token settings field.
 */
function map_blocks_token_field_callback() {
	$token = get_option('map_blocks_mapbox_token', '');
	?>
	<input type="text" id="map_blocks_mapbox_token" name="map_blocks_mapbox_token"
		value="<?php echo esc_attr($token); ?>" class="regular-text" />
	<p class="description">
		<?php esc_html_e('Enter your Mapbox access token for map tile layers.', 'map-blocks'); ?>
	</p>
	<?php
}

/**
 * Get the Mapbox access token from settings.
 *
 * @return string The Mapbox access token.
 */
function map_blocks_get_mapbox_token() {
	return get_option('map_blocks_mapbox_token', '');
}

add_action('init', 'map_blocks_register');
add_action('admin_init', 'map_blocks_register_settings');