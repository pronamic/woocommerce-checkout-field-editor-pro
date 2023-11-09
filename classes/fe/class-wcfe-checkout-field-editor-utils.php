<?php
/**
 * Woo Checkout Field Editor common functions
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Fields_Utils')) :

abstract class WCFE_Checkout_Fields_Utils {
	const OPTION_KEY_CUSTOM_SECTIONS   = 'thwcfe_sections';
	const OPTION_KEY_SECTION_HOOK_MAP  = 'thwcfe_section_hook_map';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwcfe_advanced_settings';
	
	public $pattern = array(			
			'/d/', '/j/', '/l/', '/z/', '/S/', //day (day of the month, 3 letter name of the day, full name of the day, day of the year, )			
			'/F/', '/M/', '/n/', '/m/', //month (Month name full, Month name short, numeric month no leading zeros, numeric month leading zeros)			
			'/Y/', '/y/' //year (full numeric year, numeric year: 2 digit)
		);
		
	public $replace = array(
			'dd','d','DD','o','',
			'MM','M','m','mm',
			'yy','y'
		);

	public function __construct() {
		
	}
	
	public static function get_default_full_address_fields(){
		return array('first_name', 'last_name', 'company', 'country', 'address_1', 'address_2', 'city', 'state', 'postcode');
	}
	
	public function get_default_address_fields(){
		return array('country', 'address_1', 'address_2', 'city', 'state', 'postcode');
	}
	
	public function is_default_address_field( $field_name ){
		$default_address_fields = $this->get_default_address_fields();
		if( $field_name && in_array($field_name, $default_address_fields) ){
			return true;
		}
		return false;
	}
	
	public function get_options_name_title_map(){
		$name_title_map = get_option('thwcfe_field_name_title_map');
		return empty($name_title_map) ? false : $name_title_map;
	}
	
	public function get_section_hook_map(){
		$section_hook_map = get_option(self::OPTION_KEY_SECTION_HOOK_MAP);	
		$section_hook_map = is_array($section_hook_map) ? $section_hook_map : array();
		return $section_hook_map;
	}
		
	public function get_custom_sections(){
		$sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		return empty($sections) ? false : $sections;
	}
	
	public function get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
	public function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
	public function get_settings($key){
		$settings = $this->get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
	public static function get_checkout_sections(){	
		$sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		return !empty($sections) ? $sections : array();
	}
	
	public static function get_checkout_section($section_name){
	 	if(isset($section_name) && !empty($section_name)){	
			$sections = self::get_checkout_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];	
				if(THWCFE_Utils_Section::is_valid_section($section)){
					return $section;
				} 
			}
		}
		return false;
	}
	
	public static function get_all_checkout_fields(){
		$fields = array();
		$sections = self::get_checkout_sections();	
		if($sections){
			$sections = THWCFE_Utils::sort_sections($sections);
			foreach($sections as $sname => $section){	
				$temp_fields = THWCFE_Utils_Section::get_fields($section);
				if($temp_fields && is_array($temp_fields)){
					$fields = array_merge($fields, $temp_fields);
				}
			}
		}
		return $fields;
	}
	
	public static function get_all_checkout_fields_map(){
		$fields = array();
		$sections = self::get_checkout_sections();	
		if($sections){
			foreach($sections as $sname => $section){	
				$temp_fields = THWCFE_Utils_Section::get_fieldset($section);
				if($temp_fields && is_array($temp_fields)){
					$fields = array_merge($fields, $temp_fields);
				}

				$rsections = THWCFE_Utils_Repeat::prepare_repeat_sections($section);
				if($rsections){
					foreach($rsections as $rsname => $rsection){
						$temp_fields = THWCFE_Utils_Section::get_fieldset($rsection);
						if($temp_fields && is_array($temp_fields)){
							$fields = array_merge($fields, $temp_fields);
						}
					}
				}
			}
		}
		return $fields;
	}
	
	public static function get_all_custom_checkout_fields(){
		$fields = array();
		$sections = self::get_checkout_sections();	
		if($sections){
			foreach($sections as $sname => $section){	
				$temp_fields = THWCFE_Utils_Section::get_fields($section);
				if($temp_fields && is_array($temp_fields)){
					foreach($temp_fields as $key => $field){
						if(THWCFE_Utils_Field::is_custom_field($field) && THWCFE_Utils_Field::is_enabled($field)){
							$fields[$key] = $field;
						}
					}
				}
			}
		}
		return $fields;
	}
	
	public function get_field_display_name($field){
		$label = '';
		if(is_array($field)){
			$label = isset($field['label']) && !empty($field['label']) ? $field['label'] : '';
			if(empty($label)){
				$label = isset($field['description']) && !empty($field['description']) ? $field['description'] : $label;
			}
			if(empty($label)){
				$label = isset($field['placeholder']) && !empty($field['placeholder']) ? $field['placeholder'] : $label;
			}
			if(empty($label)){
				$label = isset($field['name']) && !empty($field['name']) ? $field['name'] : $label;
			}
		}
		return $label;
	}

	public static function get_disabled_sections($order_id){
		$order = wc_get_order( $order_id );
		// $dis_sections = get_post_meta($order_id, '_thwcfe_disabled_sections', true);
		$dis_sections = $order->get_meta('_thwcfe_disabled_sections', true);
		if(is_string($dis_sections) && $dis_sections){
			$dis_sections = explode(",", $dis_sections);
		}
		$dis_sections = is_array($dis_sections) ? $dis_sections : array();
		return $dis_sections;
	}
	
	public static function get_disabled_fields($order_id){
		$order = wc_get_order( $order_id );
		// $dis_fields_str = get_post_meta( $order_id, '_thwcfe_disabled_fields', true );
		$dis_fields_str = $order->get_meta('_thwcfe_disabled_fields', true );
		$dis_fields = $dis_fields_str ? explode(",", $dis_fields_str) : array();
		return $dis_fields;
	}
	
	public function is_price_field($field){
		if(is_array($field) && isset($field['price']) && isset($field['price_type'])){
			if((is_numeric($field['price']) && $field['price'] != 0) || $field['price_type'] === 'custom'){
				return true;
			}
		} 
		return false;
	}
	
	public function is_price_option($options){
		$is_price_field = false;
		
		foreach($options as $option) {
			if(isset($option['price']) && is_numeric($option['price']) && $option['price'] != 0){
				$is_price_field = true;
			}
		}
		
		return $is_price_field;
	}
	
	public function is_blank($value) {
		return empty($value) && !is_numeric($value);
	}
	
	public function startsWith($haystack, $needle) {
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}
	
	public function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if($length == 0) {
			return true;
		}
	
		return (substr($haystack, -$length) === $needle);
	}
	
	public function woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}
	
	public function get_product_tax_class_options() {
		if($this->woo_version_check()){
			return wc_get_product_tax_class_options();
		}else{
			$tax_classes           = WC_Tax::get_tax_classes();
			$tax_class_options     = array();
			$tax_class_options[''] = __( 'Standard', 'woocommerce' );
		
			if ( ! empty( $tax_classes ) ) {
				foreach ( $tax_classes as $class ) {
					$tax_class_options[ sanitize_title( $class ) ] = $class;
				}
			}
			return $tax_class_options;
		}
	}

	public static function skip_products_loading(){
		$skip = apply_filters('thwcfe_disable_product_dropdown', false);
		return $skip;
	}
	
	public static function load_products($only_id = false){
		$productsList = array();
		$skip = self::skip_products_loading();

		if(!$skip){
			$posts_per_page = apply_filters('thwcfe_load_products_per_page', -1);
		    $only_id = apply_filters('thwcfe_load_products_id_only', $only_id);
			$args = array( 'post_type' => 'product', 'order' => 'ASC', 'posts_per_page' => $posts_per_page, 'fields' => 'ids' );

			$products = get_posts( $args );
			
			if(count($products) > 0){
				if($only_id){
					return $products;
				}else{
					foreach($products as $pid){				
						//$productsList[] = array("id" => $product->ID, "title" => $product->post_title);
						$productsList[] = array("id" => $pid, "title" => get_the_title($pid));
					}
				}
			}	
		}	
		return $productsList;
	}
	
	/*public static function load_products_cat($only_slug = false){
		$product_cats = self::load_product_terms('product_cat', $only_slug);
		return $product_cats;
	}

	public static function load_product_tags($only_slug = false){
		$product_tags = self::load_product_terms('product_tag', $only_slug);
		return $product_tags;
	}

	public static function load_product_terms($taxonomy, $only_slug = false){
		$product_terms = array();
		$pterms = get_terms($taxonomy, 'orderby=count&hide_empty=0');
		
		if($only_slug){
			foreach($pterms as $pterm){
				$product_terms[] = $pterm->slug;
			}	
		}else{
			foreach($pterms as $pterm){
				$product_terms[] = array("id" => $pterm->slug, "title" => $pterm->name);
			}	
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
	}*/
	
	public function exclude_address_fields($fields){
		$billing_keys  = $this->get_settings('custom_billing_address_keys');
		$shipping_keys = $this->get_settings('custom_shipping_address_keys');
		
		$address_fields = $billing_keys && is_array($billing_keys) ? $billing_keys : array();
		$address_fields = $shipping_keys && is_array($shipping_keys) ? array_merge($address_fields, $shipping_keys) : $address_fields;
		
		if(is_array($fields) && !empty($fields) && $address_fields && is_array($address_fields)){
			foreach($address_fields as $key) {
				unset($fields[$key]);
			}
		}
		
		return $fields;
	}
	
	/*public function get_option_text_from_value($field, $value){
		if(THWCFE_Utils_Field::is_valid_field($field) && apply_filters('thwcfe_display_option_text_instead_of_option_value', true)){
			$type = $field->get_property('type');
			if($type === 'select' || $type === 'radio'){
				$options = $field->get_property('options');
				if(is_array($options) && isset($options[$value]) && $options[$value]['text']){
					//$value = $options[$value]['text'];
					$value = THWCFE_i18n::esc_attr__t($options[$value]['text']);
				}
			}else if($type === 'multiselect' || $type === 'checkboxgroup'){
				$options = $field->get_property('options');
				if(is_array($options)){
					$value = is_array($value) ? $value : array_map('trim', explode(',', $value));
					if(is_array($value)){
						foreach($value as $key => $option_value){
							if(isset($options[$option_value]) && $options[$option_value]['text']){
								//$value[$key] = $options[$option_value]['text'];
								$value[$key] = THWCFE_i18n::esc_attr__t($options[$option_value]['text']);
							}
						}
					}
				}
			}
		}
		return $value;
	}*/
	
	/***************************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - START ******
	 ***************************************************/
	public function woo_hide_default_customer_fields_in_emails($ofields, $sent_to_admin, $order){
		try{
			$fieldset = self::get_all_checkout_fields();
			$default_fields = array('customer_note', 'billing_email', 'billing_phone');
			
			if($fieldset && is_array($fieldset)){
				foreach($default_fields as $key) {
					if(isset($fieldset[$key])){
						$field = $fieldset[$key];
						
						if(THWCFE_Utils_Field::is_valid_field($field)){	
							$show_field = false;
							if($sent_to_admin && $field->get_property('show_in_email')){
								$show_field = true;					
							}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
								$show_field = true;
							}
							
							if(!$show_field){
								unset($ofields[$key]);
							}
						}
					}
				}
			}
		}catch(Exception $e){
			//sef::write_log('Error in WCFE Utils', $e);
		}
		return $ofields;
	}


	private function may_display_section_title($section, $fields){
		if(is_array($fields) && !empty($fields) && THWCFE_Utils_Section::is_show_section_title($section, 'emails')){
			$sname = $section->get_property('name');

			$section_title = array();
			$section_title['label'] = '';
			$section_title['value'] = $section->get_property('title');
			$section_title['type']  = 'heading';

			$fields = array_merge(array('section_title_'.$sname => $section_title), $fields);
		}
		return $fields;
	}



	public function prepare_repeat_fields_for_emails($order_id, $key, $field, $rfnames){
		$order = wc_get_order( $order_id );
		$custom_fields = array();
		$rfields = THWCFE_Utils_Repeat::get_repeat_fields($order_id, $key, $field, $rfnames);

		if(is_array($rfields)){
			foreach($rfields as $rkey => $rfield) {
				// $value = get_post_meta( $order_id, $rkey, true );
				$value = $order->get_meta($rkey, true );
				$type = $rfield->get_property('type');
				$label = $rfield->get_property('title');

				$custom_field = array();
				$custom_field['label'] = $label;
				$custom_field['value'] = $value;
				$custom_field['type']  = $type;

				$custom_fields[$rkey] = $custom_field;
			}
		}
		return $custom_fields;
	}
	/***************************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - END ********
	 ***************************************************/

	
	/***************************************************
	 **** ADDRESS DISPLAY FORMAT FUNCTIONS - START *****
	 ***************************************************/
	public function woo_localisation_address_formats($formats){
		$address_formats_str = $this->get_settings('address_formats');
		
		$custom_formats = array();
		if(!empty($address_formats_str)){
			$address_formats_arr = explode("|", $address_formats_str);
			
			if(is_array($address_formats_arr) && !empty($address_formats_arr)){
				foreach($address_formats_arr as $address_format) {
					if(!empty($address_format)){
						$format_arr = explode("=>", $address_format);
						if(is_array($format_arr) && count($format_arr) == 2){
							$frmt = str_replace('\n', "\n", $format_arr[1]);
							$custom_formats[trim($format_arr[0])] = $frmt;
						}
					}
				}
			}
		}
		//$custom_formats['IN'] = "{name}\n{company}\n{address_1}\n{address_2}\n{city}, {state} {postcode}\n{country}\n{billing_add_1}\n{shipping_add1}";
		
		if(is_array($formats) && $custom_formats && is_array($custom_formats)){
			$formats = array_merge($formats, $custom_formats);
		}

		return $formats;
	}
	
	public function woo_formatted_address_replacements( $array, $args ) { 
		$billing_keys  = $this->get_settings('custom_billing_address_keys');
		$shipping_keys = $this->get_settings('custom_shipping_address_keys');
		
		$replacement_keys = $billing_keys && is_array($billing_keys) ? $billing_keys : array();
		$replacement_keys = $shipping_keys && is_array($shipping_keys) ? array_merge($replacement_keys, $shipping_keys) : $replacement_keys;
		
		if($replacement_keys && is_array($replacement_keys)){
			foreach($replacement_keys as $key) {
				$array['{'.$key.'}'] = isset($args[$key]) ? $args[$key] : '';
			}
		}
		
		return $array; 
	}
	
	public function woo_order_formatted_billing_address($address, $wc_order){
		$billing_keys  = $this->get_settings('custom_billing_address_keys');
		
		$order_id = false;
		if($this->woo_version_check()){
			$order_id = $wc_order->get_id();
		}else{
			$order_id = $wc_order->id;
		}
		
		$order = wc_get_order( $order_id );

		if($billing_keys && is_array($billing_keys) && $order_id){
			foreach($billing_keys as $key) {
				if($this->startsWith($key, 'billing_')){
					//$address[$key] = $wc_order->{$key};
					// $address[$key] = get_post_meta( $order_id, $key, true );
					$address[$key] = $order->get_meta( $key, true );
				}else{
					// $value = get_post_meta( $order_id, $key, true );
					$value = $order->get_meta( $key, true );
					$user_id = $wc_order->get_user_id();
					if(empty($value) && $user_id && apply_filters('thwcfe_show_hidden_field_value_in_address_format',true)){
						$value = get_user_meta($user_id, $key, true);
					}
					$value = is_array($value) ? implode(",", $value) : $value;
					$address[$key] = $value;
				}
			}
		}
		return $address;
	}
	
	public function woo_order_formatted_shipping_address($address, $wc_order){
		if(!is_array($address)){
			return $address;
		}
		
		$shipping_keys = $this->get_settings('custom_shipping_address_keys');
		if($this->is_ship_to_billing($wc_order)){
			$shipping_keys  = $this->get_settings('custom_billing_address_keys');
		}
		
		$order_id = false;
		if($this->woo_version_check()){
			$order_id = $wc_order->get_id();
		}else{
			$order_id = $wc_order->id;
		}
		
		$order = wc_get_order( $order_id );

		if($shipping_keys && is_array($shipping_keys)){
			foreach($shipping_keys as $key) {
				if($this->startsWith($key, 'shipping_')){
					//$address[$key] = $wc_order->{$key};
					// $address[$key] = get_post_meta( $order_id, $key, true );
					$address[$key] = $order->get_meta( $key, true );
				}else{
					// $value = get_post_meta( $order_id, $key, true );
					$value = $order->get_meta( $key, true );
					$user_id = $wc_order->get_user_id();
					if(empty($value) && $user_id && apply_filters('thwcfe_show_hidden_field_value_in_address_format',true)){
						$value = get_user_meta($user_id, $key, true);
					}
					$value = is_array($value) ? implode(",", $value) : $value;
					$address[$key] = $value;
				}
			}
		}
	
		return $address;
	}
	
	public function woo_my_account_my_address_formatted_address($args, $customer_id, $name){
		if($name === 'billing'){
			$billing_keys  = $this->get_settings('custom_billing_address_keys');
		
			if($billing_keys && is_array($billing_keys)){
				foreach($billing_keys as $key) {
					$args[$key] = get_user_meta($customer_id, $key, true);
				}
			}
		}
		
		if($name === 'shipping'){
			$shipping_keys = $this->get_settings('custom_shipping_address_keys');
		
			if($shipping_keys && is_array($shipping_keys)){
				foreach($shipping_keys as $key) {
					$args[$key] = get_user_meta($customer_id, $key, true);
				}
			}
		}
		return $args;
	}
	
	public function is_ship_to_billing($wc_order){
		$order_id = false;
		if($this->woo_version_check()){
			$order_id = $wc_order->get_id();
		}else{
			$order_id = $wc_order->id;
		}
		// $order = wc_get_order( $order_id );
		$order = is_array(wc_get_order( $order_id )) ? wc_get_order( $order_id ) : wc_get_orders( $order_id )[0];

		// $shipp_to_billing = get_post_meta($order_id, '_thwcfe_ship_to_billing', true);
		$shipp_to_billing = $order->get_meta( '_thwcfe_ship_to_billing', true );
		return $shipp_to_billing;
	}
	
	/***************************************************
	 **** ADDRESS DISPLAY FORMAT FUNCTIONS - END *******
	 ***************************************************/
	
	public static function get_file_display_name($upload_info, $downloadable=true){
		$dname = '';
		if(is_array($upload_info)){
			$dname = isset($upload_info['name']) ? $upload_info['name'] : '';
			$url = isset($upload_info['url']) ? $upload_info['url'] : '';
			$price_info = isset($upload_info['price_info']) ? $upload_info['price_info'] : '';
			
			if($dname && $downloadable && $url){
				$dname  = '<a href="'.$url.'" target="_blank">'.$dname.'</a>';
				$dname .= $price_info ? $price_info : '';
			}
		}else{
			$dname = $upload_info ? $upload_info : '';
		}
		return $dname;
	}
	
	public static function get_file_display_name_order($upload_info_json, $downloadable=true){
		$dname = '';
		if($upload_info_json){
			$upload_info = json_decode($upload_info_json, true);
			if(!$upload_info){
				$last_index = strrpos( $upload_info_json, '}');
				$_upload_info_json = substr($upload_info_json, 0, $last_index+1);
				$upload_info = json_decode($_upload_info_json, true);
				$upload_info['price_info'] = substr($upload_info_json, $last_index+1);
			}

			if(is_array($upload_info)){
				$count = count($upload_info);
				$i = 0;
				foreach ($upload_info as $name => $uploaded) {
					$dname .= self::get_file_display_name($uploaded, $downloadable);
					if(++$i < $count) {
						$dname .= ', ';
					}
				}
			}
		}
		return $dname;
	}
	
	public static function upload_dir($upload_dir){
		$subdir = '';
		if(apply_filters('thwcfe_uploads_use_unique_folders', true)){
			global $woocommerce;
			$subdir = '/' . md5($woocommerce->session->get_customer_id());
			
		}else if(apply_filters('thwcfe_uploads_use_yearmonth_folders', false)){
			$time = current_time('mysql');
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "/$y/$m";
		}
	 	
		$upload_path = rtrim(apply_filters('thwcfe_upload_path', '/thwcfe_uploads/'), '/');
		$subdir = $upload_path . $subdir;
		
		if(empty($upload_dir['subdir'])){
			$upload_dir['path'] = $upload_dir['path'] . $subdir;
			$upload_dir['url'] = $upload_dir['url'] . $subdir;
		} else {
			$upload_dir['path'] = str_replace( $upload_dir['subdir'], $subdir, $upload_dir['path'] );
			$upload_dir['url'] = str_replace( $upload_dir['subdir'], $subdir, $upload_dir['url'] );
		}
		$upload_dir['subdir'] = $subdir;
	 	
		return $upload_dir;
	}

	public static function get_upload_subdir(){
		$subdir = '';
		if(apply_filters('thwcfe_uploads_use_unique_folders', true)){
			global $woocommerce;
			$subdir = '/' . md5($woocommerce->session->get_customer_id());
			
		}else if(apply_filters('thwcfe_uploads_use_yearmonth_folders', false)){
			$time = current_time('mysql');
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "/$y/$m";
		}
	 	
		$upload_path = rtrim(apply_filters('thwcfe_upload_path', '/thwcfe_uploads/'), '/');
		$subdir = $upload_path . $subdir;
	 	
		return $subdir;
	}
	
	public static function upload_mimes(){
	
	}
	
	/*********************************
	 **** i18n FUNCTIONS - START *****
	 ********************************/
	
	/* WPML SUPPORT */
	public static function wcfe_wpml_register_string($name, $value ){
		//$context = 'ThemeHigh - Checkout Field Editor';
		$context = 'woocommerce-checkout-field-editor-pro';
		$name = "WCFE - ".$value;
		
		if(function_exists('icl_register_string')){
			icl_register_string($context, $name, $value);
		}
	}
	
	public static function wcfe_wpml_unregister_string($name){
		$context = 'woocommerce-checkout-field-editor-pro';
		
		if(function_exists('icl_unregister_string')){
			icl_unregister_string($context, $name);
		}
	}
	
	public static function wcfe_icl_t($value){
		$context = 'woocommerce-checkout-field-editor-pro';
        $name = "WCFE - ".$value;
		
		if(function_exists('icl_t')){
			$value = icl_t($context, $name, $value);
		}
		return $value;
	}
	
	/*********************************
	 **** i18n FUNCTIONS - END *******
	 ********************************/
	 
	public function get_jquery_date_format($woo_date_format){				
		$woo_date_format = !empty($woo_date_format) ? $woo_date_format : wc_date_format();
		return preg_replace($this->pattern, $this->replace, $woo_date_format);	
	}
		
	public function wcfe_add_error($msg, $errors=false){
		if($errors){
			$errors->add('validation', $msg);
		}else if(defined('WC_VERSION') && version_compare(WC_VERSION, '2.3.0', '>=')){
			wc_add_notice($msg, 'error');
		} else {
			WC()->add_error($msg);
		}
	}
		
	/*public function debug_info($description){
		$post_id = 125;
		$post = array(
			'ID'           => $post_id,
			'post_content' => $description,
		);
		wp_update_post( $post );
	}*/
}

endif;
