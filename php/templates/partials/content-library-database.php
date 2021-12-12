<?php
/**
 * Content Library Database Template.
 *
 * Displays the content of a single library database.
 *
 * @package LibraryDatabases
 */

require_once dirname( __FILE__ ) . '/../../class-database.php';

use ForbesLibrary\WordPress\LibraryDatabases\Database;

global $more;

$database      = Database::get_object( get_post() );
$content_array = get_extended( $post->post_content );
?>
<article id="post-<?php the_ID(); ?>" class="lib_databases post hentry">
<header>
	<?php $database->show_availability_icon(); ?>
	<div class="title_area">
	<?php if ( $database->is_inaccessible() ) : ?>
		<h2 class="entry-title lib_databases-database-unavailable">
			<?php the_title(); ?>
		<span class="parenthetical"> (available in library)</span>
		</h2>
	<?php else : ?>
		<h2 class="entry-title">
		<a href="<?php $database->show_database_url(); ?>">
			<?php
			if ( has_post_thumbnail( $post->ID ) ) {
				$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
				echo sprintf(
					'<img src="%s" class="lib_database_feature_icon" alt="%s">',
					esc_url( $image_attributes[0] ),
					the_title_attribute( array( 'echo' => false ) ),
				);
			} else {
				the_title();
			}
			?>
		</a>
		</h2>
	<?php endif; ?>
	<div class="quick_links">
	<span class="permalink"><a href="<?php the_permalink(); ?>">🔗 permalink</a></span>
	<?php if ( ! $database->is_inaccessible() ) : ?>
		<span class="database-link">
			<a href="<?php $database->show_database_url(); ?>">
				↗ visit <?php the_title(); ?>
			</a>
		</span>
	<?php endif; ?>
	<?php if ( $content_array['extended'] && ! $more ) : ?>
		<span class="learn-more-link"><a href="<?php the_permalink(); ?>">❓ learn more</a></span>
	<?php endif; ?>
	</div>
	</div>
</header>
<div class="entry-content">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'the_content', wp_kses_post( $content_array['main'] ) );
	?>
	<?php if ( $database->get_category_description() ) : ?>
	<div class="lib_databases-availability-text">
		<?php $database->show_category_description(); ?>
	</div>
	<?php endif; ?>
	<?php
	if ( $more ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'the_content', wp_kses_post( $content_array['extended'] ) );
	}
	?>
</div>
<?php if ( is_user_logged_in() ) : ?>
	<footer class="entry-utility"><span class="edit-link"><?php edit_post_link( 'Edit Database' ); ?></span></footer>
<?php endif; ?>
</article>
