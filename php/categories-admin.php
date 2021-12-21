<?php
/**
 * Admin code for the custom taxonomy lib_databases_categories.
 *
 * All necessary hooks are added when a new instance is created.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases\CategoriesAdmin;

use ForbesLibrary\WordPress\LibraryDatabases\Access_Category;
use ForbesLibrary\WordPress\LibraryDatabases\Database;
use function ForbesLibrary\WordPress\LibraryDatabases\Helpers\get_tax_term_meta;
use function ForbesLibrary\WordPress\LibraryDatabases\Helpers\update_tax_term_meta;

/**
 * Outputs the html for the database availability box on the lib_databases
 * edit page.
 */
function output_database_availability_metabox() {
	load_template( dirname( __FILE__ ) . '/template-parts/admin-database-availability-metabox.php' );
}

/**
 * Replaces the default lib_databases_categories metabox.
 *
 * The default metabox would let the user select multiple categories. Our own
 * metabox, which replaces this, only lets the user select one category.
 */
add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box(
			'tagsdiv-' . Access_Category::TAX_NAME,
			Database::POST_TYPE_KEY,
			'side'
		);
		add_meta_box(
			'database-availability-meta',
			__( 'Database Availability' ),
			__NAMESPACE__ . '\output_database_availability_metabox',
			Database::POST_TYPE_KEY,
			'side',
			'high',
		);
	}
);

/**
 * Modify the columns in the admin interface.
 *
 * @param string[] $columns The column header labels keyed by column ID.
 * @return string[] The column header labels keyed by column ID.
 */
add_filter(
	'manage_edit-' . Access_Category::TAX_NAME . '_columns',
	function ( array $columns ) {
		$first_columns  = array_slice( $columns, 0, 2 );
		$last_columns   = array_slice( $columns, 2 );
		$custom_columns = array(
			Access_Category::TAX_NAME . '-image' => __( 'Image' ),
			Access_Category::TAX_NAME . '-library-use-only' => __( 'Library Use Only' ),
		);
		return array_merge( $first_columns, $custom_columns, $last_columns );
	}
);

/**
 * Return content for custom columns.
 *
 * @wp-hook manage_{$this->screen->taxonomy}_custom_column
 * @see https://developer.wordpress.org/reference/hooks/manage_this-screen-taxonomy_custom_column/
 * @param string $value The value for this column. This will be ignored.
 * @param string $column_name Name of the column.
 * @param int    $term_id Term ID.
 */
add_filter(
	'manage_' . Access_Category::TAX_NAME . '_custom_column',
	function ( string $value, string $column_name, int $term_id ) {
		$term_meta = get_tax_term_meta( $term_id );
		switch ( $column_name ) {
			case Access_Category::TAX_NAME . '-image':
				if ( isset( $term_meta['image'] ) ) {
					$value = wp_get_attachment_image( $term_meta['image'], array( 32, 32 ) );
				}
				break;

			case Access_Category::TAX_NAME . '-library-use-only':
				if ( isset( $term_meta['library_use_only'] ) ) {
					$value = ( $term_meta['library_use_only'] ? 'yes' : 'no' );
				} else {
					$value = 'no';
				}
		}
		return $value;
	},
	10,
	3
);

/**
 * Save Access_Category custom fields to the database.
 *
 * @param int   $term_id Term ID.
 * @param array $term_meta An array of metadata to be saved.
 */
function save( int $term_id, $term_meta ) {
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		wp_die(
			esc_html__( 'You do not have permission to edit this.' ),
			esc_html__( 'Something went wrong.' ),
			403
		);
	}

	if ( isset( $term_meta['postfix'] ) ) {
		$postfix = sanitize_text_field( $term_meta['postfix'] );
		update_tax_term_meta( $term_id, 'postfix', $postfix );
	} else {
		update_tax_term_meta( $term_id, 'postfix', null );
	}

	if ( isset( $term_meta['library_use_only'] ) ) {
		update_tax_term_meta( $term_id, 'library_use_only', true );
	} else {
		update_tax_term_meta( $term_id, 'library_use_only', false );
	}

	if ( isset( $term_meta['image'] ) && is_numeric( $term_meta['image'] ) ) {
		update_tax_term_meta( $term_id, 'image', intval( $term_meta['image'] ) );
	} else {
		update_tax_term_meta( $term_id, 'image', null );
	}
}

