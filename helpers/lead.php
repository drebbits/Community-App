<?php 

PL_Lead_Helper::init();

class PL_Lead_Helper {

	private static $default_response = array(
		'id' => '',
		'email' => '(Not Provided)',
		'first_name' => '(Not Provided)',
		'last_name' => '(Not Provided)',
		'phone' => '(Not Provided)',
		'created' => '(Not Provided)',
		'updated' => '(Not Provided)',
		'saved_searches' => 0
	);

	public static function init () {
		// Basic AJAX endpoints
		add_action('wp_ajax_datatable_my_leads', array(__CLASS__, 'ajax_get_leads'));
		add_action('wp_ajax_datatable_leads_searches_ajax', array(__CLASS__, 'ajax_get_lead_searches'));		
		add_action('wp_ajax_datatable_favorites_ajax', array(__CLASS__, 'ajax_get_favorites_by_id'));		
		add_action('wp_ajax_pls_update_lead', array(__CLASS__, 'ajax_update_lead'));		
		add_action('wp_ajax_delete_lead', array(__CLASS__, 'ajax_delete_lead'));		
		add_action('wp_ajax_delete_lead_search', array(__CLASS__, 'ajax_delete_lead_search'));		
	}

	public static function ajax_delete_lead () {
		echo json_encode(array('result' => 1, 'data_recieved' => json_encode($_POST)));
		die();
	}

	public static function ajax_delete_lead_search () {
		echo json_encode(array('result' => 1, 'data_recieved' => json_encode($_POST)));
		die();
	}

	public static function ajax_update_lead () {
		echo json_encode(array('result' => 1, 'data_recieved' => json_encode($_POST)));
		die();
	}

	public static function add_lead ($args = array()) {
		// Try to push lead to CRM (if one is linked/active)...
		self::add_lead_to_CRM($args);	

		return PL_Lead::create($args);
	}	

	public static function add_lead_ajax () {
		$api_response = self::add_lead($_POST);
		echo json_encode($api_response);
		die();
	}

