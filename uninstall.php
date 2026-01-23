<?php
/**
 * Uninstall Map Blocks
 *
 * @package MapBlocks
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove Mapbox token option.
delete_option( 'map_blocks_mapbox_token' );
