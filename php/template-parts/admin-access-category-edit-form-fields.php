<?php
/**
 * Display the form fields for editing an Access_Category.
 *
 * @package LibraryDatabases
 *
 * Templates arguments:
 * - WP_Term $args['category'] The Access_Category being edited.
 */

$category = $args['category'];
?>
<tr class="form-field">
	<th scope="row">
		<label for="postfix">
			<?php esc_html_e( 'Title Postfix' ); ?>
		</label>
	</th>
	<td>
		<input type="text" name="term_meta[postfix]" id="postfix" value="<?php echo esc_attr( $category->get_postfix() ); ?>" />
		<p class="description">
			<?php esc_html_e( 'Short descriptive text to be appended to database titles in select menus, e.g. "with library card".' ); ?>
		</p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row">
		<label for="choose-image-button">
			<?php esc_html_e( 'Image' ); ?>
		</label>
	</th>
	<td>
		<?php
		load_template(
			dirname( __FILE__ ) . '/admin-image-form-fields.php',
			true,
			array( 'category' => $category )
		);
		?>
		<p class="description">
			<?php esc_html_e( 'An image to represent this Access Category.' ); ?>
			<?php esc_html_e( 'For best results images should be at least 64x64 pixels.' ); ?>
		</p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row">
		<?php esc_html_e( 'Access Restrictions' ); ?>
	</th>
	<td>
		<label>
			<input type="checkbox" name="term_meta[library_use_only]" <?php checked( $category->is_restricted_by_ip() ); ?>/>
			<?php esc_html_e( 'In Library Only' ); ?>
			<p class="description">
				<?php esc_html_e( '(set library IP addresses under Settings > Library Databases)' ); ?>
			</p>
		</label>
	</td>
</tr>
