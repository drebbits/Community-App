<?php
global $shortcode_subpages;

if(!class_exists('PL_Shortcodes_List_Table')){
	require_once( PL_LIB_DIR . 'shortcodes-table.php' );
}

$search = (!empty($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '');
$post_type = 'pl_general_widget';
$wp_list_table = new PL_Shortcodes_List_Table();
$wp_list_table->prepare_items();
$pagenum = $wp_list_table->get_pagenum();


PL_Router::load_builder_view('header.php');
?>
<div class="wrap pl-sc-wrap">
	<?php echo PL_Helper_Header::pl_subpages('placester_shortcodes', $shortcode_subpages, 'Custom Shortcodes'); ?>

	<div id="pl_shortcode_all">
		<h2><?php
		if ($search)
			printf( ' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $search );
		?></h2>
		<?php if ( isset( $_REQUEST['locked'] ) || isset( $_REQUEST['updated'] ) || isset( $_REQUEST['deleted'] ) || isset( $_REQUEST['trashed'] ) || isset( $_REQUEST['untrashed'] ) ) {
			$messages = array();
		?>
		<div id="message" class="updated"><p>
		<?php if ( isset( $_REQUEST['updated'] ) && $updated = absint( $_REQUEST['updated'] ) ) {
			$messages[] = sprintf( _n( '%s post updated.', '%s posts updated.', $updated ), number_format_i18n( $updated ) );
		}

		if ( isset( $_REQUEST['locked'] ) && $locked = absint( $_REQUEST['locked'] ) ) {
			$messages[] = sprintf( _n( '%s item not updated, somebody is editing it.', '%s items not updated, somebody is editing them.', $locked ), number_format_i18n( $locked ) );
		}

		if ( isset( $_REQUEST['deleted'] ) && $deleted = absint( $_REQUEST['deleted'] ) ) {
			$messages[] = sprintf( _n( 'Item permanently deleted.', '%s items permanently deleted.', $deleted ), number_format_i18n( $deleted ) );
		}

		if ( isset( $_REQUEST['trashed'] ) && $trashed = absint( $_REQUEST['trashed'] ) ) {
			$messages[] = sprintf( _n( 'Item moved to the Trash.', '%s items moved to the Trash.', $trashed ), number_format_i18n( $trashed ) );
			$ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : 0;
			$messages[] = '<a href="' . esc_url( wp_nonce_url( "edit.php?post_type=$post_type&doaction=undo&action=untrash&ids=$ids", "bulk-posts" ) ) . '">' . __('Undo') . '</a>';
		}

		if ( isset( $_REQUEST['untrashed'] ) && $untrashed = absint( $_REQUEST['untrashed'] ) ) {
			$messages[] = sprintf( _n( 'Item restored from the Trash.', '%s items restored from the Trash.', $untrashed ), number_format_i18n( $untrashed ) );
		}

		if ( $messages )
			echo join( ' ', $messages );
		unset( $messages );

		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );
		?>
		</p></div>
		<?php } ?>

		<?php $wp_list_table->views(); ?>

		<form id="posts-filter" action="<?php echo admin_url("admin.php")?>" method="get">

		<?php $wp_list_table->search_box( 'Search Custom Shortcodes', 'pl_general_widget' ); ?>

		<input type="hidden" name="page" class="post_page" value="placester_shortcodes" />
		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
		<input type="hidden" name="apost_type" class="post_type_page" value="<?php echo $post_type; ?>" />

		<?php $wp_list_table->display(); ?>

		</form>
	</div>

	<br class="clear" />
</div>
<?php
PL_Router::load_builder_view('footer.php');
