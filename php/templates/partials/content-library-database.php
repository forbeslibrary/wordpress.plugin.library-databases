<?php
/**
 * Content Library Database Template.
 *
 * Displays the content of a single library database.
 *
 * @package LibraryDatabases
 */

/** Make sure the Database class definition has been loaded. */
require_once dirname( __FILE__ ) . '/../../class-database.php';

use ForbesLibrary\WordPress\LibraryDatabases\Database;
use ForbesLibrary\WordPress\LibraryDatabases\Access_Category;

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
			<h2 class="post-title">
			<a href="<?php $database->show_database_url(); ?>">
				<?php the_title(); ?>
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
			<?php if ( is_user_logged_in() ) : ?>
				<span class="edit-link"><?php edit_post_link( 'Edit Database' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</header>
<div class="post-content">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'the_content', wp_kses_post( $content_array['main'] ) );
	?>
	<?php if ( $database->get_category_description() ) : ?>
		<?php if ( ! is_tax( Access_Category::TAX_NAME ) ) : ?>
			<div class="lib_databases-availability-text">
				<?php $database->show_category_description(); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php
	if ( $more && $content_array['extended'] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'the_content', wp_kses_post( $content_array['extended'] ) );
	}
	?>
</div>
<?php if ( is_single() ) : ?>
	<?php if ( get_option( 'lib_databases_settings_help_text' ) ) : ?>
		<div class="lib_databases-help-text">
			<?php echo wp_kses_post( get_option( 'lib_databases_settings_help_text' ) ); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
</article>
