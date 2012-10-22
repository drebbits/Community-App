<?php

/**
 * Entity functions to be used for shortcodes/widgets/frames
 *
 */

PL_Component_Entity::init();

class PL_Component_Entity {

	public static $defaults = array( 'twentyten', 'twentyeleven' );
	
	public static $featured_context;
	
	public static $listing;
	/**
	 * Featured listings logic
	 * @param array $atts id or future arguments
	 * @param string $filters filters for default_filters
	 * @return boolean|string
	 */
	
	public static function init() {
		// add_action('init', array( __CLASS__, 'filter_featured_context' ) );

		$templates = self::get_shortcode_snippet_list( 'featured_listings', self::$defaults );
		foreach ($templates as $template => $type) {
			add_filter( 'pls_listings_list_ajax_item_html_' . $template, array(__CLASS__,'featured_listings_ajax_templates'), 10, 3 );	
		}

	}
	
	public static function featured_listings_entity( $atts, $filters = '' ) {
		if( ! isset( $atts['id'] ) ) {
			return false;
		}
		$atts = wp_parse_args($atts, array('limit' => 5, 'featured_id' => 'custom', 'context' => 'shortcode'));
		ob_start();
		
		// pass a template as a context if any
		$template_context = '';
		// pls_dump($atts);
		if( isset( $atts['template'] ) ) {
		 	$template_context = $atts['template'];
		 	self::$featured_context = $template_context;
			
		// 	add_action('init', array( __CLASS__, 'filter_featured_context' ) );
		}
		
		// Print property_ids as argument to the listings
		global $property_ids;
		$property_ids = self::get_property_ids( $atts['id'] );
		$property_ids = array_flip($property_ids);
		
		add_action('featured_filters_featured_ids', array( __CLASS__, 'print_property_listing_args') );
		unset( $property_ids );
		
// 		// print the rest of the filters
 		PL_Component_Entity::print_filters( $filters, $template_context ); 
		
// 		// compose the final listing with AJAX
		echo PLS_Partials::get_listings_list_ajax( ( empty( $template_context ) ? '' : 'context=' . $template_context . '&' ). 'table_id=placester_listings_list');
		
		return ob_get_clean();
	}
	
	/**
	 * Static listings
	 * 
	 * @param array $atts arguments - id
	 * @param string $filters default filters to be passed
	 * @return boolean|string
	 */
	public static function static_listings_entity( $atts, $filters = '' ) {
		if( ! isset( $atts['id'] ) ) {
			return false;
		}
		$atts = wp_parse_args($atts, array('limit' => 5, 'featured_id' => 'custom', 'context' => 'shortcode'));
		ob_start();
		
		$template_context = '';
		// pls_dump($atts);
		if( isset( $atts['template'] ) ) {
			$template_context = $atts['template'];
			self::$featured_context = $template_context;
		}
		
		// print filters from the static listing menu
		$listing_filters = PL_Component_Entity::get_filters_by_listing( $atts['id'] );
		$filters_string = PL_Component_Entity::convert_filters( $listing_filters );

		// accepts string only due to shortcode evaluation algorithm
		PL_Component_Entity::print_filters( $filters . $filters_string, $template_context );
		echo PLS_Partials::get_listings_list_ajax( ( empty( $template_context ) ? '' : 'context=' . $template_context . '&' ). 'table_id=placester_listings_list');
	
		return ob_get_clean();
	}
	
	public static function search_listings_entity( $atts ) {
		ob_start();
		?>
			  	<script type="text/javascript">
				  	if (typeof bootloader !== 'object') {
						var bootloader;
					}
				  jQuery(document).ready(function( $ ) {
		
				  	if (typeof bootloader !== 'object') {
				  		bootloader = new SearchLoader();
				  		bootloader.add_param({list: {context: "shortcode"}});
				  	} else {
				  		bootloader.add_param({list: {context: "shortcode"}});
				  	}
				  });
				</script>
		
		
			  	<?php
			    PLS_Partials_Get_Listings_Ajax::load(array('context' => 'shortcode'));
			  return ob_get_clean();  
	}
	
