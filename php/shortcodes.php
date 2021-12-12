<?php
/**
 * Shortcodes for the Library Databases plugin.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases\Shortcodes;

require_once 'class-unique-id.php';
require_once 'class-database.php';

use ForbesLibrary\WordPress\LibraryDatabases\Unique_ID;
use ForbesLibrary\WordPress\LibraryDatabases\Database;
use WP_Query;

/**
 * A shortcode for listing lib_databases.
 *
 * Accepted shortcode attributes are:
 * - `research_area=slug` show only databases in the research area with the
 *   given slug
 * - `exclude_category=slug` show only databases which are not in the category
 *   with the given slug
 *
 * @wp-hook add_shortcode
 * @param array|string $atts An associative array of attributes set on the
 *                     shortcode, or an empty string if no attributes are given.
 * @param string       $content The content enclosed by the shortcode.
 */
function lib_database_list( $atts, $content = null ) {
	if ( is_search() ) {
		// Do not expand shortcode on search results page.
		return '';
	}
	$atts = shortcode_atts(
		array(
			'research_area'    => null,
			'exclude_category' => null,
		),
		$atts
	);

	$the_query = make_query( $atts['research_area'], $atts['exclude_category'] );

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
 * This shortcode creates a select menu of database titles.
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
 *
 * @wp-hook add_shortcode
 * @param array|string $atts An associative array of attributes set on the
 *                     shortcode, or an empty string if no attributes are given.
 * @param string       $content The content enclosed by the shortcode.
 */
function lib_database_select( $atts, $content = null ) {
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
		),
		$atts
	);

	$unique_id = new Unique_ID();
	$the_query = make_query( $atts['research_area'], $atts['exclude_category'] );
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
 * @param string $research_area The slug of a research_area to limit the results
 * to databases in that research area. Use null to include all research areas.
 * @param string $exclude_category The slug of an access_category to exlude. Use
 * null to not exclude any access_categories.
 */
function make_query( $research_area = null, $exclude_category = null ) {
	$query_args = array(
		'post_type'      => 'lib_databases',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => -1,
	);

	if ( $research_area ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => 'lib_databases_research_areas',
				'field'            => 'slug',
				'include_children' => false,
				'terms'            => $research_area,
			),
		);
	}

	if ( $exclude_category ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => 'lib_databases_categories',
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
 * @param WP_Query $query A WP_Query object for the databases to include in the
 * select menu.
 * @return array An array of associative arrays. The inner arrays have keys
 * title, url, and disabled.
 */
function prepare_data_for_select_menu( $query ) : array {
	$menu_data = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$database    = Database::get_object( get_post() );
			$menu_option = array(
				'title' => get_the_title(),
				'url'   => $database->get_database_url(),
			);

			$postfix = $database->get_category_title_postfix();

			if ( $postfix ) {
				$menu_option['title'] = $menu_option['title'] . ' ' . $postfix;
			}
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
