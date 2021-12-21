<?php
/**
 * Admin interface for the Library Databases plugin.
 *
 * @package LibraryDatabases
 */

namespace ForbesLibrary\WordPress\LibraryDatabases;

use ForbesLibrary\WordPress\LibraryDatabases\Research_Area;

/**
 * Adds the a Library Databases Settings page to the settings menu
 *
 * @wp-hook admin_menu
 */
add_action(
	'admin_menu',
	function () {
		add_options_page(
			// Page Title.
			__( 'Library Databases Settings' ),
			// Menu Title.
			__( 'Library Databases' ),
			// Capability.
			'manage_options',
			// Menu Slug (also-referred to as option group).
			'lib_databases_settings_page',
			// Callback.
			function () {
				echo '<h1>';
				esc_html_e( 'Library Databases Settings' );
				echo '</h1>';
				echo '<form method="POST" action="options.php">';
				settings_fields( 'lib_databases_settings_page' );
				do_settings_sections( 'lib_databases_settings_page' );
				submit_button();
				echo '</form>';
			}
		);
	}
);

/**
 * Initializes the settings and fields using the settings API.
 *
 * @wp-hook admin_init
 */
add_action(
	'admin_init',
	function () {
		add_settings_section(
			// ID.
			'default',
			// Title.
			'',
			// Callback. Function that echos out any content at the top of the section.
			'__return_empty_string', // Output nothing.
			// Page.
			'lib_databases_settings_page'
		);

		add_settings_field(
			// ID.
			'lib_databases_settings_ip_addresses',
			// Title.
			__( 'In Library Use IP Addresses' ),
			// Callback.
			__NAMESPACE__ . '\output_ip_addresses_form_field',
			// Page.
			'lib_databases_settings_page'
		);

		add_settings_field(
			// ID.
			'lib_databases_settings_help_text',
			// Title.
			__( 'Help Text' ),
			// Callback.
			__NAMESPACE__ . '\output_help_text_form_field',
			// Page.
			'lib_databases_settings_page'
		);

		register_setting(
			'lib_databases_settings_page',
			'lib_databases_settings_ip_addresses',
			array(
				'sanitize_callback' => __NAMESPACE__ . '\sanitize_ip_addresses_field',
			)
		);

		register_setting(
			'lib_databases_settings_page',
			'lib_databases_settings_help_text',
			array(
				'sanitize_callback' => 'wp_kses_post',
				'default'           => 'Contact us to learn more.',
			)
		);
	}
);

/**
 * Add information about lib_databases to the glance items.
 *
 * @wp-hook dashboard_glance_items
 */
add_action(
	'dashboard_glance_items',
	function () {
		$count           = wp_count_posts( Database::POST_TYPE_KEY )->publish;
		$formatted_count = number_format_i18n( $count );

		/* translators: %s is number of databases */
		$text = sprintf( _n( '%s Database', '%s Databases', $count ), $formatted_count );
		echo '<li class="lib_databases-count"><a href="edit.php?post_type=lib_databases">';
		echo esc_html( $text );
		echo '</li>';
	}
);

/**
 * Save custom fields from lib_databases edit page.
 *
 * @wp-hook save_post
 */
add_action(
	'save_post',
	function () {
		global $post;

		if ( ! $post ) {
			return;
		}

		if ( ( ! isset( $_POST['database_main_url'] ) )
			&& ( ! isset( $_POST['database_home_use_url'] ) )
		) {
			// No custom fields to save. We are done here.
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			wp_die(
				esc_html__( 'You do not have permission to edit this.' ),
				esc_html__( 'Something went wrong.' ),
				403
			);
		}

		if ( ! isset( $_POST['library-databases-urls-metabox-nonce'] ) ) {
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
			$_POST['library-databases-urls-metabox-nonce'],
			'library-databases/edit-urls' . $post->ID
		);
		if ( ! $nonce ) {
			wp_nonce_ays( 'access-category/add' );
		}
		// phpcs:enable

		if ( isset( $_POST['database_main_url'] ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['database_main_url'] ) );
			update_post_meta( $post->ID, 'database_main_url', $url );
		}
		if ( isset( $_POST['database_home_use_url'] ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['database_home_use_url'] ) );
			update_post_meta( $post->ID, 'database_home_use_url', $url );
		}
	}
);

