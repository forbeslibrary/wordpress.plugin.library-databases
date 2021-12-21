<?php
/**
 * Display the form fields for creating an Access_Category.
 *
 * @package LibraryDatabases
 *
 * Templates arguments:
 * - WP_Term $args['category'] The Access_Category being edited.
 */

?>
<div class="form-field">
	<label for="postfix">
		<?php esc_html_e( 'Title Postfix' ); ?>
		<input type="text" name="term_meta[postfix]" id="postfix" />
	</label>
	<p class="description">
		<?php esc_html_e( 'Short descriptive text to be appended to database titles in select menus, e.g. "with library card".' ); ?>
	</p>
</div>
<div class="form-field">
	<label for="choose-image-button">
		<div class="label">
			<?php esc_html_e( 'Image' ); ?>
		</div>
	</label>
	<?php
	load_template(
		dirname( __FILE__ ) . '/admin-image-form-fields.php',
		true
	);
	?>
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
