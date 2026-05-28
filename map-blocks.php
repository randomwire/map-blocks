<?php
/**
 * Plugin Name:       Map Blocks
 * Plugin URI:        https://github.com/randomwire/map-blocks
 * Description:       Gutenberg blocks for displaying maps using Advanced Custom Fields and Leaflet.
 * Version:           2.2.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  advanced-custom-fields
 * Author:            David Gilbert
 * Author URI:        https://randomwire.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       map-blocks
 */


//  Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

define('MAP_BLOCKS_VERSION', '2.2.0');

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
	return plugins_url('/lib/leaflet.js', __FILE__) . '?ver=' . MAP_BLOCKS_VERSION;
}

function map_blocks_get_supercluster_url() {
	return plugins_url('/lib/supercluster.js', __FILE__) . '?ver=' . MAP_BLOCKS_VERSION;
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

/**
 * Show an admin notice when the Mapbox access token has not been configured.
 */
function map_blocks_token_missing_notice() {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (map_blocks_get_mapbox_token() !== '') {
		return;
	}
	echo '<div class="notice notice-warning"><p>' .
		wp_kses_post(sprintf(
			/* translators: %s: Settings → General URL */
			__('Map Blocks needs a Mapbox access token to display map tiles. <a href="%s">Add one in Settings → General</a>.', 'map-blocks'),
			esc_url(admin_url('options-general.php#map_blocks_mapbox_token'))
		)) .
		'</p></div>';
}

add_action('init', 'map_blocks_register');
add_action('admin_init', 'map_blocks_register_settings');
add_action('admin_notices', 'map_blocks_token_missing_notice');