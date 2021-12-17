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

$php_dir = dirname( __FILE__ ) . '/php';

require_once $php_dir . '/class-access-category.php';
require_once $php_dir . '/class-database.php';
require_once $php_dir . '/class-research-area.php';
require_once $php_dir . '/helpers.php';
require_once $php_dir . '/shortcodes.php';

Database::register_wp_hooks();
Access_Category::register_wp_hooks();
Research_Area::register_wp_hooks();

if ( is_admin() ) {
	require_once $php_dir . '/admin.php';
	require_once $php_dir . '/categories-admin.php';
}

add_shortcode( 'lib_database_list', __NAMESPACE__ . '\Shortcodes\lib_database_list' );
add_shortcode( 'lib_database_select', __NAMESPACE__ . '\Shortcodes\lib_database_select' );

/**
 * Enqueue Styles and Scripts
 */
add_action(
	'wp_enqueue_scripts',
	function () {
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
);

/**
 * Use a special template for showing a single lib_database on a page.
 *
 * This overrides the usual template hierarchy. Theme authors can supply their
 * own single-lib_databases.php template or or completely disable this by
 * returning false from the lib_database_use_plugin_templates filter.
 *
 * @wp-hook single_template
 * @param string $template Path to the template.
 * @return string Path to the template.
 */
add_filter(
	'single_template',
	function ( string $template ) {
		global $post;

		$use_plugin_templates = apply_filters( 'lib_database_use_plugin_templates', true );

		if ( $use_plugin_templates ) {
			if ( Database::POST_TYPE_KEY === $post->post_type ) {
				if ( ! locate_template( 'single-lib_databases.php' ) ) {
					$template = dirname( __FILE__ ) . '/php/templates/single-lib-databases.php';
				}
			}
		}

		return $template;
	}
);

/**
 * Use a special template for showing the lib_database archives.
 *
 * This overrides the usual template hierarchy. Theme authors can supply their
 * own archive-lib_databases.php template or or completely disable this by
 * returning false from the lib_database_use_plugin_templates filter.
 *
 * @wp-hook archive_template
 * @param string $template Path to the template.
 * @return string Path to the template.
 */
add_filter(
	'archive_template',
	function ( string $template ) {
		global $post;

		$use_plugin_templates = apply_filters( 'lib_database_use_plugin_templates', true );

		if ( $use_plugin_templates ) {
			if ( Database::POST_TYPE_KEY === $post->post_type ) {
				if ( ! locate_template( 'archive-lib_databases.php' ) ) {
					$template = dirname( __FILE__ ) . '/php/templates/archive-lib-databases.php';
				}
			}
		}

		return $template;
	}
);

/**
 * Show all databases in alphabetical order on archive page.
 *
 * @wp-hook pre_get_posts
 * @param \WP_Query $query The WP_Query instance (passed by reference).
 */
add_action(
	'pre_get_posts',
	function ( \WP_Query $query ) {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_post_type_archive( Database::POST_TYPE_KEY )
					|| is_tax( Access_Category::TAX_NAME )
			) {
				$query->set( 'order', 'ASC' );
				$query->set( 'orderby', 'title' );
				$query->set( 'nopaging', true );
			}
		}
	}
);

/**
 * Gets the plugin version.
 *
 * @return string The version string for this plugin.
 */
function get_plugin_version() : string {
	$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	return $plugin_data['Version'];
}
