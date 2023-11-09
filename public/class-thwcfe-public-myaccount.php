<?php
/**
 * The public-facing functionality of the plugin - My Account.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Public_MyAccount')):

class THWCFE_Public_MyAccount extends THWCFE_Public {

	public function __construct( $plugin_name, $version ) {
		parent::__construct($plugin_name, $version);

		add_action('after_setup_theme', array($this, 'define_public_hooks'));
	}

	public function enqueue_styles_and_scripts() {
		global $wp_scripts;

		if( is_wc_endpoint_url('edit-account') || is_wc_endpoint_url('edit-address') || apply_filters('thwcfe_enqueue_public_myaccount', false)){
			$debug_mode = apply_filters('thwcfe_debug_mode', false);
			$in_footer  = apply_filters('thwcfe_enqueue_script_in_footer', true);

			$suffix = $debug_mode ? '' : '.min';
			$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			$this->enqueue_styles($suffix, $jquery_version, $in_footer);
			$this->enqueue_scripts($suffix, $jquery_version, $in_footer);
		}
	}

	private function enqueue_styles($suffix, $jquery_version, $in_footer) {
		wp_enqueue_style('thwcfe-timepicker-style', THWCFE_ASSETS_URL_PUBLIC.'js/timepicker/jquery.timepicker.css');
		wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'. $jquery_version .'/themes/smoothness/jquery-ui.css');

		wp_enqueue_style('thwcfe-public-myaccount-style', THWCFE_ASSETS_URL_PUBLIC . 'css/thwcfe-public'. $suffix .'.css', $this->version);
	}

	private function enqueue_scripts($suffix, $jquery_version, $in_footer) {
		wp_register_script('thwcfe-timepicker-script', THWCFE_ASSETS_URL_PUBLIC.'js/timepicker/jquery.timepicker.min.js', array('jquery'), '1.0.1', $in_footer);
		wp_register_script('thwcfe-input-mask', THWCFE_ASSETS_URL_PUBLIC.'js/inputmask-js/jquery.inputmask.min.js', array('jquery'), '5.0.6',$in_footer);


		$deps = array();
		if( apply_filters( 'thwcfe_include_jquery_ui_i18n', TRUE ) ) {
			//wp_register_script('jquery-ui-i18n', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_version.'/i18n/jquery-ui-i18n.min.js', array('jquery','jquery-ui-datepicker'), $in_footer);
			wp_register_script('jquery-ui-i18n', THWCFE_ASSETS_URL_PUBLIC.'js/jquery-ui-i18n.min.js', array('jquery','jquery-ui-datepicker'), $in_footer);

			$deps[] = 'jquery-ui-i18n';
		}else{
			$deps[] = 'jquery';
			$deps[] = 'jquery-ui-datepicker';
		}

		if(THWCFE_Utils::get_settings('enable_autofill') == 'yes') {
			$api_key = THWCFE_Utils::get_settings('autofill_apikey');
			if($api_key) {
				wp_enqueue_script('google-autocomplete', 'https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key='.$api_key);
			}
		}

		if(THWCFE_Utils::get_settings('disable_select2_for_select_fields') != 'yes'){
			//$deps[] = 'select2';
			$deps[] = 'selectWoo';

			$select2_languages = apply_filters( 'thwcfe_select2_i18n_languages', false);
			if(is_array($select2_languages)){
				foreach($select2_languages as $lang){
					$handle = 'select2_i18n_'.$lang;
					wp_register_script($handle, '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/i18n/'.$lang.'.js', array('jquery','select2'));
					$deps[] = $handle;
				}
			}
		}

		$deps = apply_filters('thwcfe_public_script_deps', $deps);

		wp_register_script('thwcfe-public-myaccount-script', THWCFE_ASSETS_URL_PUBLIC.'js/thwcfe-public-myaccount'. $suffix .'.js', $deps, THWCFE_VERSION, $in_footer);

		if(apply_filters('thwcfe_force_register_date_picker_script', false)){
			wp_register_script('thwcfe-datepicker-script', 'https://code.jquery.com/ui/'.$jquery_version.'/jquery-ui.js', array('jquery'), '1.0.1', $in_footer);
			wp_enqueue_script('thwcfe-datepicker-script');
		}

		wp_enqueue_script('thwcfe-input-mask');
		wp_enqueue_script('thwcfe-timepicker-script');
		wp_enqueue_script('thwcfe-public-myaccount-script');

		$billing_address_autofill = array('billing_address_1' => 'billing_address_1', 'billing_city' => 'billing_city','billing_country' => 'billing_country', 'billing_postcode' => 'billing_postcode','billing_state' => 'billing_state');
		$shipping_address_autofill = array('shipping_address_1' => 'shipping_address_1', 'shipping_city' => 'shipping_city','shipping_country' => 'shipping_country', 'shipping_postcode' => 'shipping_postcode','shipping_state' => 'shipping_state');
		$force_wp_server_time = apply_filters('thwcfe_force_wp_date_time_for_date_picker', false);
		$billing_address_autofill = apply_filters('thwcfe_billing_address_autofill',$billing_address_autofill);
		$shipping_address_autofill = apply_filters('thwcfe_shipping_address_autofill',$shipping_address_autofill);
		// $enabled_country_code_fields = array('billing_phone');
		// $enabled_country_code_fields = apply_filters('thwcfe_enable_country_code_tel_field', $enabled_country_code_fields);

		$wcfe_var = array(
			'lang' => array(
						'am' => __('am','woocommerce-checkout-field-editor-pro'),
						'pm' => __('pm', 'woocommerce-checkout-field-editor-pro'),
						'AM' => __('AM', 'woocommerce-checkout-field-editor-pro'),
						'PM' => __('PM', 'woocommerce-checkout-field-editor-pro'),
						'decimal' => __('.','woocommerce-checkout-field-editor-pro'),
						'mins' => __('mins','woocommerce-checkout-field-editor-pro'),
						'hr'   => __('hr','woocommerce-checkout-field-editor-pro'),
						'hrs'  => __('hrs', 'woocommerce-checkout-field-editor-pro'),
					),
			'language' 	  => THWCFE_i18n::get_locale_code(),
			'is_override_required' => $this->is_override_required_prop(),
			'date_format' => THWCFE_Utils::get_jquery_date_format(wc_date_format()),
			'dp_show_button_panel' => apply_filters('thwcfe_date_picker_show_button_panel', true),
			'dp_change_month' => apply_filters('thwcfe_date_picker_change_month', true),
			'dp_change_year' => apply_filters('thwcfe_date_picker_change_year', true),
			'dp_prevent_close_onselect' => $this->get_dp_prevent_close_onselect(),
			'readonly_date_field' => apply_filters('thwcfe_date_picker_field_readonly', true),
			'notranslate_dp' => apply_filters('thwcfe_date_picker_notranslate', true),
			'restrict_time_slots_for_same_day' => apply_filters( 'thwcfe_time_picker_restrict_slots_for_same_day', true ),
			'change_event_disabled_fields' => apply_filters('thwcfe_change_event_disabled_fields', ''),
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'user_id' => get_current_user_id(),
            'force_server_time_for_date_picker' => $force_wp_server_time,
            'wp_max_upload_size' => wp_max_upload_size(),
            'wp_max_upload_size_warning' => __('Maximum upload size exceeded', 'woocommerce-checkout-field-editor-pro'),
            'enable_autofill' 	=> THWCFE_Utils::get_settings('enable_autofill'),
            'billing_address_autofill' => $billing_address_autofill,
            'shipping_address_autofill' => $shipping_address_autofill,
            // 'enabled_country_code_fields' => $enabled_country_code_fields,
		);
        if($force_wp_server_time){
            $wp_now = current_datetime();
            $wcfe_var['wp_date_time'] = apply_filters('thwcfe_wp_current_date_time', $wp_now->format('Y-m-d\TH:i:s'));
        }		
		wp_localize_script('thwcfe-public-myaccount-script', 'thwcfe_public_var', $wcfe_var);
	}

	public function define_public_hooks(){
		parent::define_public_hooks();
		$hp_dis_fields = apply_filters('thwcfe_myaccount_address_fields_hook_priority', 1100);

		add_filter('woocommerce_my_account_my_address_formatted_address', array($this, 'woo_my_account_my_address_formatted_address'), 20, 3);
		add_action('woocommerce_edit_account_form_tag', array($this, 'woo_edit_account_form_tag'));

		// Added from 3.1.7.0
		$account_form_hook_name = apply_filters('thwcfe_account_form_hook_name','woocommerce_edit_account_form');
		add_action($account_form_hook_name, array($this, 'woo_edit_account_form'));

		add_filter('woocommerce_before_edit_address_form_billing', array($this, 'output_myaccount_form_hidden_fields'));
		add_filter('woocommerce_before_edit_address_form_shipping', array($this, 'output_myaccount_form_hidden_fields'));
		add_filter('woocommerce_address_to_edit', array($this, 'woo_address_to_edit'), 10, 2);

		add_filter('woocommerce_billing_fields', array($this, 'prepare_address_fields_before_validate'), $hp_dis_fields, 2);
		add_filter('woocommerce_shipping_fields', array($this, 'prepare_address_fields_before_validate'), $hp_dis_fields, 2);
		add_action('woocommerce_after_save_address_validation', array($this, 'override_address_field_validation'), $hp_dis_fields, 4);


		add_filter('woocommerce_save_account_details_required_fields', array($this, 'woo_save_account_details_required_fields'));
		add_action('woocommerce_save_account_details_errors', array($this, 'woo_save_account_details_validation'), 10, 2);
		add_action('woocommerce_after_save_address_validation', array($this, 'woo_save_address_fields_validation'), 10, 3);

		add_action('woocommerce_save_account_details', array($this, 'woo_save_account_details'));
		add_action('woocommerce_customer_save_address', array($this, 'woo_customer_save_address'), 10, 2);

		//Erase Personal Data
		add_filter('woocommerce_privacy_erase_personal_data_customer', array($this, 'woo_erase_personal_data_customer'), 10, 2);
		add_action('woocommerce_privacy_remove_order_personal_data', array($this, 'woo_remove_order_personal_data'));
	}

	public function output_myaccount_form_hidden_fields(){
		$this->output_disabled_field_names_hidden_field();
		$this->output_input_mask_unvalidated_hidden_field();
	}


	public function output_disabled_field_names_hidden_field(){
		echo '<input type="hidden" id="thwcfe_disabled_fields" name="thwcfe_disabled_fields" value=""/>';
		echo '<input type="hidden" id="thwcfe_disabled_sections" name="thwcfe_disabled_sections" value=""/>';
	}

	public function output_input_mask_unvalidated_hidden_field(){
		echo '<input type="hidden" id="thwcfe_unvalidated_fields" name="thwcfe_unvalidated_fields" value=""/>';
	}

	public function woo_address_to_edit($address, $load_address = 'billing'){
		$section = THWCFE_Utils::get_checkout_section($load_address);
		$fieldset = THWCFE_Utils_Section::get_fieldset($section);
		$active_fieldset = THWCFE_Utils_Section::get_fieldset($section, false, false);
		$display_hidden_as_text = apply_filters('thwcfe_myaccount_display_hidden_field_as_text_field', false);

		if($fieldset && is_array($fieldset)){
			foreach($fieldset as $key => $field) {
				if(isset($address[$key]) && isset($field['custom']) && $field['custom']){
					if(apply_filters('thwcfe_ignore_custom_fields_in_address_to_edit', false)) {
						unset($address[$key]);
					}else if(is_array($active_fieldset) && !array_key_exists($key, $active_fieldset)) {
						unset($address[$key]);
					}else{
						$ftype = isset($field['type']) ? $field['type'] : 'text';

						if($ftype === 'hidden' && $display_hidden_as_text){
							$address[$key]['type'] = 'text';
						}

						/*if($ftype === 'file'){
							$address[$key]['type'] = 'file_default';
						}*/

						if(apply_filters('thwcfe_edit_address_ignore_row_split', true)){
							if(isset($field['class']) && is_array($field['class'])){
								$field['class'] = THWCFE_Utils::delete_item_by_value($field['class'], 'form-row-first');
								$field['class'] = THWCFE_Utils::delete_item_by_value($field['class'], 'form-row-last');
								$field['class'][] = 'form-row-wide';
							}
						}

						if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
							$address[$key]['required'] = false;
							$address[$key]['validate'] = '';
						}

						if($ftype === 'label' || $ftype === 'heading' || $ftype === 'paragraph'){
							$show_in_my_account_page = isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'] ? 1 : 0;
							if(!$show_in_my_account_page){
								unset($address[$key]);
							}
						}else if(isset($field['user_meta']) && !$field['user_meta']){
							unset($address[$key]);
						}

						if($ftype === 'file' && !apply_filters('thwcfe_edit_address_form_enctype_multipart', true)) {
							unset($address[$key]);
						}

						if(isset($address[$key]) && !apply_filters('thwcfe_show_in_my_account_page', true, $key)) {
							unset($address[$key]);
						}
					}
				}
			}
		}
		return $address;
	}

	public function woo_edit_account_form_tag() {
		if(apply_filters('thwcfe_edit_account_enable_file_support', true)){
			echo 'enctype="multipart/form-data"';
		}
	}

	public function woo_edit_account_form() {
	  	$user_id = get_current_user_id();
	  	$user = get_userdata($user_id);
	  	$rn = false;

	  	if(!$user){
			return;
		}

		$display_hidden_as_text = apply_filters('thwcfe_myaccount_display_hidden_field_as_text_field', false);

		$sections = THWCFE_Utils::get_custom_sections();
		if($sections && is_array($sections)){
			$this->output_myaccount_form_hidden_fields();

			foreach($sections as $sname => $section) {
				if(THWCFE_Utils_Section::is_show_section($section)){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					$show_section = apply_filters('thwcfe_show_section_in_my_account_page', true, $sname);
					$has_user_fields = THWCFE_Utils_Section::has_user_fields($section, $fieldset);
					$show_section = $has_user_fields ? $show_section : false;

					if($fieldset && $sname != 'billing' && $sname != 'shipping' && $show_section){
						$show_section_title = $section->get_property('show_title_my_account');
						$show_section_title = apply_filters('thwcfe_show_section_title_in_my_account_page', $show_section_title, $sname);
						$wrap_with_div = THWCFE_Utils::get_settings('wrap_custom_sections_with_div');
						if($wrap_with_div != 'yes' && THWCFE_Utils_Section::has_ajax_rules($section)){
							$wrap_with_div = 'yes';
						}

						if($wrap_with_div === 'yes'){
							$css_class = $section->get_property('cssclass');
							$css_class = !empty($css_class) ? str_replace(" ", "", $css_class) : '';
							$css_class = !empty($css_class) ? str_replace(",", " ", $css_class) : '';

							$conditions_data = $this->prepare_ajax_conditions_data_section($section);
							if($conditions_data){
								$css_class .= empty($css_class) ? 'thwcfe-conditional-section' : ' thwcfe-conditional-section';
							}

							echo '<div class="thwcfe-checkout-section '. $css_class .' '. $section->get_property('name') .'" '.$conditions_data.'>';
						}

						if($show_section_title){
							echo THWCFE_Utils_Section::get_title_html($section, $rn);
						}

						foreach($fieldset as $key => $field) {
							if(isset($field['custom']) && $field['custom']){
								$ftype = isset($field['type']) ? $field['type'] : 'text';

								if(isset($field['user_meta']) && $field['user_meta']){
									$value = get_user_meta( $user_id, $key, true );
									$value = isset($_POST[$key]) ? $_POST[$key] : $value;
									$value = is_array($value) ? implode(",", $value) : $value;
									//$label = $this->get_field_display_name($field);

									if($ftype === 'hidden' && $display_hidden_as_text){
										$field['type'] = 'text';
									}

									/*if($ftype === 'file'){
										$field['type'] = 'file_default';
									}*/

									if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
										$field['required'] = false;
										$field['validate'] = '';
									}

									if(apply_filters('thwcfe_show_in_my_account_page', true, $key)) {
										woocommerce_form_field( $key, $field, $value );
									}

								}else if(($ftype === 'label' || $ftype === 'heading' || $ftype === 'paragraph') && (isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'])){
									woocommerce_form_field( $key, $field, false );
								}
							}
						}

						if($wrap_with_div === 'yes'){
							echo '</div>';
						}
					}
				}
			}
		}
	}


	public function prepare_address_fields_before_validate($fields, $country){
		if( is_wc_endpoint_url('edit-address') ){
			$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
			$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();

			// if($fields && is_array($fields) && is_array($dis_fields) && !empty($dis_fields) ){
			// 	foreach($dis_fields as $fname){
			// 		if(in_array($fname, $dis_fields)){
			// 			unset($fields[$fname]);
			// 		}
			// 	}
			// }

			foreach($fields as $key => &$field){
				if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
					$field['required'] = false;
					$field['validate'] = '';
				}

                // if(isset($field['name']) && (in_array($field['name'], $dis_fields))){
                //     $field['required'] = false;
                //     $field['validate'] = '';
                // }

				$show_field = apply_filters('thwcfe_show_in_my_account_page', true, $key);

				if(isset($field['custom']) && $field['custom']){
					$ftype = isset($field['type']) ? $field['type'] : 'text';

					if($ftype === 'label' || $ftype === 'heading' || $ftype === 'paragraph'){
						if(!(isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'])){
							$show_field = false;
						}
					}else if(isset($field['user_meta']) && !$field['user_meta']){
						$show_field = false;
					}

					if($ftype === 'file'){
						if(apply_filters('thwcfe_edit_address_form_enctype_multipart', true)){
							$field['required'] = $field['required'] ? true : false ;
						}else{
							$show_field = false;
						}
					}
				}
				if(!$show_field){
					unset($fields[$key]);
				}
			}
		}
		
		return $fields;
	}

	public function override_address_field_validation($user_id, $load_address, $address, $customer = false){
		$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
		$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
		if(empty($dis_fields)){
			return;
		}

		$wc_notices = wc_get_notices();
		$wc_errors = isset($wc_notices['error']) ? $wc_notices['error'] : null;
		if(!empty($wc_errors)){
			foreach($wc_errors as $key => $wc_error){
				$id = isset($wc_error['data']['id']) ? $wc_error['data']['id'] : false;
				if(in_array($id, $dis_fields)){
					unset($wc_errors[$key]);
				}
			}

			$wc_notices['error'] = $wc_errors;
			wc_set_notices( $wc_notices );
		}
	}	

	public function prepare_disabled_sections($posted){
		$disabled_sections = isset($posted['thwcfe_disabled_sections']) ? wc_clean($posted['thwcfe_disabled_sections']) : '';
		$dis_sections = $disabled_sections ? explode(",", $disabled_sections) : array();
		$dis_sections = array_unique($dis_sections);
		return $dis_sections;
	}

	public function prepare_disabled_fields($posted){
		$disabled_fields = isset($posted['thwcfe_disabled_fields']) ? wc_clean($posted['thwcfe_disabled_fields']) : '';
		$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
		$dis_fields = array_unique($dis_fields);
		return $dis_fields;
	}

	public function woo_save_account_details_required_fields($required_fields){
		$user_id = get_current_user_id();
		$user = get_userdata($user_id);

		if(!$user){
			return;
		}

		$sections = THWCFE_Utils::get_custom_sections();
		if($sections && is_array($sections)){
			$dis_sections = $this->prepare_disabled_sections($_POST);
			$dis_fields = $this->prepare_disabled_fields($_POST);

			foreach($sections as $sname => $section) {
				$show_section = THWCFE_Utils_Section::is_show_section($section);
				$show_section = apply_filters('thwcfe_show_section_in_my_account_page', $show_section, $sname);
				if($sname != 'billing' && $sname != 'shipping' && $show_section && !in_array($sname, $dis_sections)){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					if($fieldset && is_array($fieldset)){
						foreach($fieldset as $key => $field) {
							$type = isset($field['type']) ? $field['type'] : '';

							if($type != 'file'){
								if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
									&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
									if(!in_array($key, $dis_fields) && $field['required']){
										if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
											$required_fields[$key] = $field['title'];
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $required_fields;
	}

	public function woo_save_account_details_validation($errors, $user){
		if($errors && $user){
			$sections = THWCFE_Utils::get_custom_sections();
			if($sections && is_array($sections)){
				$dis_sections = $this->prepare_disabled_sections($_POST);
				$dis_fields = $this->prepare_disabled_fields($_POST);

				foreach($sections as $sname => $section) {
					$show_section = THWCFE_Utils_Section::is_show_section($section);
					$show_section = apply_filters('thwcfe_show_section_in_my_account_page', $show_section, $sname);
					if($sname != 'billing' && $sname != 'shipping' && $show_section && !in_array($sname, $dis_sections)){
						$fieldset = THWCFE_Utils_Section::get_fieldset($section);
						if($fieldset && is_array($fieldset)){
							foreach($fieldset as $key => $field) {
								if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
									&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
									if(!in_array($key, $dis_fields)){
										if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
											$this->validate_custom_my_account_field($field, $_POST, $errors);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function woo_save_address_fields_validation($user_id, $load_address, $address){
		if($user_id && $load_address){
			$sections = THWCFE_Utils::get_custom_sections();
			if($sections && is_array($sections) && isset($sections[$load_address])){
				$section = $sections[$load_address];
				$dis_fields = $this->prepare_disabled_fields($_POST);

				$show_section = apply_filters('thwcfe_show_section_in_my_account_page', true, $load_address);
				if($show_section){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					if($fieldset && is_array($fieldset)){
						foreach($fieldset as $key => $field) {
							if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
									&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
								$type = isset($field['type']) ? $field['type'] : 'text';

								/*if($type === 'file' && empty($_POST[$key])){
									$_POST[$key] = get_user_meta( $user_id, $key, true );
								}*/

								if(!in_array($key, $dis_fields)){
									if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
										$this->validate_custom_my_account_field($field, $_POST);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function woo_save_account_details( $user_id ) {
		$sections = THWCFE_Utils::get_custom_sections();
		
		foreach($sections as $sname => $section) {
			if($sname != 'billing' && $sname != 'shipping'){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, true);
				if($fieldset){
					foreach($fieldset as $key => $field) {
						if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']){
							$type = isset($field['type']) ? $field['type'] : 'text';
							if(isset($_POST[ $key ])){
								$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
							}else{
								$value = false;
							}
							if($type !== 'file'){
								$value = htmlentities( $value );
							}
							update_user_meta( $user_id, $key, $value);

						}
					}
				}
			}
		}
	}

	public function woo_customer_save_address( $user_id, $load_address ) {
		$sections = THWCFE_Utils::get_custom_sections();
		foreach($sections as $sname => $section) {
			if($sname === $load_address){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);

				if($fieldset){
					foreach($fieldset as $key => $field) {
						if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']){
							$type = isset($field['type']) ? $field['type'] : 'text';

							if($type === 'file'){
								$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
								update_user_meta( $user_id, $key, $value);

							}else if(isset($_POST[$key])){
								$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
								update_user_meta( $user_id, $key, htmlentities( $value ) );
							}
						}
					}
				}
			}
		}
	}

	//Erase Personal Data
	public function woo_erase_personal_data_customer($response, $customer){
		$custom_fields = $this->get_usermeta_enabled_custom_fields();
		$user_id = $customer->get_id();

		if(is_array($custom_fields) && !empty($custom_fields)){
			foreach ($custom_fields as $key => $field) {
				$updated = update_user_meta($user_id, $key, '');

				if($updated){
					$response['messages'][] = sprintf( __( 'Removed customer "%s"', 'woocommerce' ), $field );
				}
			}
		}

		return $response;
	}

	public function woo_remove_order_personal_data($order){
		// $order_id = $order->get_id();
		$custom_fields = $this->get_ordermeta_and_usermeta_enabled_custom_fields();

		if(is_array($custom_fields) && !empty($custom_fields)){
			foreach ($custom_fields as $key => $type) {
				// $value = get_post_meta($order_id, $key, true);
				$value = $order->get_meta( $key, true );
				$anon_value = function_exists( 'wp_privacy_anonymize_data' ) ? wp_privacy_anonymize_data( $type, $value ) : '';

				if(!empty($value)){
					// update_post_meta($order_id, $key, $anon_value);
					$order->update_meta_data($key, $anon_value);
				}
			}
			$order->save();
		}
	}

	private function get_usermeta_enabled_custom_fields(){
		$checkout_fields = WC()->checkout->checkout_fields;
		$user_fields = array();

		foreach($checkout_fields as $fieldset_key => $fieldset){
			foreach($fieldset as $key => $field) {
				if(isset($field['custom']) && $field['custom']){
					if(isset($field['user_meta']) && $field['user_meta']){
						$user_fields[$key] = $field['name'];
					}
				}
			}
		}

		return $user_fields;
	}

	private function get_ordermeta_and_usermeta_enabled_custom_fields(){
		$checkout_fields = WC()->checkout->checkout_fields;
		$user_fields = array();

		foreach($checkout_fields as $fieldset_key => $fieldset){
			foreach($fieldset as $key => $field) {
				if(isset($field['custom']) && $field['custom']){
					if(isset($field['user_meta']) && $field['user_meta'] && isset($field['order_meta']) && $field['order_meta']){
						$user_fields[$key] = $field['type'];
					}
				}
			}
		}

		return $user_fields;
	}

}

endif;
