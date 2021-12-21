<?php
/**
 * Defines helpful wrapper class around the lib_databases custom post type.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases;

use ForbesLibrary\WordPress\LibraryDatabases\Access_Category;
use function ForbesLibrary\WordPress\LibraryDatabases\Helpers\user_in_library;

/**
 * A helpful wrapper class around the lib_databases custom post type.
 */
class Database {
	/**
	 * This is the internal post type key passed to register_post_type().
	 *
	 * @var string
	 */
	public const POST_TYPE_KEY = 'lib_databases';

	/**
	 * The WP_Post object for this database.
	 *
	 * @var WP_Post
	 */
	private $post_object;

	/**
	 * Creates a new Database object from the passed post or post id.
	 *
	 * @param int|WP_Post $post The WP_Post object or the post id of the database.
	 */
	public function __construct( $post ) {
		$this->post_object = get_post( $post );
	}

	/**
	 * Hook into WordPress to register this custom post type.
	 *
	 * @wp-hook init
	 */
	public static function register_wp_hooks() {
		add_action(
			'init',
			function () {
				$labels = array(
					'name'               => __( 'Databases' ),
					'singular_name'      => __( 'Database' ),
					'add_new'            => _x( 'Add New', 'database' ),
					'add_new_item'       => __( 'Add New Database' ),
					'edit_item'          => __( 'Edit Database' ),
					'new_item'           => __( 'New Database' ),
					'view_item'          => __( 'View Database Page' ),
					'search_items'       => __( 'Search Databases' ),
					'not_found'          => __( 'Nothing found' ),
					'not_found_in_trash' => __( 'Nothing found in Trash' ),
					'parent_item_colon'  => '',
				);

				$args = array(
					'has_archive'        => true,
					'labels'             => $labels,
					'public'             => true,
					'publicly_queryable' => true,
					'show_ui'            => true,
					'query_var'          => true,
					'rewrite'            => array(
						'slug'       => 'databases',
						'with_front' => false,
					),
					'capability_type'    => 'post',
					'hierarchical'       => false,
					'menu_icon'          => 'dashicons-admin-site',
					'menu_position'      => 5, // After Posts but before Media.
					'supports'           => array( 'title', 'editor' ),
				);
				register_post_type( self::POST_TYPE_KEY, $args );
			}
		);
	}

	/**
	 * Returns a Database object for the passed post or post id.
	 *
	 * This is equivalent to creating a new Database object, but has better
	 * semantics.
	 *
	 * @param int|WP_Post $post The WP_Post object or the post id of the database.
	 */
	public static function get_object( $post ) {
		return new Database( $post );
	}

	/**
	 * Is the database inaccessible to the user?
	 *
	 * Returns TRUE for remote users if the database is in library use only.
	 */
	public function is_inaccessible() {
		if ( $this->is_restricted_by_ip() && ! user_in_library() ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns true if this database is restricted by ip.
	 *
	 * This restrictiona actually comes from the Access_Category.
	 */
	public function is_restricted_by_ip() : bool {
		if ( $this->get_category() ) {
			return ( $this->get_category() )->is_restricted_by_ip();
		}
		return false;
	}

	/**
	 * Returns the lib_databases_categories term for this database.
	 *
	 * If for some reason this returns multiple categories we will return just the
	 * first.
	 *
	 * @return Access_Category|null
	 */
	public function get_category() {
		$postterms = get_the_terms( $this->post_object->ID, Access_Category::TAX_NAME );

		if ( is_array( $postterms ) ) {
			return new Access_Category( array_pop( $postterms ) );
		}

		return null;
	}

	/**
	 * Returns an image tag for the media for the lib_databases_categories term
	 * associated with this database.
	 */
	public function get_availability_icon() {
		if ( $this->get_category() ) {
			return ( $this->get_category() )->get_image();
		}
		return '';
	}

	/**
	 * Outputs an <img> tag for the media for the lib_databases_categories term
	 * associated with this database.
	 */
	public function show_availability_icon() {
		if ( $this->get_category() ) {
			$this->get_category()->show_image();
		}
	}

	/**
	 * Returns the description for the lib_databases_categories term associated
	 * with this database.
	 */
	public function get_category_description() {
		if ( $this->get_category() ) {
			return ( $this->get_category() )->get_description();
		}
		return '';
	}

	/**
	 * Shows the description for the lib_databases_categories term associated
	 * with this database.
	 */
	public function show_category_description() {
		if ( $this->get_category() ) {
			$this->get_category()->show_description();
		}
	}

	/**
	 * Returns the title as it should appear in a select menu.
	 *
	 * @return string The title with HTML tags removed and the category postfix
	 * added, if defined.
	 */
	public function get_title_for_select_menu() {
		$title   = $this->post_object->post_title;
		$postfix = $this->get_category()->get_postfix();
		if ( $postfix ) {
			$title = $title . ' (' . $postfix . ')';
		}
		return wp_strip_all_tags( $title );
	}

	/**
	 * Returns the URL needed to access the database.
	 *
	 * The URL returned will be the home use url if and only it has been
	 * defined and the user is outside of the library.
	 */
	public function get_database_url() {
		$custom = get_post_custom( $this->post_object->ID );

		$database_main_url     = $custom['database_main_url'][0];
		$database_home_use_url = $custom['database_home_use_url'][0];

		if ( $database_home_use_url && ! user_in_library() ) {
			return $database_home_use_url;
		}
		return $database_main_url;
	}

	/**
	 * Echos the URL needed to access the database.
	 *
	 * The URL shown will be the home use url if and only it has been defined
	 *  and the user is outside of the library.
	 */
	public function show_database_url() {
		echo esc_url( $this->get_database_url() );
	}

	/**
	 * Returns a simple HTML rendering of the database.
	 */
	public function render() {
		ob_start();
		load_template(
			dirname( __FILE__ ) . '/template-parts/content-library-database.php',
			false
		);
		return ob_get_clean();
	}

	/**
	 * Echos a simple HTML rendering of the database.
	 */
	public function show() {
		// We have already escaped everything necessary in the template in render().
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render();
	}
}
