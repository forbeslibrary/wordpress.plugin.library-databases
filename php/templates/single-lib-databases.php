<?php
/**
 * Single Library Database Template.
 *
 * @package LibraryDatabases
 */

/** Make sure the Database class definition has been loaded. */
require_once dirname( __FILE__ ) . '/../class-database.php';

use ForbesLibrary\WordPress\LibraryDatabases\Database;

global $post;
$archive_link = get_post_type_archive_link( $post->post_type );
$name         = get_post_type_object( $post->post_type )->labels->name;
$database     = new Database( get_post() );

get_header();
?>
<div class="crumbs breadcrumbs" id="breadcrumbs"><a href="<?php echo esc_url( $archive_link ); ?>">
	<?php echo esc_html( $name ); ?></a>→<?php the_title(); ?>
</div>
<div id="content">
	<?php $database->show(); ?>
</div>
<?php
get_footer();
