<?php
/**
 * Shortcodes for the Library Databases plugin.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases\Shortcodes;

/** Make sure the Unique_ID class definition has been loaded. */
require_once 'class-unique-id.php';

use ForbesLibrary\WordPress\LibraryDatabases\Unique_ID;
use ForbesLibrary\WordPress\LibraryDatabases\Database;
use ForbesLibrary\WordPress\LibraryDatabases\Access_Category;
use ForbesLibrary\WordPress\LibraryDatabases\Research_Area;
use WP_Query;

/**
 * The lib_database_list shortcode, for listing lib_databases.
 *
 * Accepted shortcode attributes are:
 * - `research_area=slug` show only databases in the research area with the
 *   given slug
 * - `exclude_category=slug` show only databases which are not in the category
 *   with the given slug
 * - `count` The number of databases to return (defaults to 200)
 *
 * @wp-hook add_shortcode
 * @param array|string $atts An associative array of attributes set on the
 *                     shortcode, or an empty string if no attributes are given.
 * @param string       $content The content enclosed by the shortcode.
 */
function lib_database_list( $atts, ?string $content = null ) {
	if ( is_search() ) {
		// Do not expand shortcode on search results page.
		return '';
	}
	$atts = shortcode_atts(
		array(
			'research_area'    => null,
			'exclude_category' => null,
			'count'            => 200,
		),
		$atts
	);

	// Shortcode argument sanitization.
	$atts['research_area']    = sanitize_title( $atts['research_area'] );
	$atts['exclude_category'] = sanitize_title( $atts['exclude_category'] );
	$atts['count']            = intval( $atts['count'] );

	$the_query = make_query( $atts['research_area'], $atts['exclude_category'], $atts['count'] );

	ob_start();
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$database = Database::get_object( get_post() );
			$database->show();
		}
	} else {
		echo 'no databases found';
	}
	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * The lib_database_select shortcode, to create a select menu of database titles.
 *
 * We don't actually output the HTML for the select_menu, which would be useless
 * without JavaScript. Instead we output a single <div> and the a <script> with
 * the JSON data necessary to build the menu and we enqueue the
 * library-databases-select-menu script which will create the menu client side
 * so long as JavaScript is enabled.
 *
 * Accepted shortcode attributes are:
 * - `title` the label for the select menu (defaults to **Database Quick Access**)
 * - `select_message` the initial option in the menu (defaults to **Select A
 * - `research_area=slug` show only databases in the research area with the
 *   given slug
 * - `exclude_category=slug` show only databases which are not in the category
 *   with the given slug
 * - `count` The number of databases to return (defaults to 200)
 *
 * @wp-hook add_shortcode
 * @param array|string $atts An associative array of attributes set on the
 *                     shortcode, or an empty string if no attributes are given.
 * @param string       $content The content enclosed by the shortcode.
 */
function lib_database_select( $atts, ?string $content = null ) {
	if ( is_search() ) {
		// Do not expand shortcode on search results page.
		return '';
	}
	$atts = shortcode_atts(
		array(
			'title'            => 'Database Quick Access',
			'select_message'   => 'Select a Database',
			'research_area'    => null,
			'exclude_category' => null,
			'count'            => 200,
		),
		$atts
	);

	// Shortcode argument sanitization.
	// title and select_message do not need to be sanitized here because our
	// JavaScript will make them safe.
	$atts['research_area']    = sanitize_title( $atts['research_area'] );
	$atts['exclude_category'] = sanitize_title( $atts['exclude_category'] );
	$atts['count']            = intval( $atts['count'] );

	$unique_id = new Unique_ID();
	$the_query = make_query( $atts['research_area'], $atts['exclude_category'], $atts['count'] );
	$menu_data = prepare_data_for_select_menu( $the_query );

	wp_enqueue_script( 'library-databases-select-menu' );
	wp_add_inline_script(
		'library-databases-select-menu',
		'createDatabaseSelectMenu(\'lib_dabatases_data_' . $unique_id->get() . '\');'
	);

	$data = array(
		'uniqueID'      => $unique_id->get(),
		'title'         => $atts['title'],
		'selectMessage' => $atts['select_message'],
		'menuData'      => $menu_data,
	);

	ob_start();?>
	<div id="lib_databases_nav_<?php $unique_id->echo(); ?>"></div>
	<script id="lib_dabatases_data_<?php $unique_id->echo(); ?>" type="application/json">
		<?php echo wp_json_encode( $data ); ?>
	</script>
	<?php
	return ob_get_clean();
}

/**
 * Returns the WP_Query object used by our shortcodes.
 *
 * We used to return all databases, which had the potential to be very slow. We
 * now require an explicit count and default to 200 databases.
 *
 * @param string $research_area The slug of a research_area to limit the results
 *               to databases in that research area. Use null to include all
 *               research areas.
 * @param string $exclude_category The slug of an access_category to exlude. Use
 *               null to not exclude any access_categories.
 * @param int    $count The number of databases to return. Must be > 1.
 */
function make_query( ?string $research_area = null, ?string $exclude_category = null, int $count = 200 ) {
	if ( $count < 1 ) {
		$count = 1;
	}

	$query_args = array(
		'post_type'      => Database::POST_TYPE_KEY,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => $count,
	);

	if ( $research_area ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => Research_Area::TAX_NAME,
				'field'            => 'slug',
				'include_children' => false,
				'terms'            => $research_area,
			),
		);
	}

	if ( $exclude_category ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => Access_Category::TAX_NAME,
				'field'            => 'slug',
				'include_children' => false,
				'terms'            => $exclude_category,
				'operator'         => 'NOT IN',
			),
		);
	}

	$the_query = new WP_Query( $query_args );

	return $the_query;
}

/**
 * Creates an array of data that can be used by the
 * library-databases-select-menu script to create a selet menu for launching
 * databases.
 *
 * @param \WP_Query $query A WP_Query object for the databases to include in the
 * select menu.
 * @return array An array of associative arrays. The inner arrays have keys
 * title, url, and disabled.
 */
function prepare_data_for_select_menu( \WP_Query $query ) : array {
	$menu_data = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$database    = Database::get_object( get_post() );
			$menu_option = array(
				'title' => $database->get_title_for_select_menu(),
				'url'   => $database->get_database_url(),
			);
			if ( $database->is_inaccessible() ) {
				$menu_option['title']    = $menu_option['title'] . ' (available in library)';
				$menu_option['disabled'] = true;
			}
			array_push( $menu_data, $menu_option );
		}
	}
	wp_reset_postdata();

	return $menu_data;
}