/**
 * Hook for term creation
 *
 * @param int $term_id Term ID.
 */
add_action(
	'create_' . Access_Category::TAX_NAME,
	function ( int $term_id ) {
		if ( ! isset( $_POST['library-databases-access-category-nonce'] ) ) {
			wp_die(
				esc_html__( 'Security Error: nonce is missing.' ),
				esc_html__( 'Something went wrong.' ),
				403
			);
		}

		// Unslashing and sanitizing are not necessary for wp_verify_nonce().
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = wp_verify_nonce(
			$_POST['library-databases-access-category-nonce'],
			'access-category/add'
		);
		if ( ! $nonce ) {
			wp_nonce_ays( 'access-category/add' );
		}
		// phpcs:enable

		if ( ! isset( $_POST['term_meta'] ) ) {
			return;
		}

		// We leave the burden of handling the passed data safely to save().
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		save( $term_id, wp_unslash( $_POST['term_meta'] ) );
	}
);

/**
 * Hook for term edit
 *
 * @param int $term_id Term ID.
 */
add_action(
	'edit_' . Access_Category::TAX_NAME,
	function ( int $term_id ) {
		if ( ! isset( $_POST['library-databases-access-category-nonce'] ) ) {
			wp_die(
				esc_html__( 'Security Error: nonce is missing.' ),
				esc_html__( 'Something went wrong.' ),
				403
			);
		}

		// Unslashing and sanitizing are not necessary for wp_verify_nonce().
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = wp_verify_nonce(
			$_POST['library-databases-access-category-nonce'],
			'access-category/edit' . $term_id
		);
		if ( ! $nonce ) {
			wp_nonce_ays( 'access-category/edit' . $term_id );
		}
		// phpcs:enable

		if ( ! isset( $_POST['term_meta'] ) ) {
			return;
		}

		// We leave the burden of handling the passed data safely to save().
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		save( $term_id, wp_unslash( $_POST['term_meta'] ) );
	}
);

/**
 * Echos the html for the custom fields in the new access category box.
 *
 * @wp-hook "{$taxonomy}_add_form_fields"
 * @see https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/
 */
add_action(
	Access_Category::TAX_NAME . '_add_form_fields',
	function () {
		wp_nonce_field(
			'access-category/add',
			'library-databases-access-category-nonce'
		);
		load_template(
			dirname( __FILE__ ) . '/template-parts/admin-access-category-create-form-fields.php',
			true
		);
	}
);

/**
 * Returns the html for the custom fields in the edit database access category
 * metabox.
 *
 * @param \WP_Term $term The term being edited.
 */
add_action(
	Access_Category::TAX_NAME . '_edit_form_fields',
	function ( \WP_Term $term ) {
		wp_nonce_field(
			'access-category/edit' . $term->term_id,
			'library-databases-access-category-nonce'
		);
		load_template(
			dirname( __FILE__ ) . '/template-parts/admin-access-category-edit-form-fields.php',
			true,
			array( 'category' => new Access_Category( $term ) )
		);
	}
);

/**
 * Add JavaScript to get URL from media uploader.
 */
add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( 'term' !== $screen->base && 'edit-tags' !== $screen->base ) {
			return;
		}
		if ( Access_Category::TAX_NAME !== $screen->taxonomy ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'library-databases-admin-js' );
		wp_add_inline_script(
			'library-databases-admin-js',
			'jQuery(document).ready( addImageUploadFunctionality );'
		);
	}
);