	public static function search_map_entity( $atts ) {
		$atts = wp_parse_args($atts, array('type' => 'listings', 'width' => 600, 'height' => 400));
		ob_start();
	?>
	 <script type="text/javascript">
    	jQuery(document).ready(function( $ ) {
    		
    		var map = new Map (); 
    		// var filter = new Filters ();
    		var listings = new Listings ({
    			map: map
    			// filter: filter,
    		});
    		<?php if($atts['type'] == 'lifestyle'): ?>
	    		var lifestyle = new Lifestyle( {
	        		map: map
	    		});
    		<?php endif; ?>

    		<?php if($atts['type'] == 'lifestyle_poligon' ): ?>
	    		var lifestyle_poligon = new Lifestyle_Poligon( {
					map: map
	        	});
        	<?php endif;?>
            
            var status = new Status_Window ({map: map, listings:listings});
            
            map.init({
				type: '<?php echo $atts['type']; ?>',
                listings: listings,
                <?php if($atts['type'] == 'lifestyle' ): ?>
                	lifestyle: lifestyle,
                <?php elseif($atts['type'] == 'lifestyle_poligon' ): ?>
                	lifestyle_polygon: lifestyle_polygon,
                <?php endif;?>
                // type: 'lifestyle',
                // type: 'lifestyle_polygon',
                // type: 'neighborhood',
                // type: 'listings',
                status_window: status
            });

    		listings.init();
    		
    	});
    </script>

	<?php
	    echo PLS_Map::listings( null, array('width' => $atts['width'], 'height' => $atts['height']) );
	  	return ob_get_clean();  
	}
	
	public static function advanced_slideshow_entity( $atts ) {
		$atts = wp_parse_args($atts, array(
			'animation' => 'fade', 									// fade, horizontal-slide, vertical-slide, horizontal-push
			'animationSpeed' => 800, 								// how fast animtions are
			'timer' => true,											// true or false to have the timer
			'pauseOnHover' => true,									// if you hover pauses the slider
			'advanceSpeed' => 5000,									// if timer is enabled, time between transitions
			'startClockOnMouseOut' => true,					// if clock should start on MouseOut
			'startClockOnMouseOutAfter' => 1000,		// how long after MouseOut should the timer start again
			'directionalNav' => true, 							// manual advancing directional navs
			'captions' => true, 										// do you want captions?
			'captionAnimation' => 'fade', 					// fade, slideOpen, none
			'captionAnimationSpeed' => 800, 				// if so how quickly should they animate in
			'afterSlideChange' => 'function(){}',		// empty function
			'width' => 610,
			'height' => 320,
			'bullets' => 'false',
			'context' => 'home',
			'featured_option_id' => 'slideshow-featured-listings',
			'listings' => 'limit=5&is_featured=true&sort_by=price'
		));
		ob_start();
		echo PLS_Slideshow::slideshow($atts);
		return ob_get_clean();
	}
	
	
		
		public static function listing_slideshow( $atts ) {
			$atts = wp_parse_args($atts, array(
				'animation' => 'fade', 									// fade, horizontal-slide, vertical-slide, horizontal-push
				'animationSpeed' => 800, 								// how fast animtions are
				'timer' => true,											// true or false to have the timer
				'pauseOnHover' => true,									// if you hover pauses the slider
				'advanceSpeed' => 5000,									// if timer is enabled, time between transitions
				'startClockOnMouseOut' => true,					// if clock should start on MouseOut
				'startClockOnMouseOutAfter' => 1000,		// how long after MouseOut should the timer start again
				'directionalNav' => true, 							// manual advancing directional navs
				'captions' => true, 										// do you want captions?
				'captionAnimation' => 'fade', 					// fade, slideOpen, none
				'captionAnimationSpeed' => 800, 				// if so how quickly should they animate in
				'afterSlideChange' => 'function(){}',		// empty function
				'width' => 610,
				'height' => 320,
				'bullets' => 'false',
				'context' => 'home',
				'featured_option_id' => 'slideshow-featured-listings',
				'listings' => 'limit=5&is_featured=true&sort_by=price'
			));
			ob_start();
			?>
			<style type="text/css">
			.orbit-wrapper .orbit-caption { 
				z-index: 999999 !important;
				margin-top: -113px;
				position: absolute;
				right: 0;
				bottom: 0;
				width: 100%;
			}
			.orbit-caption {
				display: none;
			}
			</style>

			<?php
			echo PLS_Slideshow::slideshow($atts); 
		
			return ob_get_clean();
		}
		
