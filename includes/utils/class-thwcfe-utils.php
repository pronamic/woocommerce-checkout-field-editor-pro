<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils')):

class THWCFE_Utils {
	const OPTION_KEY_CUSTOM_SECTIONS   = 'thwcfe_sections';
	const OPTION_KEY_SECTION_HOOK_MAP  = 'thwcfe_section_hook_map';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwcfe_advanced_settings';

	static $PATTERN = array(
			'/d/', '/j/', '/l/', '/z/', '/S/', //day (day of the month, 3 letter name of the day, full name of the day, day of the year, )
			'/F/', '/M/', '/n/', '/m/', //month (Month name full, Month name short, numeric month no leading zeros, numeric month leading zeros)
			'/Y/', '/y/' //year (full numeric year, numeric year: 2 digit)
		);

	static $REPLACE = array(
			'dd','d','DD','o','',
			'MM','M','m','mm',
			'yy','y'
		);

	static $IMG_FILE_TYPES = array("image/jpg", "image/png", "image/gif", "image/jpeg");

	public static function get_default_address_fields(){
		return array('country', 'address_1', 'address_2', 'city', 'state', 'postcode');
	}

	public static function is_default_address_field($field_name){
		$default_address_fields = self::get_default_address_fields();
		if($field_name && in_array($field_name, $default_address_fields) ){
			return true;
		}
		return false;
	}

	/**********************************************
	********* COMMON UTIL FUNCTIONS - START *******
	**********************************************/

