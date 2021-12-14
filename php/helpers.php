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

/**
 * Retrieves metadata for a term.
 *
 * The original version of this plugin was written before WordPress's
 * get_term_meta() was available, hence this workaround which stores term
 * metadata in the options table in the database.
 *
 * @param int    $term_id Term ID.
 * @param string $key The meta key to retrieve. By default, returns data for all keys.
 * @return mixed|array If no $key is specified returns the entire metadata array
 * or false if no metadata was found. If $key is specified returns the metadata
 * value for that key or false if there is metadata with that key is found.
 */
function get_tax_term_meta( int $term_id, string $key = '' ) {
	$term_meta = get_option( "taxonomy_{$term_id}" );
	if ( ! $term_meta ) {
		return false;
	}

	if ( $key ) {
		if ( isset( $term_meta[ $key ] ) ) {
			return $term_meta[ $key ];
		}
		return false;
	}

	return $term_meta;
}

/**
 * Updates term metadata.
 *
 * The original version of this plugin was written before WordPress's
 * update_term_meta() was available, hence this workaround which stores term
 * metadata in the options table in the database.
 *
 * @param int    $term_id Term ID.
 * @param string $meta_key Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 */
function update_tax_term_meta( int $term_id, string $meta_key, $meta_value ) {
	$term_meta = get_tax_term_meta( $term_id );
	if ( null === $meta_value ) {
		unset( $term_meta[ $meta_key ] );
	} else {
		$term_meta[ $meta_key ] = $meta_value;
	}
	update_option( "taxonomy_{$term_id}", $term_meta );
}