		public static function listing_sub_entity( $atts, $content, $tag ) {
			$val = '';
			
			$listing_list = array();
			
			if( ! is_null( self::$listing ) ) {
				$listing_list = self::$listing;
			} else if ( ! is_null( PL_Shortcodes::$listing ) ) {
				$listing_list = PL_Shortcodes::$listing;
			} else {
				return;
			}
		
			if (array_key_exists($tag, $listing_list['cur_data'])) {
				$val = $listing_list['cur_data'][$tag];
			}else if (array_key_exists($tag, $listing_list['location'])) {
				$val = $listing_list['location'][$tag];
			}else if (array_key_exists($tag, $listing_list['contact'])) {
				$val = $listing_list['contact'][$tag];
			}else if (array_key_exists($tag, $listing_list['rets'])) {
				$val = $listing_list['rets'][$tag];
			}
			else {
			}
		
			// This is an example of handling a specific tag in a different way
			// TODO: make this more elegant...
			switch ($tag)
			{
				case 'desc':
					$max_len = @array_key_exists('maxlen', $atts) ? (int)$atts['maxlen'] : 500;
					$val = substr($val, 0, $max_len);
					break;
				case 'image':
					$width = @array_key_exists('width', $atts) ? (int)$atts['width'] : 180;
					$height = @array_key_exists('height', $atts) ? (int)$atts['height'] : 120;
					$val = PLS_Image::load($listing_list['images'][0]['url'],
							array('resize' => array('w' => $width, 'h' => $height),
									'fancybox' => true,
									'as_html' => true,
									'html' => array('alt' => $listing_list['location']['full_address'],
											'itemprop' => 'image')));
					break;
				case 'gallery':
					ob_start();
					?>
								<div id="slideshow" class="clearfix theme-default left bottomborder">
									<div class="grid_8 alpha">
										<ul class="property-image-gallery grid_8 alpha">
											<?php foreach ($listing_list['images'] as $image): ?>
												<li><?php echo PLS_Image::load($image['url'], 
													                           array('resize' => array('w' => 100, 'h' => 75), 
																			   		 'fancybox' => true, 
																			   		 'as_html' => false, 
																			   		 'html' => array('itemprop' => 'image'))); ?>
												</li>
											<?php endforeach ?>
										</ul>
									</div>
								</div>
							<?php
							$val = ob_get_clean();
							break;
						case 'map':
							$val = PLS_Map::lifestyle($listing_list, array('width' => 590, 'height' => 250, 'zoom' => 16, 'life_style_search' => true,
																			'show_lifestyle_controls' => true, 'show_lifestyle_checkboxes' => true, 
																			'lat' => $listing_list['location']['coords'][0], 'lng' => $listing_list['location']['coords'][1]));
							break;
						case 'price':
							$val = PLS_Format::number($listing_list['cur_data']['price'], array('abbreviate' => false, 'add_currency_sign' => true));
							break;
						case 'listing_type':
							$val = PLS_Format::translate_property_type($listing_list);
							break;
						case 'amenities':
							$amenities = PLS_Format::amenities_but(&$listing_list, array('half_baths', 'beds', 'baths', 'url', 'sqft', 'avail_on', 'price', 'desc'));
							$amen_type = array_key_exists('type', $atts) ? (string)$atts['type'] : 'list';
							ob_start();
							?>
								<div class="amenities-section grid_8 alpha">
				                    <ul>
				                    	<?php if (is_array($amenities[$amen_type])): ?>
				                    	<?php PLS_Format::translate_amenities(&$amenities[$amen_type]); ?>
						                    <?php foreach ($amenities[$amen_type] as $amenity => $value): ?>
						                        <li><span><?php echo $amenity; ?></span> <?php echo $value ?></li>
						                    <?php endforeach ?>		
				                      	<?php endif ?>
				                    </ul>
				                </div>
							<?php 
							$val = ob_get_clean();
							break;
						  case 'compliance':
						  	ob_start();
						  	PLS_Listing_Helper::get_compliance(array('context' => 'listings', 
				  												     'agent_name' => $listing_list['rets']['aname'] , 
				  												     'office_name' => $listing_list['rets']['oname'], 
				  												     'office_phone' => PLS_Format::phone($listing_list['contact']['phone'])));
						  	$val = ob_get_clean();
						  	break;
						default:
					}
					
					return $val;
				}
		
