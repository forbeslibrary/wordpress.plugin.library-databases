<?php
/**
 * Displays the contents of the database availability metabox which lets users
 * assign a single Access_Category to a Database.
 *
 * @package LibraryDatabases
 */

use ForbesLibrary\WordPress\LibraryDatabases\Database;
use ForbesLibrary\WordPress\LibraryDatabases\Access_Category;

global $post;

$database = Database::get_object( $post );
$tax_name = Access_Category::TAX_NAME;
$terms    = Access_Category::get_terms();

$current_term = $database->get_category();
$current_id   = $current_term ? $current_term->get_id() : false;
?>
<ul id="<?php echo esc_attr( $tax_name ); ?>-checklist">
	<?php foreach ( $terms as $category ) : ?>
			<li>
				<label class='selectit'>
					<input type='radio'
						name="tax_input[<?php echo esc_attr( $tax_name ); ?>]"
						<?php echo checked( $current_id, $category->term_id, false ); ?>
						value="<?php echo esc_attr( $category->name ); ?>" />
				<?php echo esc_html( $category->name ); ?>
			</label>
		</li>
	<?php endforeach; ?>
</ul>
