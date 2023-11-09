<?php
/**
 * The admin settings page common utility functionalities.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Utils')):

class THWCFE_Admin_Utils extends WCFE_Checkout_Fields_Utils{
	protected static $_instance = null;	
	
	public function __construct() {		
		
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function init() {	
		$this->prepare_sections_and_fields();
	}

	public static function get_checkout_section($section_name, $cart_info=false){
	 	if(isset($section_name) && !empty($section_name)){	
			$sections = THWCFE_Utils::get_custom_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];	
				if(THWCFE_Utils_Section::is_valid_section($section)){
					return $section;
				} 
			}
		}
		return false;
	}
	
	public function update_section($section){
	 	if(THWCFE_Utils_Section::is_valid_section($section)){	
			$sections = THWCFE_Utils::get_custom_sections();
			$sections = is_array($sections) ? $sections : array();
			
			$sections[$section->name] = $section;
			
			$result1 = $this->save_sections($sections);
			$result2 = $this->update_section_hook_map($section);
	
			return $result1;
		}
		return false;
	}
	
	public function save_sections($sections){
		$autoload = apply_filters('thwcfe_option_autoload', 'no');
		$result = update_option(THWCFE_Utils::OPTION_KEY_CUSTOM_SECTIONS, $sections, $autoload);
		return $result;
	}
	
	public function update_section_hook_map($section){
		$section_name  = $section->name;
		$display_order = $section->get_property('order');
		$hook_name 	   = $section->position;
				
	 	if(isset($hook_name) && isset($section_name) && !empty($hook_name) && !empty($section_name)){	
			$hook_map = $this->get_section_hook_map();
			
			//Remove from hook if already hooked
			if($hook_map && is_array($hook_map)){
				foreach($hook_map as $hname => $hsections){
					if($hsections && is_array($hsections)){
						if(($key = array_search($section_name, $hsections)) !== false) {
							unset($hsections[$key]);
							$hook_map[$hname] = $hsections;
						}
					}
					
					if(empty($hsections)){
						unset($hook_map[$hname]);
					}
				}
			}
			
			if(isset($hook_map[$hook_name])){
				$hooked_sections = $hook_map[$hook_name];
				if(!in_array($section_name, $hooked_sections)){
					$hooked_sections[] = $section_name;
					$hooked_sections = $this->sort_hooked_sections($hooked_sections);
					
					$hook_map[$hook_name] = $hooked_sections;
					$this->save_section_hook_map($hook_map);
				}
			}else{
				$hooked_sections = array();
				$hooked_sections[] = $section_name;
				$hooked_sections = $this->sort_hooked_sections($hooked_sections);
				
				$hook_map[$hook_name] = $hooked_sections;
				$this->save_section_hook_map($hook_map);
			}					
		}
	}
	
	public function save_section_hook_map($section_hook_map){
		$autoload = apply_filters('thwcfe_option_autoload', 'no');
		$result = update_option(THWCFE_Utils::OPTION_KEY_SECTION_HOOK_MAP, $section_hook_map, $autoload);		
		return $result;
	}
	
	public function remove_section_from_hook($hook_name, $section_name){
		if(isset($hook_name) && isset($section_name) && !empty($hook_name) && !empty($section_name)){	
			$hook_map = $this->get_section_hook_map();
			if(isset($hook_map[$hook_name])){
				$hooked_sections = $hook_map[$hook_name];
				if(!in_array($section_name, $hooked_sections)){
					unset($hooked_sections[$section_name]);				
					$hook_map[$hook_name] = $hooked_sections;
					$this->save_section_hook_map($hook_map);
				}
			}				
		}
	}
	
	public function prepare_sections_and_fields($copy_free_version_settings=false){
		$sections = $this->get_checkout_sections();
		if(empty($sections)){
			$sections = $this->get_default_sections($copy_free_version_settings);
			
			$old_custom_sections = get_option('thwcfd_custom_checkout_sections');
			$old_cfields = get_option('thwcfd_checkout_fields');
			
			if($sections && is_array($sections)){
				if($old_cfields && is_array($old_cfields)){
					foreach($sections as $sname => $section){
						$old_sname = 'wcfd_fields_'.$sname;
						if(isset($old_cfields[$old_sname])){
							$old_fields = $old_cfields[$old_sname];
							$fields = $this->prepare_fields_objects($old_fields);
							
							if(!empty($fields)){
								//$section->set_fields($fields);
								$section->set_property('fields', $fields);
							}
						}
					}
				}
				
				$this->save_sections($sections);
				
				if($old_custom_sections && is_array($old_custom_sections)){
					foreach($old_custom_sections as $old_csname => $old_csection){
						$section = $this->prepare_section_object($old_csection, $old_cfields);
						if($section){
							//$sections[$old_csname] = $section;
							$this->update_section($section);
						}
					}
				}
			}
			$this->clear_old_settings();
		}
	}
	
	public function prepare_section_object($section_arr, $fields_arr){
		$section = false;
		if($section_arr && is_array($section_arr)){
			$sname = $section_arr['name'];
			
			$section = new WCFE_Checkout_Section();
			$section->set_property('id', $sname);
			$section->set_property('name', $sname);
			$section->set_property('title', $section_arr['label']);
			$section->set_property('position', $section_arr['position']);
			$section->set_property('custom_section', 1);
			$section->set_property('show_title', $section_arr['use_as_title']);
			/*$section->set_id($sname);
			$section->set_name($sname);
			$section->set_title($section_arr['label']);
			$section->set_position($section_arr['position']);
			$section->set_custom_section(1);
			$section->set_show_title($section_arr['use_as_title']);*/
			
			if($fields_arr && is_array($fields_arr) && isset($fields_arr['wcfd_fields_'.$sname])){
				$old_fields = $fields_arr['wcfd_fields_'.$sname];
				$fields = $this->prepare_fields_objects($old_fields);
				$section->set_property('fields', $fields);
			}
		}
		return $section;
	}
	
	public function prepare_fields_objects($fields){			
		$field_objects = array();
		if($fields && !empty($fields) && is_array($fields)){
			foreach($fields as $name => $field){
				if(!empty($name) && !empty($field) && is_array($field)){
					$field['type'] = isset($field['type']) ? $field['type'] : 'text';
					$field_object = THWCFE_Utils_Field::create_field($field['type'], $name, $field); 
				
					if($field_object){
						$field_objects[$name] = $field_object;
					}
				}
			}
		}
		
		return $field_objects;
	}
	
	public function get_default_sections($copy_free_version_settings=false){
		$checkout_fields = $this->get_default_checkout_fields('', $copy_free_version_settings);
		$default_sections = array('billing' => 'Billing Details', 'shipping' => 'Shipping Details', 'additional' => 'Additional Details');
		$default_sections = apply_filters('thwcfe_default_checkout_sections', $default_sections);

		$sections = array();
		$order = -3;
		foreach($checkout_fields as $fieldset => $fields){
			$fieldset = $fieldset && $fieldset === 'order' ? 'additional' : $fieldset;
			$title = isset($default_sections[$fieldset]) ? $default_sections[$fieldset] : '';

			$section = new WCFE_Checkout_Section();
			$section->set_property('id', $fieldset);
			$section->set_property('name', $fieldset);
			$section->set_property('order', $order);
			$section->set_property('title', $title);
			$section->set_property('custom_section', 0);
			$section->set_property('fields', $this->prepare_default_fields($fields));

			$sections[$fieldset] = $section;
			$order++;
		}
		
		return $sections;
	}

	public function prepare_default_fields($fields){
		$field_objects = array();
		$default_fields_id = array(
					'billing_first_name' => array(
						'label'          => 'First name',
					),
					'billing_last_name'  => array(
						'label'          => 'Last name',
					),
					'billing_company'    => array(
						'label'          => 'Company name',
					),
					'billing_country'    => array(
						'label'          =>  'Country / Region',
					),	
					'billing_address_1'  => array(
						'label'          => 'Street address',
						'placeholder'  	 => 'House number and street name',
					),
					'billing_address_2'  => array(
						'label'        => 'Apartment, suite, unit, etc.',
						'placeholder'  => 'Apartment, suite, unit, etc. (optional)',
					),
					'billing_city'       => array(
						'label'        => 'Town / City',
					),
					'billing_state'      => array(
						'label'        => 'State / County',
					),
					'billing_postcode'   => array(
						'label'        => 'Postcode / ZIP',
					),
					'shipping_first_name' => array(
						'label'        => 'First name',
					),
					'shipping_last_name'  => array(
						'label'        => 'Last name',
					),
					'shipping_company'    => array(
						'label'        => 'Company name',
					),
					'shipping_country'    => array(
						'label'        =>  'Country / Region',
					),	
					'shipping_address_1'  => array(
						'label'        => 'Street address',
						'placeholder'  => 'House number and street name',
					),
					'shipping_address_2'  => array(
						'label'        => 'Apartment, suite, unit, etc.',
						'placeholder'  => 'Apartment, suite, unit, etc. (optional)',
					),
					'shipping_city'       => array(
						'label'        => 'Town / City',
					),
					'shipping_state'      => array(
						'label'        => 'State / County',
					),
					'shipping_postcode'   => array(
						'label'        => 'Postcode / ZIP',
					),
					'billing_phone' => array(
						'label' => 'Phone Number',
					),
					'billing_email' => array(
						'label' => 'Email Address',
					),
					'order_comments' => array(
						'label'       => 'Order notes',
						'placeholder' => 'Notes about your order, e.g. special notes for delivery.',
					)
				);

		if(is_array($fields)){
			foreach($fields as $name => $field){
				if(!empty($name) && !empty($field) && is_array($field)){
					$field['type'] = isset($field['type']) ? $field['type'] : 'text';
					$field_object = THWCFE_Utils_Field::create_field($field['type'], $name, $field); 

					if(array_key_exists($name, $default_fields_id) && is_object($field_object)){
						$field_object->title = $default_fields_id[$name]['label'];
						if($field_object->placeholder != '' && isset($default_fields_id[$name]['placeholder'])){
							$field_object->placeholder = $default_fields_id[$name]['placeholder'];
						}
					}
					if(($name === 'billing_state' || $name === 'shipping_state') && isset($field['country'])){
						$field_object->set_property('country', '');
					}
				
					if($field_object){
						$field_objects[$name] = $field_object;
					}
				}
			}
		}
		return $field_objects;
	}

	public function reset_section_fields($all_sections, $section_name, $sectionCopy){
		$checkout_fields = $this->get_default_checkout_fields('');

		if($section_name == 'additional'){
			$default_fields = isset($checkout_fields['order']) ? $checkout_fields['order'] : array();
		}else{
			$default_fields = isset($checkout_fields[$section_name]) ? $checkout_fields[$section_name] : array();
		}

		$sectionCopy->set_property('fields', $this->prepare_default_fields($default_fields));

		if(THWCFE_Utils_Section::is_valid_section($sectionCopy)){
			$all_sections[$section_name] = $sectionCopy;
			$this->save_sections($all_sections);
		}
	}

	/*public function remove_filter($tag, $class, $function, $priority){
		global $wp_filter;
		$obj = null;
		
		if(isset($wp_filter[$tag]) && $wp_filter[$tag]->callbacks){
			$r = $wp_filter[$tag]->callbacks;

			foreach($r as $priority => $callbacks) {
				if(is_array($callbacks)){
					foreach($callbacks as $key => $callback) {
						if(isset($callback['function'])){
							$f = $callback['function'];
							THWCFE_Utils::write_log('In magic loop...');
							THWCFE_Utils::write_log($f);
							if(is_array($f) && isset($f[0]) && isset($f[1])){
								if(is_a($f[0], $class) && $f[1] === $function){
									$obj = $f[0];
									THWCFE_Utils::write_log(spl_object_hash($obj));
								}
							}
						}
					}
				}
			}
		}

		remove_filter($tag, array($obj, $function), $priority);	
	}

	public function remove_free_version_checkout_hooks(){
		if(class_exists('THWCFD_Checkout')){
			$this->remove_filter('woocommerce_checkout_fields', 'THWCFD_Checkout', 'checkout_fields', 1000);
		}
	}*/

	public function get_default_checkout_fields($fieldset = '', $copy_free_version_settings=false) {
		// Fields are based on billing/shipping country. Grab those values but ensure they are valid for the store before using.
		$billing_country   = WC()->countries->get_base_country();
		$allowed_countries = WC()->countries->get_allowed_countries();

		if ( ! array_key_exists( $billing_country, $allowed_countries ) ) {
			$billing_country = current( array_keys( $allowed_countries ) );
		}

		$shipping_country  = WC()->countries->get_base_country();
		$allowed_countries = WC()->countries->get_shipping_countries();

		if ( ! array_key_exists( $shipping_country, $allowed_countries ) ) {
			$shipping_country = current( array_keys( $allowed_countries ) );
		}

		$checkout_fields = array(
			'billing'  => WC()->countries->get_address_fields(
				$billing_country,
				'billing_'
			),
			'shipping' => WC()->countries->get_address_fields(
				$shipping_country,
				'shipping_'
			),
			'order'    => array(
				'order_comments' => array(
					'type'        => 'textarea',
					'class'       => array( 'notes' ),
					'label'       => __( 'Order notes', 'woocommerce' ),
					'placeholder' => esc_attr__(
						'Notes about your order, e.g. special notes for delivery.',
						'woocommerce'
					),
				),
			),
		);

		if(!$copy_free_version_settings){
			$checkout_fields = apply_filters( 'woocommerce_checkout_fields', $checkout_fields );
		}

		foreach ( $checkout_fields as $field_type => $fields ) {
			// Sort each of the checkout field sections based on priority.
			uasort( $checkout_fields[ $field_type ], 'wc_checkout_fields_uasort_comparison' );

			// Add accessibility labels to fields that have placeholders.
			foreach ( $fields as $single_field_type => $field ) {
				if ( empty( $field['label'] ) && ! empty( $field['placeholder'] ) ) {
					$checkout_fields[ $field_type ][ $single_field_type ]['label']       = $field['placeholder'];
					$checkout_fields[ $field_type ][ $single_field_type ]['label_class'] = 'screen-reader-text';
				}
			}
		}

		return $fieldset ? $checkout_fields[ $fieldset ] : $checkout_fields;
	}
	
	public function postmeta_form_keys($keys, $post){
		if($post && $post->post_type === 'shop_order'){
			$custom_fields = self::get_all_custom_checkout_fields();
			$custom_field_keys = array();
			if(is_array($custom_fields)){
				foreach($custom_fields as $key => $field){
					$custom_field_keys[] = $key;
				}
			}
			
			if(!empty($custom_field_keys)){
				if(apply_filters('thwcfe_postmeta_form_keys_show_custom_fields_only', false)){
					return $custom_field_keys;
				}
			
				global $wpdb;
			
				if ( null === $keys ) {
					$limit = apply_filters( 'postmeta_form_limit', 30 );
					$sql = "SELECT DISTINCT meta_key
					FROM $wpdb->postmeta
					WHERE meta_key NOT BETWEEN '_' AND '_z'
					HAVING meta_key NOT LIKE %s
					ORDER BY meta_key
					LIMIT %d";
					$keys = $wpdb->get_col( $wpdb->prepare( $sql, $wpdb->esc_like( '_' ) . '%', $limit ) );
				}

				$keys = array_diff($keys, $custom_field_keys);
				$keys = array_merge($custom_field_keys, $keys);
			}			
		}
		return $keys;
	}


	public static function load_products_cat($only_slug = false){
		$product_cats = self::load_product_terms('category', 'product_cat', $only_slug);
		return $product_cats;
	}

	public static function load_product_tags($only_slug = false){
		$product_tags = self::load_product_terms('tag', 'product_tag', $only_slug);
		return $product_tags;
	}

	public static function load_product_terms($type, $taxonomy, $only_slug = false){
		$product_terms = array();
		$pterms = get_terms($taxonomy, 'orderby=count&hide_empty=0');

		$ignore_translation = true;
		$is_wpml_active = THWCFE_Utils::is_wpml_active();
		if($is_wpml_active && $ignore_translation){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;
			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
		}

		if(is_array($pterms)){
			foreach($pterms as $term){
				$dterm = $term;

				if($is_wpml_active && $ignore_translation){
					$dterm = THWCFE_Utils::get_default_lang_term($term, $taxonomy, $default_lang);
				}

				if($only_slug){
					$product_terms[] = $dterm->slug;
				}else{
					$product_terms[] = array("id" => $dterm->slug, "title" => $dterm->name);
				}
			}
		}

		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}

		return $product_terms;
	}

	public static function load_user_roles($only_id = false){
		$user_roles = array();
		
		global $wp_roles;
    	$roles = $wp_roles->roles;
		//$roles = get_editable_roles();
		
		if($only_id){
			foreach($roles as $key => $role){
				$user_roles[] = $key;
			}
		}else{
			foreach($roles as $key => $role){
				$user_roles[] = array("id" => $key, "title" => $role['name']);
			}
		}
		return $user_roles;
	}


	public function stable_uasort(&$array, $cmp_function) {
		if(count($array) < 2) {
			return;
		}
		
		$halfway = count($array) / 2;
		$array1 = array_slice($array, 0, $halfway, TRUE);
		$array2 = array_slice($array, $halfway, NULL, TRUE);
	
		$this->stable_uasort($array1, $cmp_function);
		$this->stable_uasort($array2, $cmp_function);
		if(call_user_func_array($cmp_function, array(end($array1), reset($array2))) < 1) {
			$array = $array1 + $array2;
			return;
		}
		
		$array = array();
		reset($array1);
		reset($array2);
		while(current($array1) && current($array2)) {
			if(call_user_func_array($cmp_function, array(current($array1), current($array2))) < 1) {
				$array[key($array1)] = current($array1);
				next($array1);
			} else {
				$array[key($array2)] = current($array2);
				next($array2);
			}
		}
		while(current($array1)) {
			$array[key($array1)] = current($array1);
			next($array1);
		}
		while(current($array2)) {
			$array[key($array2)] = current($array2);
			next($array2);
		}
		return;
	}
	
	public function sort_sections(&$sections){
		if(is_array($sections) && !empty($sections)){
			$this->stable_uasort($sections, array($this, 'sort_sections_by_order'));
		}
	}
	
	public function sort_sections_by_order($a, $b){
		if(THWCFE_Utils_Section::is_valid_section($a) && THWCFE_Utils_Section::is_valid_section($b)){
			$order_a = is_numeric($a->get_property('order')) ? $a->get_property('order') : 0;
			$order_b = is_numeric($b->get_property('order')) ? $b->get_property('order') : 0;
			
			if($order_a == $order_b){
				return 0;
			}
			return ($order_a < $order_b) ? -1 : 1;
		}else{
			return 0;
		}
	}
	
	public function sort_hooked_sections($hsections){
		$sections = array();
		if(is_array($hsections) && !empty($hsections)){
			$custom_sections = $this->get_custom_sections();
			if(is_array($custom_sections) && !empty($custom_sections)){
				foreach($hsections as $sname){
					$temp = array();
					$temp['name'] = $sname;
						
					$section = isset($custom_sections[$sname]) ? $custom_sections[$sname] : false;
					if($section){
						$temp['order'] = $section->get_property('order');
					}else{
						$temp['order'] = 0;
					}
					
					$sections[] = $temp;
				}
			}
		}
	
		$this->stable_uasort($sections, array($this, 'sort_hooked_sections_by_order'));
		$result = array();
		foreach($sections as $section){
			$result[] = $section['name'];
		}
		
		return $result;
	}
	
	public function sort_hooked_sections_by_order($a, $b){
		if(is_array($a) && is_array($b)){
			$order_a = isset($a['order']) && is_numeric($a['order']) ? $a['order'] : 0;
			$order_b = isset($b['order']) && is_numeric($b['order']) ? $b['order'] : 0;
			
			if($order_a == $order_b){
				return 0;
			}
			return ($order_a < $order_b) ? -1 : 1;
		}else{
			return 0;
		}
	}
	
	/********************************************
	*-------- OLDER VERSION SUPPORT - START -----
	********************************************/
	public function clear_old_settings(){
		delete_option("thwcfd_custom_checkout_sections");
		delete_option("thwcfd_section_hook_map");
		delete_option('thwcfd_checkout_fields');
	}
	/********************************************
	*-------- OLDER VERSION SUPPORT - END -------
	********************************************/
}

endif;