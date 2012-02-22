<?php 

class PL_Router {

	private static function router($template, $params, $wrap = false) {
		
		ob_start();
			// delete_option('placester_api_key');
			extract($params, EXTR_SKIP);
			self::load_builder_view('header.php');
			if (!PL_Option_Helper::api_key()) {
				do_action('sign-up-action');
				self::load_builder_view('sign-up.php');	
			}
			self::load_builder_view($template);	
			self::load_builder_view('footer.php');
		echo ob_get_clean();
	}

	public static function load_builder_partial($template, $params = array()) {
		if (empty($params)) {
			include(trailingslashit(PL_VIEWS_PART_DIR) . $template);
		} else {
			ob_start();
				extract(PL_Validate::route($params, array('title' => 'Default', 'content' => '')) ) ;
				include(trailingslashit(PL_VIEWS_PART_DIR) . $template);
			echo ob_get_clean();
		}
	}
	
	private static function load_builder_view($template) {
		include_once(trailingslashit(PL_VIEWS_ADMIN_DIR) . $template);
	}
	
	public function my_listings() {
		self:: router('my-listings.php', array('test'=>'donkey'), false);
	}

	public function add_listings() {
		if (isset($_GET['id'])) {
			$_POST = PL_Listing_Helper::details($_GET);
		}
		self:: router('add-listing.php', array(), false);
	}

	public function theme_gallery() {
		self:: router('theme-gallery.php', array('test'=>'donkey'), false);
	}

	public function settings() {
		self:: router('settings.php', array(), false);
	}

//end of class
}