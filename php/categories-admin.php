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

register_wp_hooks();

/**
 * Hooks into WordPress to register our actions and filters.
 */
function register_wp_hooks() {
	$tax_name = Access_Category::$tax_name;
	$actions  = array(
		'admin_head'                   => '\embed_uploader_code',
		'admin_menu'                   => '\admin_menu',
		'add_meta_boxes'               => '\add_meta_boxes',
		"{$tax_name}_add_form_fields"  => '\add_form_fields',
		"{$tax_name}_edit_form_fields" => '\edit_form_fields',
		"create_{$tax_name}"           => '\create',
		"edit_{$tax_name}"             => '\edit',
	);

	foreach ( $actions as $action => $method_name ) {
		add_action( $action, __NAMESPACE__ . $method_name );
	}

	add_filter( "manage_edit-{$tax_name}_columns", __NAMESPACE__ . '\columns' );

	add_filter(
		"manage_{$tax_name}_custom_column",
		__NAMESPACE__ . '\column_content',
		10,
		3
	);
}

/**
 * Adds a custom metabox to select a single lib_databases_categories term on
 * the lib_databases edit page.
 */
function add_meta_boxes() {
	add_meta_box(
		'database-availability-meta',
		__( 'Database Availability' ),
		__NAMESPACE__ . '\output_database_availability_metabox',
		'lib_databases',
		'side',
		'high',
	);
}

/**
 * Returns the html for the database availability box on the lib_databases
 * edit page.
 */