/**
 * Adds custom fields to the lib_databases edit page.
 *
 * @wp-hook add_meta_boxes
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'database-url-meta',
			__( 'Database URL' ),
			__NAMESPACE__ . '\editbox_database_urls',
			Database::POST_TYPE_KEY,
			'side',
			'high'
		);
	}
);

/**
 * Outputs the contents of each custom column on the lib_databases admin page.
 *
 * @wp-hook manage_{$post->post_type}_posts_custom_column
 *
 * @param string $column The name of the column to display.
 */
add_action(
	'manage_lib_databases_posts_custom_column',
	function ( string $column ) {
		global $post;

		switch ( $column ) {
			case 'description':
				the_excerpt();
				break;
			case 'lib_databases_research_areas':
				$research_areas = wp_get_post_terms(
					$post->ID,
					Research_Area::TAX_NAME,
					array( 'fields' => 'names' )
				);
				echo implode( ',<br> ', array_map( 'esc_html', $research_areas ) );
				break;
		}
	}
);

/**
 * Customizes the columns on the lib_databases admin page.
 *
 * @wp-hook manage_{$post_type}_posts_columns
 *
 * @param string[] $columns An associative array of column headings.
 * @return string[] An associative array of column headings.
 */
add_filter(
	'manage_lib_databases_posts_columns',
	function ( array $columns ) {
		$columns = array_merge(
			$columns,
			array(
				'title'                        => __( 'Database Title' ),
				'lib_databases_research_areas' => __( 'Research Area' ),
				'description'                  => __( 'Description' ),
			)
		);

		return $columns;
	}
);

/**
 * Enqueues scripts and styles required for the admin interface.
 *
 * @param string $hook_suffix The current admin page.
 */
add_action(
	'admin_enqueue_scripts',
	function ( string $hook_suffix ) {
		wp_enqueue_style(
			'library-databases-admin',
			plugin_dir_url( __FILE__ ) . '../css/library-databases-admin.css',
			array(), // No dependencies.
			get_plugin_version()
		);
		wp_register_script(
			'library-databases-admin-js',
			plugin_dir_url( __FILE__ ) . '../js/library-databases-admin.js',
			array(), // No dependencies.
			get_plugin_version(),
			true
		);
	}
);

/**
 * Outputs the html for the database urls box on the lib_databases edit page.
 */
function editbox_database_urls() {
	global $post;

	wp_nonce_field(
		'library-databases/edit-urls' . $post->ID,
		'library-databases-urls-metabox-nonce'
	);

	$custom = get_post_custom( $post->ID );
	if ( isset( $custom['database_main_url'] ) ) {
		$database_main_url = $custom['database_main_url'][0];
	} else {
		$database_main_url = '';
	}
	if ( isset( $custom['database_home_use_url'] ) ) {
		$database_home_use_url = $custom['database_home_use_url'][0];
	} else {
		$database_home_use_url = '';
	}
	?>
	<label><?php esc_html_e( 'Main URL' ); ?>:</label>
	<input name="database_main_url" value="<?php echo esc_url( $database_main_url ); ?>" />
	<label><?php esc_html_e( 'Home Use URL (if different)' ); ?>:</label>
	<input name="database_home_use_url" value="<?php echo esc_url( $database_home_use_url ); ?>" />
	<?php
}

/**
 * Outputs HTML for the lib_databases settings ip address field.
 *
 * This is a callback function for the WordPress Settings API
 */
function output_ip_addresses_form_field() {
	?>
	<textarea
		name="lib_databases_settings_ip_addresses"
		id="lib_databases_settings_ip_addresses"
		class="code"
	><?php echo esc_textarea( get_option( 'lib_databases_settings_ip_addresses' ) ); ?></textarea>
	<p class="description">Please enter each IP address on its own line.<p>
	<?php
}

/**
 * Outputs HTML for the lib_databases settings help text  field.
 *
 * This is a callback function for the WordPress Settings API
 */
function output_help_text_form_field() {
	?>
	<textarea
		name="lib_databases_settings_help_text"
		id="lib_databases_settings_help_text"
		class="code"
	><?php echo esc_textarea( get_option( 'lib_databases_settings_help_text' ) ); ?></textarea>
	<p class="description">Text to be displayed on databases pages. HTML is allowed.<p>
	<?php
}

/**
 * Sanitize a textarea field leaving only line endings and characters that are
 * valid in ip addresses.
 *
 * @param string $str The string to be sanitized.
 * @return string The sanitized string.
 */
function sanitize_ip_addresses_field( string $str ) {
	return trim( preg_replace( '/[^0-9\n\.]/', '', $str ) );
}
