<?php 

PL_People_Helper::init();

class PL_People_Helper {

	public static function init () {
		add_action('wp_ajax_add_person', array(__CLASS__, 'add_person_ajax'));
	}

	public static function add_person ($args = array()) {
		// If 'Leads' functionality is enabled, add in parallel...
		if (defined('PL_LEADS_ENABLED')) {
			PL_Lead_Helper::add_lead($args);
		}

		// Try to push lead to CRM (if one is linked/active)...
		self::add_person_to_CRM($_POST);

		return PL_People::create($args);
	}	

	public static function add_person_ajax () {
		$api_response = self::add_person($_POST);
		echo json_encode($api_response);
		die();
	}

	public static function add_person_to_CRM ($args = array()) {
		// Check to see if site is actively linked to a CRM...
		$activeCRMKey = 'pl_active_CRM';
		$crm_id = PL_Options::get($activeCRMKey);
		
		if (!empty($crm_id)) {
			// Load CRM libs...
			$path_to_CRM = trailingslashit(PL_LIB_DIR) . 'CRM/controller.php';
			include_once($path_to_CRM);

			// Call necessary lib to add the contact to the active/registered CRM...
			if (class_exists('PL_CRM_Controller')) {
				PL_CRM_Controller::callCRMLib('createContact', $args);
			}
		}
	}

	public static function update_person_details ($person_details) {
		$placester_person = self::person_details();
		return PL_People::update(array_merge(array('id' => $placester_person['id']), $person_details));
	}

	// Fetch a site user's details based on his/her unique Placester ID (managed by Rails, stored in WP's usermeta table)
	public static function person_details () {
		$details = array();
		$wp_user = wp_get_current_user();

		if (!empty($wp_user->ID)) {
			$placester_id = get_user_meta($wp_user->ID, 'placester_api_id');
		
			if (is_array($placester_id)) { 
				$placester_id = implode($placester_id, ''); 
			}
			
			if (!empty($placester_id)) {
				$details = PL_People::details(array('id' => $placester_id));
			}
		}

		return $details;
	}

}

?>