	public static function wcfe_capability() {
		$allowed = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('thwcfe_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}

	public static function get_cart_summary(){
		$cart = array();
		$cart['products']   = array();
		$cart['categories'] = array();
		$cart['tags'] 		= array();
		$cart['variations'] = array();
		$cart['shipping_class'] = array();
		$cart['product_type'] = array();

		if(WC()->cart){
			$items = WC()->cart->get_cart();
			$cart_total = WC()->cart->total;
			$cart_subtotal = WC()->cart->subtotal;
			$shipping_weight = WC()->cart->get_cart_contents_weight();

			foreach($items as $item => $values) {
				$product_id = $values['product_id'];

				$cart['products'][] = self::get_original_product_id($product_id);
				$cart['categories'] = array_merge( $cart['categories'], self::get_product_categories($product_id) );
				$cart['tags']       = array_merge( $cart['tags'], self::get_product_tags($product_id) );
				$cart['shipping_class'] = array_merge($cart['shipping_class'] ,self::get_product_shipping_class($product_id));
				$cart['product_type'][] = WC_Product_Factory::get_product_type($product_id);
				if($values['variation_id']){
					$cart['variations'][] = $values['variation_id'];
					$cart['products'][] = self::get_original_product_id($values['variation_id']);
				}
				
			}

			$cart['products']      = array_values($cart['products']);
			$cart['categories']    = apply_filters('thwcfe_cart_product_categories', array_values($cart['categories']));
			$cart['tags'] 		   = apply_filters('thwcfe_cart_product_tags', array_values($cart['tags']));
			$cart['variations']    = array_values($cart['variations']);
			$cart['product_type']  = array_values($cart['product_type']); 
			$cart['shipping_class'] = array_values($cart['shipping_class']);
			$cart['cart_total']    = $cart_total;
			$cart['cart_subtotal'] = $cart_subtotal;
			$cart['shipping_weight']   = $shipping_weight;
		}
		return $cart;
	}

	public static function get_order_summary($order){
		$cart = array();
		$cart['products']   = array();
		$cart['categories'] = array();
		$cart['tags'] 		= array();
		$cart['variations'] = array();
		$cart['shipping_class'] = array();
		$cart['product_type'] = array();
		$shipping_weight = 0;

		if($order){
			$items = $order->get_items();
			$order_total = $order->get_total();
			$order_subtotal = $order->get_subtotal();
			foreach($items as $item) {
				$product_id = $item->get_product_id();
				if(!$product_id){
					continue;
				}

				$cart['products'][] = self::get_original_product_id($product_id);
				$cart['categories'] = array_merge( $cart['categories'], self::get_product_categories($product_id) );
				$cart['tags']       = array_merge( $cart['tags'], self::get_product_tags($product_id) );
				$cart['shipping_class'] = array_merge($cart['shipping_class'] ,self::get_product_shipping_class($product_id));
				$cart['product_type'][] = WC_Product_Factory::get_product_type($product_id);

				if($item->get_variation_id()){
					$cart['variations'][] = $item->get_variation_id();
					$cart['products'][] = self::get_original_product_id($item->get_variation_id());
				}
				$product_qty = $item->get_quantity();
				$_product = wc_get_product( $product_id );
				$weight = $_product->get_weight();
				if(!empty($weight) && is_numeric($weight)){
					$shipping_weight += $weight * $product_qty;
				}
			}

			$cart['products']   = array_values($cart['products']);
			$cart['categories'] = apply_filters('thwcfe_cart_product_categories', array_values($cart['categories']));
			$cart['tags'] 		= apply_filters('thwcfe_cart_product_tags', array_values($cart['tags']));
			$cart['variations']    = array_values($cart['variations']);
			$cart['shipping_class'] = array_values($cart['shipping_class']);
			$cart['product_type']  = array_values($cart['product_type']);
			$cart['cart_total']    = $order_total;
			$cart['cart_subtotal'] = $order_subtotal;
			$cart['shipping_weight']   = $shipping_weight;
		}
		return $cart;
	}

	/*public static function get_product_categories($product_id){
		$categories = array();
		$assigned_categories = wp_get_post_terms($product_id, 'product_cat');

		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_category', false);
		$is_wpml_active = THWCFE_Utils::is_wpml_active();
		if($is_wpml_active && $ignore_translation){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;
			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
		}

		foreach($assigned_categories as $category){
			$parent_categories = get_ancestors( $category->term_id, 'product_cat' );
			if(is_array($parent_categories)){
				foreach($parent_categories as $pcat_id){
					$pcat = get_term( $pcat_id, 'product_cat' );
					$categories[] = $pcat->slug;
				}
			}

			$cat_slug = $category->slug;
			if($is_wpml_active && $ignore_translation){
				$default_cat_id = icl_object_id($category->term_id, 'category', true, $default_lang);
				$default_cat = get_term($default_cat_id);
				$cat_slug = $default_cat->slug;
			}
			$categories[] = $cat_slug;
		}

		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}

		return $categories;
	}*/

	public static function form_field_file_html( $key, $args, $value ) {
		$fname = esc_attr($key);

		$value_json = esc_attr($value);
		
		$hinput_class = array();
		$hinput_class[] = 'thwcfe-input-field';

		$input_class = isset($args['input_class']) ? $args['input_class'] : array();
		$input_class = is_array($input_class) ? $input_class : array();
		
		if(($ckey = array_search('thwcfe-input-field', $input_class)) !== false){
		    unset($input_class[$ckey]);
		}

		$field = '';
		$custom_file_field_attr = ' multiple';

		$file_name = array();

		if($value){
			$value = str_replace('\\','\\\\',$value);
			$value_arr = json_decode($value, true);
			//$value = is_array($value_arr) && isset($value_arr['name']) ? $value_arr['name'] : '';

			if(is_array($value_arr)){
				$file_name = wp_list_pluck($value_arr, 'name');
				//$custom_file_field_attr = 'style="display:none;"';
			}
		}

		$file_upload_btn_text = 'Upload a File';
		$file_upload_btn_text = apply_filters('thwcfe_change_file_upload_btn_text', $file_upload_btn_text, $key);
		
		$field .= '<input type="hidden" class="thwcfe-checkout-file-value input-text '.esc_attr(implode(' ', $hinput_class)) .'" name="'.esc_attr($key).'" id="'.$fname.'" value="'.$value_json.'" ';
		$field .= 'data-file-name="'.implode(", ",$file_name).'"';
		$field .= 'data-nonce="'. wp_create_nonce( 'file_handling_nonce' ) .'" />';
		if(isset($args['custom_btn_file_upload']) && $args['custom_btn_file_upload']){
			$field .= '<span class="upload-btn-wrapper"><button class="button thwcfe-btn-file-upload">'.__($file_upload_btn_text, 'woocommerce-checkout-field-editor-pro').'</button><input type="' . esc_attr( $args['type'] ) . '"  class="thwcfe-checkout-file thwcfe-checkout-file-btn '.esc_attr(implode(' ', $input_class)) .'" name="'. esc_attr($key) .'_file" id="'. esc_attr($args['id']) .'_file" placeholder="' . esc_attr($args['placeholder']) . '" value="" '.$custom_file_field_attr.' /></span>';
		}else{
			$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="thwcfe-checkout-file '.esc_attr(implode(' ', $input_class)) .'" name="'. esc_attr($key) .'_file" ';
		$field .= 'id="'. $fname .'_file" placeholder="' . esc_attr($args['placeholder']) . '" value="" '.$custom_file_field_attr.' />';
		}
		return $field;
	}

	public static function prepare_file_preview_html($value, $hyperlink=true){
		$prev_html = '';

		$uploaded = false;
		if(is_string($value) && !empty($value)){
			$value = str_replace('\\','\\\\',$value);
			$uploaded = json_decode($value, true);
		}

		if(is_array($uploaded)){
			$name = isset($uploaded['name']) ? $uploaded['name'] : '';
			$size = isset($uploaded['size']) ? $uploaded['size'] : '';
			$type = isset($uploaded['type']) ? $uploaded['type'] : '';
			$url  = isset($uploaded['url']) ? $uploaded['url'] : '';

			$size = THWCFE_Utils::convert_bytes_to_kb($size);
			$disp_name = WCFE_Checkout_Fields_Utils::get_file_display_name($uploaded, $hyperlink); 
			
			if($disp_name){
				$prev_html .= '<span class="thwcfe-uloaded-file-list"><span class="thwcfe-uloaded-file-list-item">';
				$prev_html .= '<span class="thwcfe-columns">';
				
				if(in_array($type, THWCFE_Utils::$IMG_FILE_TYPES)){
					$prev_html .= '<span class="thwcfe-column-thumbnail">';
					$prev_html .= '<a href="'.$url.'" target="_blank"><img src="'. $url .'" ></a>';
					$prev_html .= '</span>';
				}

				$prev_html .= '<span class="thwcfe-column-title">';
				$prev_html .= '<span title="'.$name.'" class="title"><a href="'.$url.'" target="_blank">'.$disp_name.'</a></span>';
				if($size){
					$prev_html .= '<span class="size">'.$size.'</span>';
				}
				$prev_html .= '</span>';

				$prev_html .= '<span class="thwcfe-column-actions">';
				$prev_html .= '<a href="#" onclick="thwcfeRemoveUploaded(this, event); return false;" class="thwcfe-action-btn thwcfe-remove-uploaded" title="Remove">X</a>';
				$prev_html .= '</span>';

				$prev_html .= '</span>';
				$prev_html .= '</span></span>';
			}
		}

		$display = $prev_html ? 'block' : 'none';

		$html  = '<span class="thwcfe-uloaded-files" style="display:'.$display.';">';
		$html .= '<span class="thwcfe-upload-preview" style="margin-right:15px;">'.$prev_html.'</span>';
		$html .= '</span>';
		$html .= '<span class="thwcfe-file-upload-status" style="display:none;"><img src="'.THWCFE_ASSETS_URL_PUBLIC.'css/loading.gif" style="width:32px;"/></span>';
		$html .= '<span class="thwcfe-file-upload-msg" style="display:none; color:red;"></span>';
		
		return $html;
	}

	public static function get_product_shipping_class($product_id){
		$shipping_class = array();
		$cart_product = wc_get_product($product_id);
		$shipping_class[] = $cart_product->get_shipping_class();
		return $shipping_class;
	}

	public static function get_product_categories($product_id){
		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_category', true);
		$categories = self::get_product_terms($product_id, 'category', 'product_cat', $ignore_translation);
		return $categories;
	}

	public static function get_product_tags($product_id){
		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_tag', true);
		$tags = self::get_product_terms($product_id, 'tag', 'product_tag', $ignore_translation);
		return $tags;
	}

	public static function get_product_terms($product_id, $type, $taxonomy, $ignore_translation=false){
		$terms = array();
		$assigned_terms = wp_get_post_terms($product_id, $taxonomy);
		
		$is_wpml_active = self::is_wpml_active();
		if($is_wpml_active && $ignore_translation){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;
			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
		}

		if(is_array($assigned_terms)){
			foreach($assigned_terms as $term){
				$parent_terms = get_ancestors($term->term_id, $taxonomy);
				if(is_array($parent_terms)){
					foreach($parent_terms as $pterm_id){
						$pterm = get_term($pterm_id, $taxonomy);
						$terms[] = $pterm->slug;
					}
				}

				$term_slug = $term->slug;
				if($is_wpml_active && $ignore_translation){
					$default_term = self::get_default_lang_term($term, $taxonomy, $default_lang);
					$term_slug = $default_term->slug;
				}
				$terms[] = $term_slug;
			}
		}

		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}

		if($type === 'tag' && empty($terms)){
			$terms[] = "undefined";
		}

		return $terms;
	}

	public static function load_products_cat($only_slug=false, $ignore_translation=true){
		$product_cats = self::load_product_terms('category', 'product_cat', $only_slug);
		return $product_cats;
	}

	public static function load_product_tags($only_slug=false, $ignore_translation=true){
		$product_tags = self::load_product_terms('tag', 'product_tag', $only_slug);
		return $product_tags;
	}

	public static function load_product_terms($type, $taxonomy, $only_slug=false, $ignore_translation=true){
		$terms  = array();
		$pterms = get_terms($taxonomy, 'orderby=count&hide_empty=0');

		$is_wpml_active = self::is_wpml_active();
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
					$terms[] = $dterm->slug;
				}else{
					$terms[] = array("id" => $dterm->slug, "title" => $dterm->name);
				}
			}
		}

		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}

		return $terms;
	}

	public static function get_default_lang_term($term, $taxonomy, $default_lang){
		$dterm_id = icl_object_id($term->term_id, $taxonomy, true, $default_lang);
		$dterm = get_term($dterm_id);
		return $dterm;
	}

	/*public static function get_product_categories($product_id){
		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_category', false);
		$categories = self::get_product_terms($product_id, 'category', 'product_cat', $ignore_translation);
		return $categories;
	}

	public static function get_product_tags($product_id){
		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_tag', false);
		$tags = self::get_product_terms($product_id, 'tag', 'product_tag', $ignore_translation);
		return $tags;
	}

	public static function get_product_terms($product_id, $type, $taxonomy, $ignore_translation=false){
		$terms = array();
		$assigned_terms = wp_get_post_terms($product_id, $taxonomy);

		$is_wpml_active = self::is_wpml_active();
		if($is_wpml_active && $ignore_translation){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;
			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
		}

		foreach($assigned_terms as $term){
			$parent_terms = get_ancestors($term->term_id, $taxonomy);
			if(is_array($parent_terms)){
				foreach($parent_terms as $pterm_id){
					$pterm = get_term($pterm_id, $taxonomy);
					$terms[] = $pterm->slug;
				}
			}

			$term_slug = $term->slug;
			if($is_wpml_active && $ignore_translation){
				$default_term_id = icl_object_id($term->term_id, $type, true, $default_lang);
				$default_term = get_term($default_term_id);
				$term_slug = $default_term->slug;
			}
			$terms[] = $term_slug;
		}

		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}

		return $terms;
	}*/

	public static function get_user_roles($user = false) {
		$user = $user ? new WP_User( $user ) : wp_get_current_user();

		if(!($user instanceof WP_User))
		   return false;

		$roles = $user->roles;
		return $roles;
	}

	public static function get_original_product_id($product_id){
		$is_wpml_active = self::is_wpml_active();
		//$ignore_translation = true;

		if($is_wpml_active){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;

			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();

			$product_id = icl_object_id($product_id, 'product', true, $default_lang);

			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}
		return $product_id;
	}

	public static function get_product_tax_class_options() {
		if(self::woo_version_check()){
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

	public static function get_option_text_from_value($field, $value){
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
	}

	//TODO check for any better approach.
	/*public static function get_product_categories($product){
		$categories = array();
		if($product->get_id()){
			$product_cat = wp_get_post_terms($product->get_id(), 'product_cat');
			if(is_array($product_cat)){
				foreach($product_cat as $category){
					$parent_cat = get_ancestors( $category->term_id, 'product_cat' );
					if(is_array($parent_cat)){
						foreach($parent_cat as $pcat_id){
							$pcat = get_term( $pcat_id, 'product_cat' );
							$categories[] = $pcat->slug;
						}
					}
					$categories[] = $category->slug;
				}
			}
		}
		return $categories;
	}*/
	/**********************************************
	********* COMMON UTIL FUNCTIONS - END *********
	**********************************************/


	/****************************************************
	********* ADVANCED SETTINGS FUNCTIONS - START *******
	****************************************************/
	public static function get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}

	public static function get_settings($key){
		$settings = self::get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

	public static function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	/****************************************************
	********* ADVANCED SETTINGS FUNCTIONS - END *********
	****************************************************/


	/**************************************************
	********* CUSTOM SECTIONS & FIELDS - START ********
	**************************************************/
	public static function get_section_hook_map(){
		$section_hook_map = get_option(self::OPTION_KEY_SECTION_HOOK_MAP);
		$section_hook_map = is_array($section_hook_map) ? $section_hook_map : array();
		return $section_hook_map;
	}

	public static function get_custom_sections(){
		$sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		return empty($sections) ? false : $sections;
	}

	public static function get_hooked_sections($hook_name){
		$sections = false;
		$section_hook_map = self::get_section_hook_map();

		if(is_array($section_hook_map) && isset($section_hook_map[$hook_name])){
			$sections = $section_hook_map[$hook_name];
		}

		return empty($sections) ? false : $sections;
	}

	public static function get_checkout_section($section_name, $cart_info=false){
	 	if(isset($section_name) && !empty($section_name)){
			$sections = self::get_custom_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];
				if(THWCFE_Utils_Section::is_valid_section($section) && THWCFE_Utils_Section::is_show_section($section, $cart_info)){
					return $section;
				}
			}
		}
		return false;
	}

	public static function get_fieldset_to_show($section){
		$cart_info = self::get_cart_summary();
		$fieldset = THWCFE_Utils_Section::get_fieldset($section, $cart_info);
		return !empty($fieldset) ? $fieldset : false;
	}

	public static function get_checkout_fields_full($return_fieldset=false){
		$fields = array();
		$sections = self::get_custom_sections();
		if($sections){
			$sections = self::sort_sections($sections);
			foreach($sections as $sname => $section){
				$temp_fields = false;

				if($return_fieldset){
					$temp_fields = THWCFE_Utils_Section::get_fieldset($section);
				}else{
					$temp_fields = THWCFE_Utils_Section::get_fields($section);
				}

				if($temp_fields && is_array($temp_fields)){
					$fields = array_merge($fields, $temp_fields);
				}
			}
		}
		return $fields;
	}

	public static function exclude_address_fields($fields){
		$billing_keys  = self::get_settings('custom_billing_address_keys');
		$shipping_keys = self::get_settings('custom_shipping_address_keys');

		$address_fields = $billing_keys && is_array($billing_keys) ? $billing_keys : array();
		$address_fields = $shipping_keys && is_array($shipping_keys) ? array_merge($address_fields, $shipping_keys) : $address_fields;

		if(is_array($fields) && !empty($fields) && $address_fields && is_array($address_fields)){
			foreach($address_fields as $key) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}

	public static function preare_fee_name($name, $label, $value, $fee_labels=false){
		if($label && $value && apply_filters('thwcfe_display_value_with_fee_label', true, $name)){
			$label .= ' ('.$value.')';
		}

		if(is_array($fee_labels) && in_array($label, $fee_labels)){
			$label = $name.'_'.$label;
		}

		$label = apply_filters('thwcfe_fee_label', $label, $name);
		return $label;
	}

	public static function is_ship_to_billing($wc_order){
		$order_id = self::get_order_id($wc_order);
		$order = wc_get_order( $order_id );
		// $shipp_to_billing = get_post_meta($order_id, '_thwcfe_ship_to_billing', true);
		$shipp_to_billing = $order->get_meta( '_thwcfe_ship_to_billing', true  );
		return $shipp_to_billing;
	}

	/*public static function get_checkout_section($section_name){
	 	if(isset($section_name) && !empty($section_name)){
			$sections = self::get_custom_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];
				if(THWCFE_Utils_Section::is_valid_section($section)){
					return $section;
				}
			}
		}
		return false;
	}*/

	/*** FIELD FUNCTIONS ***/

	/*public static function get_fieldset_all($section, $exclude_disabled = true){
		$fieldset = array();
		if(THWCFE_Utils_Section::is_valid_enabled_section($section)){
			$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, $exclude_disabled);
		}
		return !empty($fieldset) ? $fieldset : false;
	}*/

	/*public static function get_fieldset($section, $cart = false, $ignore_conditions = false){
		$fieldset = array();
		if(THWCFE_Utils_Section::is_valid_enabled_section($section)){
			if($ignore_conditions){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);
			}else if(!$cart){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, false, false);
			}else{
				$products   = $cart['products'];
				$categories = $cart['categories'];
				$variations = $cart['variations'];

				$fieldset = THWCFE_Utils_Section::get_fieldset($section, $products, $categories, $variations);
			}
		}

		return !empty($fieldset) ? $fieldset : false;
	}*/

	public static function is_wc_handle_custom_field($field){

		if (is_object($field)) {
			$field = json_decode(json_encode($field), true);
		}

		$name = isset($field['name']) ? $field['name'] : '';
		$special_fields = array();
		
		if(version_compare(THWCFE_Utils::get_wc_version(), '5.6.0', ">=")){
			$special_fields[] = 'shipping_phone';
		}

		$special_fields = apply_filters('thwcfd_wc_handle_custom_field', $special_fields);

		if($name && in_array($name, $special_fields)){
			return true;
		}
		return false;
	}

	/**************************************************
	********* CUSTOM SECTIONS & FIELDS - END **********
	**************************************************/

	public static function get_posted_value($posted, $key, $type='', $default=''){
		$value = '';

		if($type === 'select'){
			$value = isset($posted[$key]) ? sanitize_text_field($posted[$key]) : $default;
		}else if($type === 'checkbox'){
			$value = isset($posted[$key]) ? sanitize_text_field($posted[$key]) : $default;
		}else if($type === 'radio'){
			$value = isset($posted[$key]) ? sanitize_text_field($posted[$key]) : $default;
		}else if($type === 'textarea'){
			$value = isset($posted[$key]) ? sanitize_textarea_field($posted[$key]) : $default;
		}else{
			$value = isset($posted[$key]) ? sanitize_text_field($posted[$key]) : $default;
		}

		return $value;
	}

	/**************************************************
	********* FILE UPLOAD FUNCTIONS - START ***********
	**************************************************/
	public static function get_posted_file_type($file){
		$file_type = false;
		if($file && isset($file['name'])){
			//$file_type = isset($file['type']) ? $file['type'] : false;
			$file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
		}
		return $file_type;
	}

	public static function convert_bytes_to_kb($size){
		if(is_numeric($size)){
			$size = $size/1000;
			$size = round($size);
			$size = $size.' KB';
		}
		return $size;
	}
	/**************************************************
	********* FILE UPLOAD FUNCTIONS - END *************
	**************************************************/


	/**********************************************
	********* OTHER PLUGIN SUPPORT - START ********
	**********************************************/
	//WMSC Support TODO
	public static function get_hooked_sections_($sections, $hook_name){
		$section_hook_map = self::get_section_hook_map();

		if(is_array($section_hook_map) && isset($section_hook_map[$hook_name])){
			$sections = $section_hook_map[$hook_name];
		}

		return empty($sections) ? false : $sections;
	}

	public static function has_hooked_sections($result, $hook_name){
		$sections = array();
		$hooked_sections = self::get_hooked_sections($hook_name);
		$cart_info = self::get_cart_summary();

		if(is_array($hooked_sections)){
			foreach($hooked_sections as $key => $section_name){
				$section = self::get_checkout_section($section_name, $cart_info);
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, $cart_info);
				if($section && !empty($fieldset)){
					$sections[$section_name] = $section;
				}
			}
		}
		return empty($sections) ? false : $result;
	}

	/*
	 * To Access custom fields from outside the plugin.
	 * Added to the hook 'thwcfe_custom_checkout_fields'
	 */
	public static function get_custom_checkout_fields($ofields, $args=array()){
		$args = is_array($args) ? $args : array();
		$exc_addr_fields = isset($args['exclude_address_fields']) ? $args['exclude_address_fields'] : true;
		$display_fields_type = isset($args['display_fields_type']) ? $args['display_fields_type'] : false;

		$custom_fields = array();
		$fieldset = self::get_checkout_fields_full();

		if($exc_addr_fields){
			$fieldset = self::exclude_address_fields($fieldset);
		}

		foreach($fieldset as $key => $field) {
			if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field)){
				$show_field = true;
				/*if($sent_to_admin && $field->get_property('show_in_email')){
					$show_field = true;
				}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
					$show_field = true;
				}*/

				if($display_fields_type === 'user_meta' && !THWCFE_Utils_Field::is_user_field($field)){
					continue;
				}
				if($display_fields_type === 'order_meta' && !THWCFE_Utils_Field::is_order_field($field)){
					continue;
				}

				if($show_field){
					$label = $field->get_property('title') ? $field->get_property('title') : $key;
					if(apply_filters('thwcfe_esc_attr_custom_field_label_email', false)){
						$label = THWCFE_i18n::esc_attr__t($label);
					}else{
						$label = THWCFE_i18n::t($label);
					}

					$custom_field = array();
					$custom_field['label'] = $label;

					$custom_fields[$key] = $custom_field;
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}

	/*
	 * To Access custom fields and values from outside the plugin.
	 * Added to the hook 'thwcfe_custom_checkout_fields_and_values'
	 */
	public static function get_custom_checkout_fields_and_values($ofields, $order_id, $args=array()){
		$args = is_array($args) ? $args : array();
		$exc_addr_fields = isset($args['exclude_address_fields']) ? $args['exclude_address_fields'] : true;
		$display_fields_type = isset($args['display_fields_type']) ? $args['display_fields_type'] : false;

		$custom_fields = array();
		$fieldset = self::get_checkout_fields_full();
		$order = wc_get_order( $order_id );

		if($exc_addr_fields){
			$fieldset = self::exclude_address_fields($fieldset);
		}

		$is_nl2br = apply_filters('thwcfe_nl2br_custom_field_value', true);

		foreach($fieldset as $key => $field) {
			if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field)){
				$show_field = true;
				/*if($sent_to_admin && $field->get_property('show_in_email')){
					$show_field = true;
				}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
					$show_field = true;
				}*/

				if($display_fields_type === 'user_meta' && !THWCFE_Utils_Field::is_user_field($field)){
					continue;
				}
				if($display_fields_type === 'order_meta' && !THWCFE_Utils_Field::is_order_field($field)){
					continue;
				}

				if($show_field){
					$type = $field->get_property('type');
					// $value = get_post_meta($order_id, $key, true);
					$value = $order->get_meta( $key, true );

					if(!empty($value)){
						$value = self::get_option_text_from_value($field, $value);
						$value = is_array($value) ? implode(", ", $value) : $value;

						$label = $field->get_property('title') ? $field->get_property('title') : $key;
						if(apply_filters('thwcfe_esc_attr_custom_field_label_email', false)){
							$label = THWCFE_i18n::esc_attr__t($label);
						}else{
							$label = THWCFE_i18n::t($label);
						}

						if($is_nl2br && $type === 'textarea'){
							$value = nl2br($value);
						}

						$custom_field = array();
						$custom_field['label'] = $label;
						$custom_field['value'] = $value;

						$custom_fields[$key] = $custom_field;
					}
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}
	/**********************************************
	********* OTHER PLUGIN SUPPORT - END ********
	**********************************************/

	public static function get_order_id($order){
		$order_id = false;
		if(self::woo_version_check()){
			$order_id = $order->get_id();
		}else{
			$order_id = $order->id;
		}
		return $order_id;
	}

	public static function get_product_id($product){
		$product_id = false;
		if(self::woo_version_check()){
			$product_id = $product->get_id();
		}else{
			$product_id = $product->id;
		}
		return $product_id;
	}

	public static function get_product_type($product){
		$product_type = false;
		if(self::woo_version_check()){
			$product_type = $product->get_type();
		}else{
			$product_type = $product->product_type;
		}
		return $product_type;
	}

	public static function delete_item_by_value($arr, $value){
		if(is_array($arr) && ($key = array_search($value, $arr)) !== false) {
			unset($arr[$key]);
		}
		return $arr;
	}

	public static function get_jquery_date_format($woo_date_format){
		$woo_date_format = !empty($woo_date_format) ? $woo_date_format : wc_date_format();
		return preg_replace(self::$PATTERN, self::$REPLACE, $woo_date_format);
	}

	public static function convert_cssclass_string($cssclass){
		if(!is_array($cssclass)){
			$cssclass = array_map('trim', explode(',', $cssclass));
		}

		if(is_array($cssclass)){
			$cssclass = implode(" ",$cssclass);
		}
		return $cssclass;
	}

	public static function convert_string_to_array($str, $separator = ','){
		if(!is_array($str)){
			$str = array_map('trim', explode($separator, $str));
		}
		return $str;
	}

	public static function is_subset_of($arr1, $arr2){
		if(is_array($arr1) && is_array($arr2)){
			foreach($arr2 as $value){
				if(!in_array($value, $arr1)){
					return false;
				}
			}
		}
		return true;
	}

	public static function remove_by_value($value, $arr){
		if(is_array($arr)){
			foreach (array_keys($arr, $value, true) as $key) {
			    unset($arr[$key]);
			}
		}
		return $arr;
	}

	public static function is_blank($value) {
		return empty($value) && !is_numeric($value);
	}

	public static function extract_query_string_params($query_string){
		$qparams = array();

		if(is_string($query_string) && !empty($query_string)){
			$data = urldecode($query_string);
			$params = is_string($data) ? explode("&", $data) : array();

			foreach($params as $param) {
				$param_data  = is_string($param) ? explode("=", $param) : array();
				$param_key   = isset($param_data[0]) ? rtrim($param_data[0], "[]") : false;
				$param_value = isset($param_data[1]) ? $param_data[1] : '';

				if($param_key && $param_value){
					if(isset($qparams[$param_key]) && !empty($qparams[$param_key])){
						$qparams[$param_key] .= ','.$param_value;
					}else{
						$qparams[$param_key] = $param_value;
					}
				}
			}
		}
		return $qparams;
	}

	/*public static function get_value_from_query_string($query_string, $key) {
		$value = false;

		if(is_string($query_string) && is_string($key)){
			$data = urldecode($query_string);
			$params = is_string($data) ? explode("&", $data) : array();

			foreach($params as $param) {
				$param_data = is_string($param) ? explode("=", $param) : array();
				$param_key  = isset($param_data[0]) ? rtrim($param_data[0], "[]") : false;

				if($param_key === $key){
					if(is_string($value) && !empty($value)){
						$temp_value = isset($param_data[1]) ? $param_data[1] : '';
						if(is_string($temp_value) && !empty($temp_value)){
							$value .= ','.$temp_value;
						}
					}else{
						$value = isset($param_data[1]) ? $param_data[1] : '';
					}
				}
			}
		}
		return $value;
	}*/

	public static function sort_sections(&$sections){
		if(is_array($sections) && !empty($sections)){
			self::stable_uasort($sections, array('THWCFE_Utils', 'sort_sections_by_order'));
		}
		return $sections;
	}

	public static function sort_sections_by_order($a, $b){
		if(THWCFE_Utils_Section::is_valid_section($a) && THWCFE_Utils_Section::is_valid_section($b)){
			$order_a = is_numeric($a->get_property('order')) ? $a->get_property('order') : 0;
			$order_b = is_numeric($b->get_property('order')) ? $b->get_property('order') : 0;
			$order_a = (int)$order_a;
			$order_b = (int)$order_b;

			if($order_a == $order_b){
				return 0;
			}
			return ($order_a < $order_b) ? -1 : 1;
		}else{
			return 0;
		}
	}

	public static function stable_uasort(&$array, $cmp_function) {
		if(count($array) < 2) {
			return;
		}

		$halfway = count($array) / 2;
		$array1 = array_slice($array, 0, $halfway, TRUE);
		$array2 = array_slice($array, $halfway, NULL, TRUE);

		self::stable_uasort($array1, $cmp_function);
		self::stable_uasort($array2, $cmp_function);
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

	public static function is_wpml_active(){
		global $sitepress;
		return function_exists('icl_object_id') && is_object($sitepress);
		//return function_exists('icl_object_id');
	}

	public static function is_thwmsc_enabled(){
		$enabled = false;
		if(self::is_thwmsc_active()){
			if(class_exists('THWMSC_Utils') && method_exists('THWMSC_Utils', 'is_wmsc_enabled')){
				$enabled = THWMSC_Utils::is_wmsc_enabled();
			}
		}
		return apply_filters('thwcfe_is_thwmsc_enabled', $enabled);
	}

	public static function is_thwmsc_active(){
		$active = is_plugin_active('woocommerce-multistep-checkout/woocommerce-multistep-checkout.php');
		return apply_filters('thwcfe_is_thwmsc_active', $active);
	}

	public static function woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	public static function get_wc_version() {
		if(!class_exists('WooCommerce')){
		    return;
		}

		if(defined('WC_VERSION')) {
		    return WC_VERSION;
		}
		return;
	}	

	public static function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

endif;
