<?php
/**
 * A custom taxonomy used to group databases by subject or audience.
 *
 * This taxonomy is very similar to WordPress's post categories, so much so that
 * we can rely on the default interfaces and methods, so this class is very
 * minimal.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases;

/**
 * A helpful wrapper class around the lib_databases_research_areas custom taxonomy.
 */
class Research_Area {
	/**
	 * This is the internal taxonomy name passed to register_taxonomy().
	 *
	 * @var string
	 */
	public static $tax_name = 'lib_databases_research_areas';

	/**
	 * The term_id of this Research_Area instance.
	 *
	 * @var int
	 */
	private $term_id;

	/**
	 * Creates a Research_Area object as a wrapper around the passed WP_Term or
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
	 * Hook into WordPress to setup this taxonomy.
	 */
	public static function register_wp_hooks() {
		add_action( 'init', array( __class__, 'register_taxonomy' ) );
	}

	/**
	 * Register the taxonomy
	 */
	public static function register_taxonomy() {
		register_taxonomy(
			self::$tax_name,
			'lib_databases',
			array(
				'label'          => 'Research Areas',
				'singular_label' => 'Research Area',
				'hierarchical'   => true,
				'show_ui'        => true,
			)
		);
	}
}
