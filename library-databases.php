<?php
/**
 * Plugin Name: Library Databases
 * Plugin URI: https://github.com/forbeslibrary/wordpress.plugin.library-databases
 * Author: Benjamin Kalish
 * Author URI: https://github.com/bkalish
 * Description: Provides easy access to and organization of a library databases and other electronic resources on the web.
 * Version: 2.0.0
 * License: GNU General Public License v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases;

load_dependencies();
add_actions();
add_filters();
add_shortcodes();

/**
 * Include required files.
 */
function load_dependencies() {
	$php_dir = dirname( __FILE__ ) . '/php';

	require_once $php_dir . '/class-access-category.php';
	require_once $php_dir . '/class-research-area.php';
	require_once $php_dir . '/helpers.php';
	require_once $php_dir . '/shortcodes.php';

	Access_Category::register_wp_hooks();
	Research_Area::register_wp_hooks();

	if ( is_admin() ) {
		require_once $php_dir . '/admin.php';
		require_once $php_dir . '/categories-admin.php';
	}
}

/**
 * Hook into WordPress to add our actions.
 */
function add_actions() {
	add_action( 'init', __NAMESPACE__ . '\init' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
	add_action( 'pre_get_posts', __NAMESPACE__ . '\archive_sort_order' );
}

/**
 * Hook into WordPress to add our filters.
 */
function add_filters() {
	add_filter( 'single_template', __NAMESPACE__ . '\single_template' );
	add_filter( 'archive_template', __NAMESPACE__ . '\archive_template' );
}

/**
 * Hook into WordPress to add our shortcodes.
 */
function add_shortcodes() {
	add_shortcode( 'lib_database_list', __NAMESPACE__ . '\Shortcodes\lib_database_list' );
	add_shortcode( 'lib_database_select', __NAMESPACE__ . '\Shortcodes\lib_database_select' );
}

/**
 * Initializes the plugin.
 *
 * @wp-hook init
 */
function init() {
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
		'supports'           => array( 'title', 'editor', 'thumbnail' ),
	);

	register_post_type( 'lib_databases', $args );
}

/**
 * Enqueue Styles and Scripts
 */
function enqueue_scripts() {
	wp_enqueue_style(
		'librarydatabases-style',
		plugin_dir_url( __FILE__ ) . 'css/library-databases.css',
		array(), // No dependencies.
		get_plugin_version(),
	);

	wp_register_script(
		'library-databases-select-menu',
		plugin_dir_url( __FILE__ ) . 'js/library-databases-select-menu.js',
		array(),
		get_plugin_version(),
		true
	);
}

/**
 * Use a special template for showing a single lib_database on a page.
 *
 * @wp-hook single_template
 * @param string $template Path to the template.
 */
function single_template( $template ) {
	global $post;

	if ( 'lib_databases' === $post->post_type ) {
		$template = dirname( __FILE__ ) . '/php/templates/single-lib-databases.php';
	}
	return $template;
}

/**
 * Use a special template for showing the lib_database archives.
 *
 * @wp-hook archive_template
 * @param string $template Path to the template.
 */
function archive_template( $template ) {
	global $post;

	if ( 'lib_databases' === $post->post_type ) {
		$template = dirname( __FILE__ ) . '/php/templates/archive-lib-databases.php';
	}
	return $template;
}

/**
 * Show all databases in alphabetical order on archive page.
 *
 * @wp-hook pre_get_posts
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function archive_sort_order( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ( is_post_type_archive( 'lib_databases' )
				|| is_tax( 'lib_databases_categories' )
		) {
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'title' );
			$query->set( 'nopaging', true );
		}
	}
}

/**
 * Gets the plugin version.
 *
 * @return string The version string for this plugin.
 */
function get_plugin_version() : string {
	$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	return $plugin_data['Version'];
}