	public static function add_lead_to_CRM ($args = array()) {
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

	public static function update_lead_details ($lead_details) {
		$pl_lead = self::lead_details();
		return PL_People::update(array_merge(array('id' => $pl_lead['id']), $lead_details));
	}

	// Fetch a site user's details based on his/her unique Placester ID (managed by Rails, stored in WP's usermeta table)
	public static function lead_details () {
		$details = array();
		$wp_user = wp_get_current_user();

		if (!empty($wp_user->ID)) {
			$pl_lead_id = get_user_meta($wp_user->ID, 'pl_lead_id');
		
			if (is_array($pl_lead_id)) { 
				$pl_lead_id = implode($pl_lead_id, '');
			}
			
			if (!empty($pl_lead_id)) {
				$details = PL_People::details(array('id' => $pl_lead_id));
			}
		}

		return $details;
	}

	public static function get_leads ($filters = array()) {
		// Get leads from model...
		// $api_response = PL_Lead::get($filters);
		$api_response = array(
			'total' => 2,
			'leads' => array(
				array(
					'id' => '1',
					'email' => 'john@smith.com',
					'first_name' => 'john',
					'last_name' => 'smith',
					'phone' => '123 123 1234',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'saved_searches' => 5
				),
				array(
					'id' => '2',
					'email' => 'john@smith.com',
					'first_name' => 'Jane',
					'last_name' => 'Johnson',
					'phone' => '123 123 1234',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'saved_searches' => 5
				)
			)
		);

		return $api_response;
	}

	public static function ajax_get_leads () {
		// Get all leads associated with this site...
		$api_response = self::get_leads();
		
		// build response for datatables.js
		$leads = array();
		foreach ($api_response['leads'] as $key => $lead) {
			// $images = $listing['images'];
			$leads[$key][] = $lead['created'];
			$lead['full_name'] = $lead['first_name'] . ' ' . $lead['last_name'];
			// $leads[$key][] = ((is_array($images) && isset($images[0])) ? '<img width=50 height=50 src="' . $images[0]['url'] . '" />' : 'empty');
			$leads[$key][] = '<a class="address" href="' . ADMIN_MENU_URL . '?page=placester_my_leads&id=' . $lead['id'] . '">' .
			 					$lead['full_name'] . 
			 				'</a>
			 				<div class="row_actions">
			 				<a href="' . ADMIN_MENU_URL . '?page=placester_my_leads&id='. $lead['id'] .'&edit=1" >
			 					Edit
			 				</a>
			 				<span>|</span>
			 				<a href="' . ADMIN_MENU_URL . '?page=placester_my_leads&id=' . $lead['id'] . '">
			 					View
			 				</a>
			 				<span>|</span>
			 				<a class="red" id="pls_delete_listing" href="#" ref="'.$lead['id'].'">
			 					Delete
			 				</a>
			 				</div>';
			// $leads[$key][] = $listing["location"]["postal"];
			
			$leads[$key][] = $lead['email'];
			$leads[$key][] = $lead['phone'];
			$leads[$key][] = $lead['updated'];
			$leads[$key][] = $lead['saved_searches'];
		}

		// Required for datatables.js to function properly
		$response = array();
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $leads;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		
		echo json_encode($response);
		die();
	}
		public static function get_lead_searches ($lead_id) {
		// Get leads from model

		// $api_response = PL_Lead::get($lead_id);
		$api_response = array(
			'total' => 40,
			'searches' => array(
				array(
					'id' => '1',
					'name' => 'Boston Properties',
					'saved_fields' => '1 Beds, City Boston, $500k+',
					'link_to_search' => '/listings/something',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'notification_schedule' => 'Once per week'
				),
				array(
					'id' => '2',
					'name' => 'Cambridge Properties',
					'saved_fields' => '1 Beds, City Boston, $500k+',
					'link_to_search' => '/listings/something',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'notification_schedule' => 'Once per week'
				),
			)
		);

		return $api_response;
	}

	public static function ajax_get_lead_searches () {
		$lead_id = $_POST['lead_id'];

		$saved_searches = self::get_lead_searches($lead_id);
		
		// build response for datatables.js
		$searches = array();
		foreach ($saved_searches['searches'] as $key => $search) {
			// $images = $listing['images'];
			$searches[$key][] = $search['created'];
			// $searches[$key][] = ((is_array($images) && isset($images[0])) ? '<img width=50 height=50 src="' . $images[0]['url'] . '" />' : 'empty');
			$searches[$key][] = '<a class="address" href="' . ADMIN_MENU_URL . $search['link_to_search'] . '">' . 
									$search['name'] . 
								'</a>
								<div class="row_actions">
									<a href="' . ADMIN_MENU_URL . '?page=placester_my_searches&id=' . $search['id'] . '">
										View
									</a>
									<span>|</span>
									<a class="red" id="pls_delete_search" href="#" ref="'.$search['id'].'">
										Delete
									</a>
								</div>';
		
			// <a href="' . ADMIN_MENU_URL . '?page=placester_my_searches&id=' . $search['id'] . '" >
			// 							Edit
			// 						</a>
			
			$searches[$key][] = $search['saved_fields'];
			$searches[$key][] = $search['updated'];
			$searches[$key][] = $search['notification_schedule'];
		}

		// Required for datatables.js to function properly.
		$response = array();
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $searches;
		$response['iTotalRecords'] = $saved_searches['total'];
		$response['iTotalDisplayRecords'] = $saved_searches['total'];
		
		echo json_encode($response);
		die();
	}

	public static function get_lead_details_by_id ($lead_id) {
		// $api_response = PL_Lead::get_by_id($lead_id);
		$api_response = array(
					'id' => '2',
					'email' => 'john@smith.com',
					'first_name' => 'Jane',
					'last_name' => 'Johnson',
					'phone' => '123 123 1234',
					'created' => 'Today',
					'updated' => 'Yesterday',
					'saved_searches' => 5,
					'favorited_listings' => 3
				);
		$api_response['full_name'] = $api_response['first_name'] . ' ' . $api_response['last_name'];
		$api_response = wp_parse_args($api_response, self::$default_response);
		return $api_response;
	}

	public static function ajax_get_favorites_by_id () {
		$lead_id = $_POST['lead_id'];

		// Get leads from model
		// $api_response = PL_Lead::get($lead_id);
		$api_response = array(
			'total' => 40,
			'searches' => array(
				array(
					'id' => '1',
					'image' => '',
					'full_address' => '38 W Cedar Street',
					'beds' => '1',
					'baths' => '2',
					'price' => '500k',
					'sqft' => '3454',
					'mls_id' => '123123'
				),
				array(
					'id' => '2',
					'image' => '',
					'full_address' => '38 W Cedar Street',
					'beds' => '1',
					'baths' => '2',
					'price' => '500k',
					'sqft' => '3454',
					'mls_id' => '123123'
				),
			)
		);
		
		// build response for datatables.js
		$searches = array();
		foreach ($api_response['searches'] as $key => $search) {
			
			$searches[$key][] = '<img src="' . $search['image'] . '" />';
			$searches[$key][] = '<a class="address" href="' . ADMIN_MENU_URL . $search['id'] . '">' . 
									$search['full_address'] . 
								'</a>
								<div class="row_actions">
									<a href="' . ADMIN_MENU_URL . '?page=placester_my_searches&id=' . $search['id'] . '">
										View
									</a>
									<span>|</span>
									<a class="red" id="pls_delete_listing" href="#" ref="'.$search['id'].'">
										Delete
									</a>
								</div>';
			
			$searches[$key][] = $search['beds'];
			$searches[$key][] = $search['baths'];
			$searches[$key][] = $search['price'];
			$searches[$key][] = $search['sqft'];
			$searches[$key][] = $search['mls_id'];
		}

		// Required for datatables.js to function properly.
		$response = array();
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $searches;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);
		die();
	}

}