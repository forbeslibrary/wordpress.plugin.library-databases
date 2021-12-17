<?php
/**
 * A custom taxonomy used to group databases by access rules such as
 * 'free to all' or 'requires a library card'.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases;

use ForbesLibrary\WordPress\LibraryDatabases\Database;
use function ForbesLibrary\WordPress\LibraryDatabases\Helpers\get_tax_term_meta;

/**
 * A helpful wrapper class around the lib_databases_categories custom taxonomy.
 */
class Access_Category {
	/**
	 * This is the internal taxonomy name passed to register_taxonomy().
	 *
	 * @var string
	 */
	public const TAX_NAME = 'lib_databases_categories';

	/**
	 * The term_id of this Access_Category instance.
	 *
	 * @var int
	 */
	private $term_id;

	/**
	 * Creates a Access_Category object as a wrapper around the passed WP_Term or
	 * the term with the specified id.
	 *
	 * @param int|WP_Term $term A WP_Term object or term id.
	 */
	public function __construct( $term ) {
		if ( isset( $term->term_id ) ) {
			$this->term_id = $term->term_id;
		} else {
			$this->term_id = $term;
		}
	}

	/**
	 * Hook into WordPress to register this taxonomy.
	 *
	 * @wp-hook init
	 */
	public static function register_wp_hooks() {
		add_action(
			'init',
			function () {
				register_taxonomy(
					self::TAX_NAME,
					Database::POST_TYPE_KEY,
					array(
						'label'        => 'Access Categories',
						'labels'       => array(
							'singular_label' => 'Access Category',
							'add_new_item'   => 'Add Access Category',
							'edit_item'      => 'Edit Access Category',
							'search_items'   => 'Search Access Categories',
							'popular_items'  => null,
						),
						'hierarchical' => false,
						'rewrite'      => array(
							'slug'       => 'databases-access',
							'with_front' => false,
						),
						'show_ui'      => true,
						'capabilities' => array(
							'manage_terms' => 'manage_options',
							'edit_terms'   => 'manage_options',
							'delete_terms' => 'manage_options',
							'assign_terms' => 'edit_posts',
						),
					)
				);
			}
		);
	}

	/**
	 * Returns all the terms for this taxonomy.
	 *
	 * @return WP_Term[] An array of WP_Term objects (not Access_Category
	 * objects!)
	 */
	public static function get_terms() {
		return get_terms( self::TAX_NAME, array( 'hide_empty' => 0 ) );
	}

	/**
	 * Get the term id.
	 *
	 * @return int The term ID.
	 */
	public function get_id() {
		return $this->term_id;
	}

	/**
	 * Returns the description for this Access_Category.
	 */
	public function get_description() {
		return term_description( $this->term_id, self::TAX_NAME );
	}

	/**
	 * Shows the description for this Access_Category.
	 */
	public function show_description() {
		echo wp_kses_post( $this->get_description() );
	}

	/**
	 * Returns an <img> tag for the image for this Access_Category.
	 */
	public function get_image() {
		$term_image_id = intval( get_tax_term_meta( $this->term_id, 'image' ) );
		$term          = get_term( $this->term_id );
		if ( $term_image_id ) {
			return wp_get_attachment_image(
				$term_image_id,
				'thumbnail',
				'true',
				array(
					'class' => 'lib_databases_category_image',
					'alt'   => $term->name,
				)
			);
		}
		return '';
	}

	/**
	 * Outputs an <img> tag for the image for this Access_Category.
	 */
	public function show_image() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_image();
	}

	/**
	 * Returns the title postfix for select menus for this Access_Category.
	 *
	 * The title postfix is added to the title of databases in the select menu
	 * to provide information to the library patron about the access requirements
	 * for the database. This will be the only information about the
	 * Access_Category visible to the patron in the select menu.
	 *
	 * @return string Postfix.
	 */
	public function get_postfix() {
		$postfix = get_tax_term_meta( $this->term_id, 'postfix' );
		if ( $postfix ) {
			return $postfix;
		}
		return '';
	}

	/**
	 * Returns true if the lib_databases_categories term associated
	 * with the given id is restricted by IP address.
	 */
	public function is_restricted_by_ip() {
		$term_meta = $this->get_metadata();
		if ( isset( $term_meta['library_use_only'] ) ) {
			return $term_meta['library_use_only'];
		}
		return false;
	}

	/**
	 * Retrieves metadata for this Access_Category.
	 *
	 * @param ?string $field The field to be retrieved. If no field is specified
	 * the full metadata array will be returned.
	 *
	 * @return mixed The value of the requested field or n array of metadata field
	 * names and values if no field was specified.
	 */
	public function get_metadata( ?string $field = null ) {
		if ( $field ) {
			return get_tax_term_meta( $this->term_id, $field );
		}

		return get_tax_term_meta( $this->term_id );
	}
}