function output_database_availability_metabox() {
	global $post;
	$database = Database::get_object( $post );
	$tax_name = Access_Category::$tax_name;
	$tax      = get_taxonomy( $tax_name );
	$terms    = Access_Category::get_terms();

	$current_term = $database->get_category();
	$current_id   = $current_term ? $current_term->get_id() : false;
	?>
	<ul id="<?php echo esc_attr( $tax_name ); ?>-checklist">
		<?php foreach ( $terms as $term ) : ?>
				<li>
					<label class='selectit'>
						<input type='radio'
							name="tax_input[<?php echo esc_attr( $tax_name ); ?>]"
							<?php echo checked( $current_id, $term->term_id, false ); ?>
							value="<?php echo esc_attr( $term->name ); ?>" />
					<?php echo esc_html( $term->name ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Removes the default lib_databases_categories metabox
 */
function admin_menu() {
	$tax_name = Access_Category::$tax_name;
	remove_meta_box( "tagsdiv-{$tax_name}", 'lib_databases', 'side' );
}

/**
 * Modify the columns in the admin interface_exists
 */
function columns( $columns ) {
	$columns = array(
		'cb'               => '<input type="checkbox" />',
		'name'             => __( 'Name' ),
		'image'            => __( 'Image' ),
		'library_use_only' => __( 'Library Use Only' ),
		'description'      => __( 'Description' ),
		'slug'             => __( 'Slug' ),
		'posts'            => __( 'Count' ),
	);
	return $columns;
}

/**
 * Return content for custom columns
 */
function column_content( $value, $column_name = null, $term_id ) {
	$term_meta = get_option( "taxonomy_{$term_id}" );
	switch ( $column_name ) {
		case 'image':
			if ( isset( $term_meta['image'] ) ) {
				$value = wp_get_attachment_image( $term_meta['image'], array( 32, 32 ) );
			}
			break;
		case 'library_use_only':
			if ( isset( $term_meta['library_use_only'] ) ) {
				$value = ( $term_meta['library_use_only'] ? 'yes' : 'no' );
			} else {
				$value = 'no';
			}
	}
	return $value;
}

/**
 * Save access category custom fields
 */
function save( $term_id ) {
	if ( ! isset( $_POST['term_meta'] ) ) {
		return;
	}

	$tax_name  = Access_Category::$tax_name;
	$term_meta = get_option( "taxonomy_{$term_id}" );

	if ( isset( $_POST['term_meta']['library_use_only'] ) ) {
		$term_meta['library_use_only'] = true;
	} else {
		$term_meta['library_use_only'] = false;
	}

	if ( isset( $_POST['term_meta']['image'] ) && is_numeric( $_POST['term_meta']['image'] ) ) {
		$term_meta['image'] = intval( $_POST['term_meta']['image'] );
	} else {
		$term_meta['image'] = null;
	}

	// Save the option array.
	update_option( "taxonomy_{$term_id}", $term_meta );
}

/**
 * Hook for term creation
 */
function create( $term_id ) {
	save( $term_id );
}

/**
 * Hook for term edit
 */
function edit( $term_id ) {
	save( $term_id );
}

/**
 * Returns the html for the custom fields in the new database access category box
 */
function add_form_fields() {
	?>
	<div class="form-field">
		<label for="choose-image-button">
			<div class="label">
				<?php esc_html_e( 'Image' ); ?>
			</div>
		</label>
		<?php echo_image_form_fields(); ?>
	</div>
	<div class="form-field">
		<div class="label">
			<?php esc_html_e( 'Access Restrictions' ); ?>
		</div>
		<label>
			<input type="checkbox" name="term_meta[library_use_only]"/>
			<?php esc_html_e( 'In Library Only' ); ?>
			<p>
				<?php esc_html_e( '(set library IP addresses under Settings > Library Databases)' ); ?>
			</p>
		</label>
	</div>
	<?php
}

/**
 * Returns the html for the custom fields in the edit database access category
 * metabox.
 *
 * @param WP_Term $term The term being edited.
 */
function edit_form_fields( $term ) {
	$term_meta = get_option( "taxonomy_{$term->term_id}" );
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="choose-image-button">
				<?php esc_html_e( 'Image' ); ?>
			</label>
		</th>
		<td>
			<?php echo_image_form_fields( $term ); ?>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<?php esc_html_e( 'Access Restrictions' ); ?>
		</th>
		<td>
			<label>
				<input type="checkbox" name="term_meta[library_use_only]" <?php checked( ! empty( $term_meta['library_use_only'] ) ); ?>/>
				<?php esc_html_e( 'In Library Only' ); ?>
				<p>
					<?php esc_html_e( '(set library IP addresses under Settings > Library Databases)' ); ?>
				</p>
			</label>
		</td>
	</tr>
	<?php
}

/**
 * Outputs the form fields used to add or remove images for the access category.
 *
 * @param WP_Term $term If provided, echo_image_form_fields will use the image
 * set for this term for its default value.
 */
function echo_image_form_fields( $term = null ) {
	if ( $term ) {
		$term_meta = get_option( "taxonomy_{$term->term_id}" );
		$has_image = isset( $term_meta['image'] );
	} else {
		$term_meta = array();
		$has_image = false;
	}
	?>
	<input type="hidden"
		class="metaValueField"
		id="term_meta[image]"
		name="term_meta[image]"
		value="
		<?php
		if ( $has_image ) {
			echo esc_attr( $term_meta['image'] );
		}
		?>
		"
	/>
	<div id="lib-databases-thumbnail">
		<?php if ( $has_image ) : ?>
			<?php echo wp_get_attachment_image( $term_meta['image'] ); ?>
		<?php endif; ?>
	</div>
	<input id="choose-image-button" type="button" value="Choose File" />
	<input id="remove-image-button" type="button" value="Remove File" style="display:none;" />
	<?php
}

/**
 * Add JavaScript to get URL from media uploader.
 */
function embed_uploader_code() {
	$screen = get_current_screen();
	if ( 'term' !== $screen->base && 'edit-tags' !== $screen->base ) {
		return;
	}
	if ( Access_Category::$tax_name !== $screen->taxonomy ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'library-databases-admin-js' );
	wp_add_inline_script(
		'library-databases-admin-js',
		'jQuery(document).ready( addImageUploadFunctionality );'
	);
}