		public static function search_form_entity( $atts ) {
			// Handle attributes using shortcode_atts...
			// Ajax setting as an attr?
			
			// Default form enclosure
			$header = '<form method="post" action="' . esc_url( home_url( '/' ) ) . 'listings" class="pls_search_form_listings">';
			$footer = '</form>';
			wp_register_script( 'modernizr', trailingslashit( PLS_JS_URL ) . 'libs/modernizr/modernizr.min.js' , array(), '2.6.1');
			wp_enqueue_script( 'modernizr' );
			?>
			<script type="text/javascript" src="<?php echo trailingslashit(PLS_JS_URL); ?>scripts/filters.js"></script>
			<script type="text/javascript">
				if (typeof bootloader !== 'object') {
					var bootloader;
				}
	
			  jQuery(document).ready(function( $ ) {
			  	if (typeof bootloader !== 'object') {
			  		bootloader = new SearchLoader();
			  		bootloader.add_param({filter: {context: "shortcode"}});
			  	} else {
			  		bootloader.add_param({filter: {context: "shortcode"}});
			  	}
			  });
			</script>
	
			<?php
			
			// add context and ajax support if missing
			if( !isset( $atts['context'] ) ) { $atts['context'] = 'shortcode'; }
			if( !isset( $atts['ajax'] ) ) { $atts['ajax'] = true; }
			
			return ( $header . PLS_Partials_Listing_Search_Form::init($atts) . $footer );
		} 
		
		/**
		 * Helpers
		 */
		
		private static function get_property_ids( $featured_listing_id ) {
			// if( ! is_int( $featured_listing_id ) ) { }
			$values = get_post_custom( $featured_listing_id );
			$property_ids = isset( $values['keatingbrokerage_meta'] ) ? unserialize($values['keatingbrokerage_meta'][0]) : '';
			$pl_featured_listing_meta = isset( $values['pl_featured_listing_meta'] ) ? unserialize($values['pl_featured_listing_meta'][0]) : '';
			$pl_featured_meta_value = empty( $pl_featured_listing_meta ) ? array('listings' => array()) : $pl_featured_listing_meta['featured-listings-type'];
		
			return $pl_featured_meta_value;
		}
		
		private static function get_filters_by_listing( $static_listing_id ) {
			$static_listings = get_post_meta($static_listing_id, false);
				
			if( ! empty( $static_listings ) && isset( $static_listings['pl_static_listings_option'] ) ) {
				$static_listing_filters = unserialize( $static_listings['pl_static_listings_option'][0] );
				return $static_listing_filters;
			}
			
			return array();
		}
		
