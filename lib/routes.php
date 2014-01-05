<?php

class PL_Router {

	private static function router ($template, $params, $directory = PL_VIEWS_ADMIN_DIR) {
		ob_start();
		self::load_builder_view('header.php');
		self::load_builder_view($template, $directory, $params);
		self::load_builder_view('footer.php');
		echo ob_get_clean();

	}

	public static function load_builder_partial ($template, $params = array(), $return = false) {
		ob_start();
		if (!empty($params)) {
			extract($params);
		}
		include(trailingslashit(PL_VIEWS_PART_DIR) . $template);
		if ($return) {
			return ob_get_clean();
		} else {
			echo ob_get_clean();
		}
	}

	public static function load_builder_library ($template, $directory = PL_JS_LIB_DIR) {
		include_once(trailingslashit($directory) . $template);
	}

	public static function load_builder_helper ($template, $directory = PL_HLP_DIR) {
		include_once(trailingslashit($directory) . $template);
	}

	private static function load_builder_view ($template, $directory = PL_VIEWS_ADMIN_DIR, $params = array()) {
		ob_start();
		if (!empty($params)) {
			extract($params);
		}
		include_once(trailingslashit($directory) . $template);
		echo ob_get_clean();
	}
	
	/*
	 * Plugin Menu Pages
	 */

	public static function my_listings () {
		self::router('my-listings.php', array());
	}

	public static function add_listings () {
		if (isset($_GET['id'])) {
			// Fetch listing and store it in the POST global...
			$_POST = PL_Listing_Helper::single_listing($_GET['id']);
		}

		self::router('add-listing.php', array());
	}

	public static function my_leads () {
		//index page also doubles as the details page since wp doesn't make it easy to have an "admin"
		//page that doesn't appear in the main navigation.
		if (isset($_GET['id']) && isset($_GET['edit']) && $_GET['edit'] === '1') {
			$_POST = PL_UI_Saved_Search::get_lead_details_by_id($_GET['id']);
			self::router('lead-edit.php', array());	
		} elseif ( isset($_GET['id']) && isset($_GET['new_search']) && $_GET['new_search'] === '1' ) {
			self::router('/leads/new-search.php', array());	
		} elseif ( isset($_GET['id']) && isset($_GET['search_id']) && isset($_GET['edit_search']) && $_GET['edit_search'] === '1' ) {
			self::router('/leads/search-edit.php', array());	
		} elseif (isset($_GET['id'])) {
			self::router('lead-details.php', array());	
		} else {
			self::router('my-leads.php', array());	
		}
		
	}

	public static function theme_gallery () {
		if (isset($_GET['theme_url'])) {
			self::router('install-theme.php', array());	
		} else {
			self::router('theme-gallery.php', array());
		}
	}

	public static function settings () {
		self::router('settings/general.php', array());
	}
	
	public static function settings_polygons () {
		self::router('settings/polygons.php', array());
	}
	
	public static function settings_property_pages () {
		self::router('settings/property-taxonomies.php', array());
	}
	
	public static function settings_international () {
		self::router('settings/international.php', array());
	}
	
	public static function settings_neighborhood () {
		self::router('settings/neighborhood.php', array());
	}
	
	public static function settings_filtering () {
		self::router('settings/filtering.php', array());
	}

	public static function settings_client () {
		self::load_builder_helper('membership.php');
		self::router('settings/client.php', PL_Membership_Helper::get_client_settings());
	}

	public static function settings_lead_capture () {
		self::router('lead-capture/general.php', array());
	}

	public static function settings_crm () {
		self::router('crm.php', array());
	}

	public static function support () {
		self::router('support.php', array());
	}

	public static function integrations () {
		self::router('integrations.php', array());
	}

	public static function shortcodes () {
		self::router('shortcodes/shortcodes.php', array());
	}
	
	public static function shortcodes_shortcode_edit () {
		if (isset($_REQUEST['trashed'])) {
			wp_redirect(admin_url('admin.php?page=placester_shortcodes'));
			exit;
		}
		self::router('shortcodes/shortcode-edit.php', array());
	}
	
	public static function shortcodes_templates () {
		self::router('shortcodes/templates.php', array());
	}
	
	public static function shortcodes_template_edit () {
		self::router('shortcodes/template-edit.php', array());
	}

	public static function shortcodes_listing_templates () {
		self::router('listing-customizer/templates.php', array());
	}

	public static function shortcodes_listing_template_edit () {
		self::router('listing-customizer/template-edit.php', array());
	}

	public static function buffer_op($page_hook) {
		add_action('load-'.$page_hook, array(__CLASS__, 'admin_header'));
		add_action('admin_footer-'.$page_hook, array(__CLASS__, 'admin_footer'));
	}
	
	public static function admin_header() {
		ob_start();
	}
	
	public static function admin_footer() {
		ob_end_flush();
	}
}
// end of class
?>
