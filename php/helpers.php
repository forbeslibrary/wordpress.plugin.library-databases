<?php
/**
 * Helper functions for the Library Databases plugin.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases\Helpers;

/**
 * Is the user in the library?
 */
function user_in_library() {
	$in_library_ip_addresses = explode(
		"\n",
		str_replace(
			"\r",
			'',
			get_option( 'lib_databases_settings_ip_addresses' )
		)
	);
	if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
		return false;
	}

	return in_array( $_SERVER['REMOTE_ADDR'], $in_library_ip_addresses, true );
}