		private static function print_filters( $static_listing_filters, $context = 'listings_search' ) {
			
				wp_enqueue_script('filters-featured.js', trailingslashit(PLS_JS_URL) . 'scripts/filters.js', array('jquery'));
				?>
					<script type="text/javascript">

					  jQuery(document).ready(function( $ ) {
					
					    var list = new List ();
					    var filter = new Filters ();
					    var listings = new Listings ({
					      filter: filter,
					      <?php echo do_action('featured_filters_featured_ids'); ?>
					      list: list
					    });
					
					    filter.init({
					      dom_id : "#pls_search_form_listings",
					      class : ".pls_search_form_listings",
					      list : list,
					      listings : listings
					    });
					
					    list.init({
					      dom_id: '#placester_listings_list',
					      filter : filter,
					      class: '.placester_listings_list',
					      listings: listings,
					      context: '<?php echo $context; ?>'
					    });

					    
					    <?php 
					    	 if( !empty( $static_listing_filters ) ) {
							 		echo $static_listing_filters;
							 }
						?>
					    listings.init();
					
					  });
					
					</script>
				
				<?php 
		} 

		public static function partial_one( $listing, $featured_listing_id ) {

			$property_ids = PL_Component_Entity::get_property_ids( $featured_listing_id );
			$property_ids = array_flip( $property_ids );
			
			$api_response = PLS_Plugin_API::get_listings_details_list(array('property_ids' => $property_ids));
			//response is expected to be of fortmat api response
			//no addiitonal formatting needed.
			return $api_response;
		}
		
		public static function print_property_listing_args() {
			global $property_ids;
			echo "property_ids: ['" . implode("','", $property_ids) . "'],";
		}
		
		private static function convert_filters( $filters ) {
			ob_start();
			if( is_array( $filters) ) {
				foreach( $filters as $top_key => $top_value ) {
					if( is_array( $top_value ) ) {
						foreach( $top_value as $key => $value ) {
							echo 'listings.default_filters.push( { "name": "' . $top_key . '[' .  $key . ']", "value" : "'. $value . '" } );';
						}
					} else {
						echo 'listings.default_filters.push( { "name": "'. $top_key . '", "value" : "'. $top_value . '" } );';
					}
				} 
			}
			return ob_get_clean();
		}
		
		// Provide template layout for featured listings
		public static function featured_listings_ajax_templates( $item_html, $listing, $context_var ) {
			//PL_Shortcodes::get_active_snippet_body('listings', self::$featured_context);			
			self::$listing = $listing;

			// get the template attached as a context arg, 33 is the length of the filter prefix
			$template = substr(current_filter(), 33);
			
			$template_body = self::get_active_snippet_body( 'featured_listings', $template );
			
			return do_shortcode( $template_body );
		}
		
		public static function get_shortcode_snippet_list($shortcode, $default_snippets)
		{
			// Get list of custom snippet ids for this shortcode...
			$snippet_list_DB_key = ('pls_' . $shortcode . '_list');
			$snip_arr = get_option($snippet_list_DB_key, self::$defaults );
		
			$snippet_type_map = array();
		
			foreach ($default_snippets as $snippet) {
				$snippet_type_map[$snippet] = 'default';
			}
		
			// Add Custom snippets..
			foreach ($snip_arr as $snippet) {
				$snippet_type_map[$snippet] = 'custom';
			}
		
			return $snippet_type_map;
		}
		
		public static function get_active_snippet_body($shortcode, $template_name = '')
		{
			// Get snippet ID currently associated with this shortcode...
			$option_key = ('pls_' . $shortcode);
			$snippet_name = get_option($option_key, self::$defaults[0]);
		
			// Determine if snippet is custom (in DB) or default (stored in flat-file)
			$snippet_DB_key = ('pls_' . $shortcode . '_' . $snippet_name);
			$type = ( get_option($snippet_DB_key) ? 'custom' : 'default' );
		
			// assign a template as a shortcode arg
			if( ! empty( $template_name ) ) {
				$snippet_name = $template_name;
				$type = 'custom';
				if( in_array( $template_name, self::$defaults ) ) {
					$type = 'default';
				} 
			}
			$snippet_body = PL_Router::load_snippet($shortcode, $snippet_name, $type);
			return $snippet_body;
		}
		
}