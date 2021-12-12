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
		$postterms = get_the_terms( $this->post_object->ID, Access_Category::$tax_name );

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
	 * Returns the title postfix for select menus for the lib_databases_categories
	 * term associated with this database.
	 */
	public function get_category_title_postfix() {
		return ( $this->get_category() )->get_postfix();
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
		ob_start();?>
		<?php load_template( dirname( __FILE__ ) . '/templates/partials/content-library-database.php', false ); ?>
		<?php
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
