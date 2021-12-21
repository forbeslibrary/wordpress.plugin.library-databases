<?php
/**
 * Display the form fields for adding and removing images to an Access_Category.
 *
 * @package LibraryDatabases
 *
 * Templates arguments:
 * - WP_Term $args['category'] The Access_Category being edited.
 */

use function ForbesLibrary\WordPress\LibraryDatabases\Helpers\get_tax_term_meta;

$category       = $args['category'] ?? null;
$category_image = null;
if ( $category ) {
	$category_image = get_tax_term_meta( $category->get_id(), 'image' );
}
?>
<input type="hidden"
	class="metaValueField"
	id="term_meta[image]"
	name="term_meta[image]"
	value="<?php echo esc_attr( $category_image ); ?>"
/>
<div id="lib-databases-thumbnail">
	<?php if ( $category_image ) : ?>
		<?php echo wp_get_attachment_image( $category_image ); ?>
		<?php echo esc_html( basename( get_attached_file( $category_image ) ) ); ?>
	<?php endif; ?>
</div>
<input id="choose-image-button" type="button" value="Choose File" />
<input id="remove-image-button" type="button" value="Remove File" style="display:none;" />
