<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Public_Checkout')):

class THWCFE_Public_Checkout extends THWCFE_Public {
	protected $session;

	public function __construct( $plugin_name, $version ) {
		parent::__construct($plugin_name, $version);

		$this->session = new THWCFE_Public_Session_Handler();

		/*$force_wp_session = apply_filters('thwcfe_force_wp_session', false);
		if($force_wp_session && !isset($_SESSION) && !defined('DOING_CRON')){
			session_start();
		}*/

		add_action('after_setup_theme', array($this, 'define_public_hooks'));
	}

	public function enqueue_styles_and_scripts() {
		global $wp_scripts;

		if(is_checkout() || apply_filters('thwcfe_force_enqueue_checkout_public_scripts', false)){
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
		wp_enqueue_style('jquery-ui-style', THWCFE_ASSETS_URL_PUBLIC . 'css/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css');
		wp_enqueue_style('thwcfe-public-checkout-style', THWCFE_ASSETS_URL_PUBLIC . 'css/thwcfe-public'. $suffix .'.css', $this->version);
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

		$api_key = THWCFE_Utils::get_settings('autofill_apikey');

		if(THWCFE_Utils::get_settings('enable_autofill') == 'yes') {
			
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

		wp_register_script('thwcfe-public-checkout-script', THWCFE_ASSETS_URL_PUBLIC.'js/thwcfe-public-checkout'. $suffix .'.js', $deps, THWCFE_VERSION, $in_footer);

		if(apply_filters('thwcfe_force_register_date_picker_script', false)){
			wp_register_script('thwcfe-datepicker-script', THWCFE_ASSETS_URL_PUBLIC.'js/jquery-ui.min.js', array('jquery'), THWCFE_VERSION, $in_footer);
			wp_enqueue_script('thwcfe-datepicker-script');
		}

		wp_enqueue_script('thwcfe-input-mask');
		wp_enqueue_script('thwcfe-timepicker-script');
		wp_enqueue_script('thwcfe-public-checkout-script');

		$enable_conditions_payment_shipping = THWCFE_Utils::get_settings('enable_conditions_payment_shipping') ? true : false;
		$enable_conditions_review_panel = THWCFE_Utils::get_settings('enable_conditions_review_panel') ? true : false;
		$enable_country_based_conditions = THWCFE_Utils::get_settings('enable_country_based_conditions') ? true : false;
		$enable_conditions_review_panel = $enable_conditions_review_panel || $enable_conditions_payment_shipping || $enable_country_based_conditions;
		$force_wp_server_time_date_picker = apply_filters('thwcfe_force_wp_date_time_for_date_picker', false);
		$force_wp_server_time_time_picker = apply_filters('thwcfe_force_wp_date_time_for_time_picker', false);
		$show_file_upload_button_after_upload = apply_filters('thwcfe_show_file_upload_after_upload', true);
		$use_custom_ship_to_different_address_css_selector = apply_filters('thwcfe_use_custom_ship_to_different_address_css_selector', false);
		$billing_address_autofill = array('billing_address_1' => 'billing_address_1', 'billing_city' => 'billing_city','billing_country' => 'billing_country', 'billing_postcode' => 'billing_postcode','billing_state' => 'billing_state');
		$shipping_address_autofill = array('shipping_address_1' => 'shipping_address_1', 'shipping_city' => 'shipping_city','shipping_country' => 'shipping_country', 'shipping_postcode' => 'shipping_postcode','shipping_state' => 'shipping_state');
		// $enabled_country_code_fields = array('billing_phone');
		$billing_address_autofill = apply_filters('thwcfe_billing_address_autofill',$billing_address_autofill);
		$shipping_address_autofill = apply_filters('thwcfe_shipping_address_autofill',$shipping_address_autofill);
		// $enabled_country_code_fields = apply_filters('thwcfe_enable_country_code_tel_field', $enabled_country_code_fields);
		$enable_inline_validations = THWCFE_Utils::get_settings('enable_inline_validations') ? true : false;
		$required_validation = apply_filters('thwcfe_required_validation_text', __('This field is mandatory', 'woocommerce-checkout-field-editor-pro'));
		$email_validation = apply_filters('thwcfe_email_validation_text', __('Invalid email format', 'woocommerce-checkout-field-editor-pro'));
		$phone_validation = apply_filters('thwcfe_phone_validation_text', __('Please enter the input in digits', 'woocommerce-checkout-field-editor-pro'));
		$url_validation = apply_filters('thwcfe_url_validation_text', __('Invalid url format', 'woocommerce-checkout-field-editor-pro'));
		$minlength_validation = apply_filters('thwcfe_minlength_validation_text', __('Text entered is less than the minimum length', 'woocommerce-checkout-field-editor-pro'));
		$number_validation = apply_filters('thwcfe_number_validation_text', __('Input value is not a number', 'woocommerce-checkout-field-editor-pro'));
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
			'language' => THWCFE_i18n::get_locale_code(),
			'is_override_required' => $this->is_override_required_prop(),
			'date_format' => THWCFE_Utils::get_jquery_date_format(wc_date_format()),
			'dp_show_button_panel' => apply_filters('thwcfe_date_picker_show_button_panel', true),
			'dp_change_month' => apply_filters('thwcfe_date_picker_change_month', true),
			'dp_change_year' => apply_filters('thwcfe_date_picker_change_year', true),
			'dp_prevent_close_onselect' => $this->get_dp_prevent_close_onselect(),
			'readonly_date_field' => apply_filters('thwcfe_date_picker_field_readonly', true),
			'notranslate_dp' => apply_filters('thwcfe_date_picker_notranslate', true),
			'restrict_time_slots_for_same_day' => apply_filters( 'thwcfe_time_picker_restrict_slots_for_same_day', true ),
			'rebind_all_cfields' => apply_filters( 'thwcfe_enable_conditions_based_on_review_panel_fields', $enable_conditions_review_panel ),
			'change_event_disabled_fields' => apply_filters('thwcfe_change_event_disabled_fields', ''),
			'extra_cost_legacy_call' => $this->use_extra_cost_calculation_legacy_call(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'user_id' => get_current_user_id(),
            'force_server_time_for_date_picker' => $force_wp_server_time_date_picker,
            'force_server_time_for_time_picker' => $force_wp_server_time_time_picker,
            'wp_max_upload_size' => wp_max_upload_size(),
            'wp_max_upload_size_warning' => __('Maximum upload size exceeded', 'woocommerce-checkout-field-editor-pro'),
            'use_custom_ship_to_different_address_css_selector' => $use_custom_ship_to_different_address_css_selector,
            'enable_autofill' 	=> THWCFE_Utils::get_settings('enable_autofill'),
            'billing_address_autofill' => $billing_address_autofill,
            'shipping_address_autofill' => $shipping_address_autofill,
            'address_autofill_show_street_name_first' => apply_filters('thwcfe_show_street_name_first', false),
            // 'enabled_country_code_fields' => $enabled_country_code_fields,
            'enable_inline_validations' => $enable_inline_validations,
            'required_validation' => $required_validation,
            'email_validation' => $email_validation,
            'phone_validation' => $phone_validation,
            'url_validation' => $url_validation,
            'minlength_validation' => $minlength_validation,
            'number_validation' => $number_validation,
            'api_key' => $api_key,
            'show_file_upload_button_after_upload' => $show_file_upload_button_after_upload,
		);
        if($force_wp_server_time_date_picker or $force_wp_server_time_time_picker){
            $wp_now = current_datetime();
            $wcfe_var['wp_date_time'] = apply_filters('thwcfe_wp_current_date_time', $wp_now->format('Y-m-d\TH:i:s'));
        }
        if($use_custom_ship_to_different_address_css_selector){
            $wcfe_var['custom_ship_to_different_address_css_selector'] = apply_filters('thwcfe_ship_to_different_address_css_selector', '#ship-to-different-address-checkbox');
        }        
		wp_localize_script('thwcfe-public-checkout-script', 'thwcfe_public_var', $wcfe_var);
	}

	public function define_public_hooks(){
		parent::define_public_hooks();

		$advanced_settings = $this->get_advanced_settings();
		$hp_cf = apply_filters('thwcfd_woocommerce_checkout_fields_hook_priority', 1000);
		$hidden_fields_display_position = apply_filters('thwcfe_hidden_fields_display_position', 'woocommerce_checkout_after_customer_details');

		//Show Custome Fields in Checkout Page
		add_action('woocommerce_checkout_before_customer_details', array($this, 'woo_checkout_before_customer_details'));
		add_action('woocommerce_checkout_after_customer_details', array($this, 'woo_checkout_after_customer_details'));

		add_action('woocommerce_before_checkout_billing_form', array($this, 'woo_before_checkout_billing_form'));
		add_action('woocommerce_after_checkout_billing_form', array($this, 'woo_after_checkout_billing_form'));

		add_action('woocommerce_before_checkout_shipping_form', array($this, 'woo_before_checkout_shipping_form'));
		add_action('woocommerce_after_checkout_shipping_form', array($this, 'woo_after_checkout_shipping_form'));

		add_action('woocommerce_before_checkout_registration_form', array($this, 'woo_before_checkout_registration_form'));
		add_action('woocommerce_after_checkout_registration_form', array($this, 'woo_after_checkout_registration_form'));

		add_action('woocommerce_before_order_notes', array($this, 'woo_before_order_notes'));
		add_action('woocommerce_after_order_notes', array($this, 'woo_after_order_notes'));

		add_action('woocommerce_review_order_before_cart_contents', array($this, 'woo_review_order_before_cart_contents'));
		add_action('woocommerce_review_order_after_cart_contents', array($this, 'woo_review_order_after_cart_contents'));

		add_action('woocommerce_review_order_before_order_total', array($this, 'woo_review_order_before_order_total'));
		add_action('woocommerce_review_order_after_order_total', array($this, 'woo_review_order_after_order_total'));

		add_action('woocommerce_checkout_before_terms_and_conditions', array($this, 'woo_checkout_before_terms_and_conditions'));
		add_action('woocommerce_checkout_after_terms_and_conditions', array($this, 'woo_checkout_after_terms_and_conditions'));

		add_action('woocommerce_review_order_before_submit', array($this, 'woo_review_order_before_submit'));
		add_action('woocommerce_review_order_after_submit', array($this, 'woo_review_order_after_submit'));

		add_action('woocommerce_checkout_before_order_review_heading', array($this, 'woo_checkout_before_order_review_heading'));
		add_action('woocommerce_checkout_before_order_review', array($this, 'woo_checkout_before_order_review'));
		add_action('woocommerce_checkout_after_order_review', array($this, 'woo_checkout_after_order_review'));

		add_action('woocommerce_checkout_order_review', array($this, 'woo_checkout_order_review_0'), 0);
		add_action('woocommerce_checkout_order_review', array($this, 'woo_checkout_order_review_99'), 99);

		$this->render_sections_added_to_custom_positions();

		add_filter('woocommerce_enable_order_notes_field', array($this, 'woo_enable_order_notes_field'), 1000);

		//Themehigh's Multistep plugin Support
		if(THWCFE_Utils::is_thwmsc_enabled()){
			add_action('thwmsc_multi_step_tab_panels', array($this, 'output_checkout_form_hidden_fields'));
		}

		add_action('template_redirect', array($this, 'template_redirect'));
		add_action('woocommerce_remove_cart_item', array($this, 'woo_remove_cart_item'));
		add_filter('woocommerce_update_cart_action_cart_updated', array($this, 'woo_update_cart_action_cart_updated'));


		// Checkout init
		add_filter('woocommerce_checkout_fields', array($this, 'woo_checkout_fields'), $hp_cf);
		add_filter('woocommerce_billing_fields', array($this, 'woo_billing_fields'), $hp_cf, 2);
		add_filter('woocommerce_shipping_fields', array($this, 'woo_shipping_fields'), $hp_cf, 2);
		//add_filter('woocommerce_default_address_fields', array($this, 'woo_default_address_fields'), $hp_cf);
		if(apply_filters('thwcfe_override_country_locale', true)){
			add_filter('woocommerce_get_country_locale', array($this, 'woo_get_country_locale'), $hp_cf);
			add_filter('woocommerce_get_country_locale_base', array($this, 'woo_prepare_country_locale'), $hp_cf);
			add_filter('woocommerce_get_country_locale_default', array($this, 'woo_prepare_country_locale'), $hp_cf);
		}

		//Checkout Process(Validate checkout fields, save user meta and save order meta
		add_action('woocommerce_checkout_process', array($this, 'woo_checkout_process'));
		add_action('woocommerce_after_checkout_validation', array($this, 'woo_checkout_fields_validation'), 10, 2);
		add_action('woocommerce_checkout_update_user_meta', array($this, 'woo_checkout_update_user_meta'), 10, 2);
		add_action('woocommerce_checkout_update_order_meta', array($this, 'woo_checkout_update_order_meta'), 10, 2);
		//add_action('woocommerce_checkout_order_processed', array($this, 'woo_checkout_order_processed'), 10, 3);
		add_action('woocommerce_order_status_processing', array($this, 'woo_order_status_processing'), 10, 2);

		add_action('wp_ajax_thwcfe_calculate_extra_cost', array($this, 'thwcfe_calculate_extra_cost'), 10);
    	add_action('wp_ajax_nopriv_thwcfe_calculate_extra_cost', array($this, 'thwcfe_calculate_extra_cost'), 10);
		add_action('woocommerce_cart_calculate_fees', array($this, 'woo_cart_calculate_fees') );
		add_filter('woocommerce_cart_totals_fee_html', array($this, 'woo_cart_totals_fee_html'), 10, 2);

		//Custom user meta data
		add_filter( 'woocommerce_checkout_get_value', array($this, 'woo_checkout_get_value'), 10, 2 );
		add_filter( 'default_checkout_billing_country', array($this, 'woo_default_checkout_country'), 10, 2 );
		add_filter( 'default_checkout_shipping_country', array($this, 'woo_default_checkout_country'), 10, 2 );

		//Supporting filters to use for other plugins
		//add_filter('thwcfe_custom_checkout_fields_and_values', array('THWCFE_Utils', 'get_custom_checkout_fields_and_values'), 10, 3);
		//add_filter('thwmsc_has_hooked_sections', array($this, 'has_hooked_sections'), 10, 2);
		add_filter('thwcfe_remove_disabled_fields_and_sections', array($this, 'filter_disabled_fields_and_sections'), 10, 2);
		add_filter('thwcfe_field_price_info', array($this, 'get_extra_cost_data'));
		//add_filter('thwcfe_field_price_info', array($this, 'get_extra_cost_from_session'));

		add_action($hidden_fields_display_position, array($this, 'checkout_hidden_fields'));
	}


	/********************************************************
	******** DISPLAY DEFAULT SECTIONS & FIELDS - START ******
	********************************************************/
	public function woo_checkout_fields( $checkout_fields ) {
		$sections = $this->get_checkout_sections();
		$cart_info = THWCFE_Utils::get_cart_summary();

		foreach($sections as $sname => $section) {
			if($sname !== 'billing' && $sname !== 'shipping'){
				if(THWCFE_Utils_Section::is_show_section($section, $cart_info)){
					$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
					$fieldset = $fieldset ? $fieldset : array();

					if(is_array($fieldset)){
						$sname = $sname === 'additional' ? 'order' : $sname;
						$fieldset = THWCFE_Utils_Repeat::prepare_repeat_fields_set($fieldset);
						$checkout_fields[$sname] = $fieldset; //TODO merge instead replacing existing fields to avoid losing any other non identified property
					}

					$rsections = THWCFE_Utils_Repeat::prepare_repeat_sections($section);
					if(is_array($rsections)){
						foreach($rsections as $rsname => $rsection){
							$rsfieldset = THWCFE_Utils::get_fieldset_to_show($rsection);
							$rsfieldset = $rsfieldset ? $rsfieldset : array();

							if(is_array($rsfieldset)){
								$checkout_fields[$rsname] = $rsfieldset;
							}
						}
					}
				}
			}
		}
		return $checkout_fields;
	}

	public function woo_billing_fields($fields, $country){
		$section_name = 'billing';
		$section = $this->get_checkout_section('billing');
		$use_default = apply_filters('thwcfe_use_default_fields_if_empty', false, $section_name);

		if(THWCFE_Utils_Section::is_valid_section($section)){
			if(is_wc_endpoint_url('edit-address')){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);
				if($fieldset || !$use_default){
					if(apply_filters('thwcfe_ignore_address_field_changes', false)) {
						$fieldset = $this->prepare_address_fields_my_account($fieldset, $fields, $section_name);
					}else{
						$fieldset = $this->prepare_address_fields($fieldset, $country, $fields, $section_name);
					}
					$fields = $fieldset;
				}
			}else{
				$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
				if($fieldset || !$use_default){
					$fieldset = $this->prepare_address_fields($fieldset, $country, $fields, $section_name);

					if(isset($fields['billing_state']['country']) && isset($fieldset['billing_state'])){
						$fieldset['billing_state']['country'] = $fields['billing_state']['country'];
					}

					$fields = apply_filters('thwcfe_override_address_fields', $fieldset, $country, $fields, $section_name);
					$fields = THWCFE_Utils_Repeat::prepare_repeat_fields_set($fields);
				}
			}
		}

		return is_array($fields) ? $fields : array();
	}

	public function woo_shipping_fields($fields, $country){
		$section_name = 'shipping';
		$section = $this->get_checkout_section('shipping');
		$use_default = apply_filters('thwcfe_use_default_fields_if_empty', false, $section_name);

		if(THWCFE_Utils_Section::is_valid_section($section)){
			if(is_wc_endpoint_url('edit-address')){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);
				if($fieldset || !$use_default){
					if(apply_filters('thwcfe_ignore_address_field_changes', false)) {
						$fieldset = $this->prepare_address_fields_my_account($fieldset, $fields, $section_name);
					}else{
						$fieldset = $this->prepare_address_fields($fieldset, $country, $fields, $section_name);
					}
					$fields = $fieldset;
				}
			}else{
				$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
				if($fieldset || !$use_default){
					$fieldset = $this->prepare_address_fields($fieldset, $country, $fields, $section_name);

					if(isset($fields['shipping_state']['country']) && isset($fieldset['shipping_state'])){
						$fieldset['shipping_state']['country'] = $fields['shipping_state']['country'];
					}

					$fields = $fieldset;
					$fields = THWCFE_Utils_Repeat::prepare_repeat_fields_set($fields);
				}
			}
		}

		return is_array($fields) ? $fields : array();
	}

	/*public function woo_default_address_fields($fields){
		if(apply_filters('thwcfe_skip_default_address_fields_override', false)){
			return $fields;
		}

		$sname = apply_filters('thwcfe_address_field_override_with', 'billing');
		if($sname === 'billing' || $sname === 'shipping'){
			$section = $this->get_checkout_section($sname);

			if(THWCFE_Utils_Section::is_valid_section($section)){
				$address_fields = THWCFE_Utils::get_fieldset_to_show($section);

				foreach($fields as $name => $field) {
					if($this->is_default_address_field($name)){
						$custom_field = isset($address_fields[$sname.'_'.$name]) ? $address_fields[$sname.'_'.$name] : false;

						if($custom_field && !( isset($custom_field['enabled']) && $custom_field['enabled'] == false )){
							$fields[$name]['required'] = isset($custom_field['required']) && $custom_field['required'] ? true : false;
						}
					}
				}
			}
		}
		return $fields;
	}*/

	public function woo_get_country_locale($locale) {
		/*$countries_obj = new WC_Countries();
		$allowed_countries = $countries_obj->get_allowed_countries();
		$allowed_countries = array_keys($allowed_countries);*/

		$countries = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );
		$countries = array_keys($countries);

		if(is_array($locale) && is_array($countries)){
			foreach($countries as $country){
				if(isset($locale[$country])){
					$locale[$country] = $this->woo_prepare_country_locale($locale[$country], $country);
				}
			}
		}

		return $locale;
	}

	public function woo_prepare_country_locale($fields, $country=false) {
		if(is_array($fields)){
			/*
			$override_ph = apply_filters('thwcfe_address_field_override_placeholder', true, $country);
			$override_label = apply_filters('thwcfe_address_field_override_label', true, $country);
			$override_required = apply_filters('thwcfe_address_field_override_required', false, $country);
			$override_priority = apply_filters('thwcfe_address_field_override_priority', true, $country);
			$override_class = apply_filters('thwcfe_address_field_override_class', true, $country);
			*/
			$settings = THWCFE_Utils::get_advanced_settings();

			$override_label    = $this->is_override_label($settings, $country);
			$override_ph       = $this->is_override_placeholder($settings, $country);
			$override_class    = $this->is_override_class($settings, $country);
			$override_priority = $this->is_override_priority($settings, $country);

			/*$fieldset = false;
			$sname = apply_filters('thwcfe_country_locale_override_with', 'billing');
			if($sname === 'billing' || $sname === 'shipping'){
				$section = $this->get_checkout_section($sname);
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
				}
			}*/

			foreach($fields as $key => $props){
				if($override_label && isset($props['label'])){
					unset($fields[$key]['label']);
				}

				if($override_ph && isset($props['placeholder'])){
					unset($fields[$key]['placeholder']);
				}

				if($override_class && isset($props['class'])){
					unset($fields[$key]['class']);
				}

				if($override_priority && isset($props['priority'])){
					unset($fields[$key]['priority']);
				}

				/*if($override_required && isset($props['required'])){
					if(is_array($fieldset)){
						if(isset($fieldset[$sname.'_'.$key]) && isset($fieldset[$sname.'_'.$key]['required'])){
							$fields[$key]['required'] = $fieldset[$sname.'_'.$key]['required'];
						}
					}else{
						unset($fields[$key]['required']);
					}
				}*/
			}
		}
		return $fields;
	}
	/********************************************************
	******** DISPLAY DEFAULT SECTIONS & FIELDS - END ********
	********************************************************/

	/********************************************************
	******** DISPLAY CUSTOM SECTIONS & FIELDS - START *******
	********************************************************/
	public function get_custom_sections_by_hook($hook_name){
		$section_hook_map = THWCFE_Utils::get_section_hook_map();

		$sections = false;
		if(is_array($section_hook_map) && isset($section_hook_map[$hook_name])){
			$sections = $section_hook_map[$hook_name];
		}

		return empty($sections) ? false : $sections;
	}

	public function output_custom_section($sections, $checkout=false, $wrap_with=''){
		if($sections && is_array($sections)){
			$cart_info = THWCFE_Utils::get_cart_summary();

			foreach($sections as $sname){
				$section = THWCFE_Utils::get_checkout_section($sname, $cart_info);
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$this->output_custom_section_single($section, $cart_info, $checkout, $wrap_with);

					$rsections = THWCFE_Utils_Repeat::prepare_repeat_sections($section);
					if(is_array($rsections)){
						foreach($rsections as $rsection){
							$this->output_custom_section_single($rsection, $cart_info, $checkout, $wrap_with);
						}
					}
				}
			}
		}
	}

	public function output_custom_section_single($section, $cart_info, $checkout=false, $wrap_with=''){
		$sname = $section->get_property('name');
		$fields = THWCFE_Utils_Section::get_fieldset($section, $cart_info);
		$fields = THWCFE_Utils_Repeat::prepare_repeat_fields_set($fields);
		$rn = false;

		$r_exp = $section->get_property('repeat_rules');
		if($r_exp){
			$sname = $section->get_property('name');
			$rn = is_numeric($rn) ? $rn : THWCFE_Utils_Repeat::get_repeat_times($r_exp, $sname, 'section');
			
		}else{
			$rn = 0;
		}

		do_action('thwcfe_before_section_'.$sname, $section);

		if(is_array($fields) && sizeof($fields) > 0){
			$wrap_with_div = THWCFE_Utils::get_settings('wrap_custom_sections_with_div');
			if($wrap_with_div != 'yes' && THWCFE_Utils_Section::has_ajax_rules($section)){
				$wrap_with_div = 'yes';
			}

			if($wrap_with === 'tr'){
				echo '<tr><td colspan="2">';
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
			if($section->get_property('show_title')){
				echo THWCFE_Utils_Section::get_title_html($section, $rn);
			}

			do_action('thwcfe_before_section_fields_'.$sname, $section);

			$instance_type = false;
			$qparams = array();
			if($checkout instanceof WC_Checkout){
				$instance_type = 'checkout';
			}else if(is_array($checkout) && isset($checkout['post_data'])){
				$instance_type = 'query_string';
				$qparams = THWCFE_Utils::extract_query_string_params($checkout['post_data']);
			}

			foreach($fields as $name => $field){
				if(!(isset($field['enabled']) && $field['enabled'] == false)) {
					$value = null;

					if($instance_type === 'checkout'){
						$value = $checkout->get_value($name);
					}else if($instance_type === 'query_string'){
						//$value = THWCFE_Utils::get_value_from_query_string($checkout['post_data'], $name);
						$value = isset($qparams[$name]) ? $qparams[$name] : '';
					}

					if(!$value && is_user_logged_in() && isset($field['user_meta']) && $field['user_meta']){
						$current_user = wp_get_current_user();
						if(metadata_exists('user', $current_user->ID, $field['name'])){
							$value = get_user_meta($current_user->ID, $field['name'], true);
						}
					}

					woocommerce_form_field($name, $field, $value);
				}
			}

			do_action('thwcfe_after_section_fields_'.$sname, $section);

			if($wrap_with_div === 'yes'){
				echo '</div>';
			}

			if($wrap_with === 'tr'){
				echo '</td></tr>';
			}
		}

		do_action('thwcfe_after_section_'.$sname, $section);
	}

	public function woo_before_checkout_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_checkout_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_after_checkout_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_checkout_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_checkout_before_customer_details() {
		$sections = $this->get_custom_sections_by_hook('before_customer_details');
		$this->output_custom_section($sections);
	}
	public function woo_checkout_after_customer_details() {
		$sections = $this->get_custom_sections_by_hook('after_customer_details');
		$this->output_custom_section($sections);
	}
	public function woo_before_checkout_billing_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_checkout_billing_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_after_checkout_billing_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_checkout_billing_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_before_checkout_shipping_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_checkout_shipping_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_after_checkout_shipping_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_checkout_shipping_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_before_checkout_registration_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_checkout_registration_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_after_checkout_registration_form($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_checkout_registration_form');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_before_order_notes($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_order_notes');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_after_order_notes($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_order_notes');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_review_order_before_cart_contents($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_cart_contents');
		$this->output_custom_section($sections, $checkout, 'tr');
	}
	public function woo_review_order_after_cart_contents($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_cart_contents');
		$this->output_custom_section($sections, $checkout, 'tr');
	}
	public function woo_review_order_before_order_total($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_order_total');
		$this->output_custom_section($sections, $checkout, 'tr');
	}
	public function woo_review_order_after_order_total($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_order_total');
		$this->output_custom_section($sections, $checkout, 'tr');
	}
	public function woo_checkout_before_terms_and_conditions($checkout) {
		if(!is_wc_endpoint_url('order-pay')){
			$sections = $this->get_custom_sections_by_hook('before_terms_and_conditions');
			$this->output_custom_section($sections, $_POST);
		}
	}
	public function woo_checkout_after_terms_and_conditions($checkout) {
		if(!is_wc_endpoint_url('order-pay')){
			$sections = $this->get_custom_sections_by_hook('after_terms_and_conditions');
			$this->output_custom_section($sections, $_POST);
		}
	}
	public function woo_review_order_before_submit($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_submit');
		$this->output_custom_section($sections, $_POST);
	}
	public function woo_review_order_after_submit($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_submit');
		$this->output_custom_section($sections, $_POST);
	}
	public function woo_checkout_before_order_review_heading($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_order_review_heading');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_checkout_before_order_review($checkout) {
		$sections = $this->get_custom_sections_by_hook('before_order_review');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_checkout_after_order_review($checkout) {
		$sections = $this->get_custom_sections_by_hook('after_order_review');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_checkout_order_review_0($checkout) {
		$sections = $this->get_custom_sections_by_hook('order_review_0');
		$this->output_custom_section($sections, $checkout);
	}
	public function woo_checkout_order_review_99($checkout) {
		$sections = $this->get_custom_sections_by_hook('order_review_99');
		$this->output_custom_section($sections, $checkout);
	}

	public function render_sections_added_to_custom_positions(){
		$positions = apply_filters('thwcfe_custom_section_positions', array());
		if(is_array($positions)){
			foreach($positions as $hook_name => $label){
				add_action($hook_name, array($this, 'woo_checkout_custom_hook_legacy'));
			}
		}

		$positions = apply_filters('thwcfe_custom_section_display_positions', array());
		if(is_array($positions)){
			foreach($positions as $hook_name => $label){
				add_action($hook_name, array($this, 'woo_checkout_custom_hook'));
			}
		}
	}

	public function woo_checkout_custom_hook_legacy($hook_name, $checkout=false){
		$sections = $this->get_custom_sections_by_hook($hook_name);
		$this->output_custom_section($sections, $checkout);
	}

	public function woo_checkout_custom_hook($checkout=false){
		$hook_name = current_action();
		$sections = $this->get_custom_sections_by_hook($hook_name);
		$this->output_custom_section($sections, $checkout);
	}

	/* Hide Additional Fields title if no fields available. */
	public function woo_enable_order_notes_field() {
		global $theorder;

		$section = $this->get_checkout_section('additional');
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$cart_info = false;

			if(WC()->cart){
				$cart_info = THWCFE_Utils::get_cart_summary();
			}else{
			   	$order     = $theorder;
				$cart_info = THWCFE_Utils::get_order_summary($order);
			}

			$fieldset  = THWCFE_Utils_Section::get_fields($section, $cart_info);

			//$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
			if($fieldset){
				$enabled = 0;
				foreach($fieldset as $field){
					if($field->get_property('enabled')){
						$enabled = 1;
						break;
					}
				}
				return $enabled > 0 ? true : false;
			}else{
				return false;
			}
		}
		return true;
	}

	public function checkout_hidden_fields(){
		//Themehigh's Multistep plugin Support
		if(!THWCFE_Utils::is_thwmsc_enabled()){
			$this->output_checkout_form_hidden_fields();
		}
	}
   /*********************************************************
	******** DISPLAY CUSTOM SECTIONS & FIELDS - END *********
	*********************************************************/


	/*******************************************
	******** CHECKOUT PROCESS - START **********
	*******************************************/
	public function filter_disabled_fields_and_sections($checkout_fields, $posted){
		$disabled_fields = isset($posted['thwcfe_disabled_fields']) ? wc_clean($posted['thwcfe_disabled_fields']) : '';
		$disabled_sections = isset($posted['thwcfe_disabled_sections']) ? wc_clean($posted['thwcfe_disabled_sections']) : '';

		$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
		$dis_fields = array_unique($dis_fields);

		//$default_shipping_fields = array('shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode');
		//$dis_fields = array_diff($dis_fields, $default_shipping_fields);

		$dis_sections = $disabled_sections ? explode(",", $disabled_sections) : array();
		$dis_sections = array_unique($dis_sections);

		//$dis_sections = array();
		$dis_hooks = array();
		$ship_to_different_address = isset($posted['ship_to_different_address']) ? $posted['ship_to_different_address'] : false;

		if(($ship_to_different_address == false || ! WC()->cart->needs_shipping_address())){
			$dis_hooks = array_merge($dis_hooks, array('before_checkout_shipping_form','after_checkout_shipping_form'));
		}
		if(is_user_logged_in()){
			$dis_hooks = array_merge($dis_hooks, array('before_checkout_registration_form','after_checkout_registration_form'));
		}
		if(!(isset($posted['terms-field']) && $posted['terms-field'])){
			$dis_hooks = array_merge($dis_hooks, array('before_terms_and_conditions','after_terms_and_conditions'));
		}

		$dis_hooks = apply_filters('thwcfe_disabled_hooks', $dis_hooks);

		if(!empty($dis_hooks)){
			$rsnames = THWCFE_Utils_Repeat::get_repeat_section_names_from_posted($posted);

			foreach($dis_hooks as $hname){
				$hooked_sections = $this->get_custom_sections_by_hook($hname);
				if(is_array($hooked_sections)){
					foreach($hooked_sections as $sname){
						if(!in_array($sname, THWCFE_Utils_Section::$DEFAULT_SECTIONS)){
							$dis_sections[] = $sname;
							$rsections = isset($rsnames[$sname]) ? $rsnames[$sname] : false;
							if(is_array($rsections)){
								$dis_sections = array_merge($dis_sections, $rsections);
							}
						}
					}
				}
			}
		}

		$dis_sections = apply_filters('thwcfe_disabled_sections', $dis_sections);
		$dis_fields = apply_filters('thwcfe_disabled_fields', $dis_fields);

		if( (is_array($dis_fields) && !empty($dis_fields)) || (is_array($dis_sections) && !empty($dis_sections)) ){
			//$checkout_fields = WC()->checkout->checkout_fields;
			$modified = false;

			if(is_array($checkout_fields)){
				foreach($checkout_fields as $fieldset_key => $fieldset) {
					if(in_array($fieldset_key, $dis_sections)){
						unset($checkout_fields[$fieldset_key]);
						$modified = true;
						continue;
					}

					if(is_array($dis_fields)){
						foreach($dis_fields as $fname){
							if(isset($fieldset[$fname])){
								unset($checkout_fields[$fieldset_key][$fname]);
								$modified = true;
							}
						}
					}
				}
			}

			if(!$modified){
				//WC()->checkout->checkout_fields = $checkout_fields;
				$checkout_fields = false;
			}
		}
		return $checkout_fields;
	}

	// Prepare Checkout Fields
	public function woo_checkout_process(){
		$checkout_fields = WC()->checkout->checkout_fields;
		$checkout_fields = $this->filter_disabled_fields_and_sections($checkout_fields, $_POST);
		if($checkout_fields){
			WC()->checkout->checkout_fields = $checkout_fields;
		}
	}

	// Validate Checkout Fields
	public function woo_checkout_fields_validation($posted, $errors){
		if(is_plugin_active('woocommerce-paypal-payments/woocommerce-paypal-payments.php') && $posted['payment_method'] == 'ppcp-gateway'){
			$post = $_POST;	
			$disabled_fields = $post['thwcfe_disabled_fields'];
			$disabled_fields_arr = explode(',', $disabled_fields);

			if(isset($disabled_fields_arr)){
				foreach($disabled_fields_arr as $key => $disabled_field){
					if (array_key_exists($disabled_field , $posted)){
						$error_data_arr = $errors->error_data;
						$error_arr = $errors->errors;
						foreach ($error_data_arr as $key => $error_data) {	
							if($disabled_field == $error_data['id']){
								unset($error_data_arr[$key]);
								unset($error_arr[$key]);
								$errors->error_data = $error_data_arr;
								$errors->errors = $error_arr;
							}
						}	
					}
				}
			}
		}
		$checkout_fields = WC()->checkout->checkout_fields;

		foreach($checkout_fields as $fieldset_key => $fieldset){
			if($this->maybe_skip_fieldset($fieldset_key, $posted)){
				continue;
			}

			foreach($fieldset as $key => $field) {
				if(isset($field['type']) && ($field['type'] === 'file')){
					//$this->validate_file($field, $posted, $errors);

				}else if(isset($posted[$key]) && !$this->is_blank($posted[$key])){
					$this->validate_custom_field($field, $posted,  $fieldset_key, $errors);
				}
			}
		}
	}

	// Save User Meta
	public function woo_checkout_update_user_meta($customer_id, $posted){
		$checkout_fields = WC()->checkout->checkout_fields;

		foreach($checkout_fields as $fieldset_key => $fieldset){
			if($fieldset_key === 'shipping' && !WC()->cart->needs_shipping()){
				continue;
			}

			foreach($fieldset as $key => $field) {
				if(THWCFE_Utils::is_wc_handle_custom_field($field)){
					continue;
				}
				if(isset($field['custom']) && $field['custom'] && isset($posted[$key])){
					if(isset($field['user_meta']) && $field['user_meta']){
						$type = $field['type'];
						$value = false;

						if($type === 'file'){
							$value = $posted[$key];

						}else{
							$value  = $posted[$key];
							$value  = is_array($value) ? implode(",", $value) : $value;
							$fvalue = $field['default'];

							if($field['type'] === 'checkbox'){
								if($value == 1){
									$value = !empty($field['on_value']) ? 1 : $value;
								}else{
									$value = !empty($field['off_value']) ? $field['off_value'] : $value;
								}
							}
						}
			
						$value = apply_filters( 'thwcfe_woocommerce_checkout_user_meta_posted_value_'.$key, $value, $customer_id, $posted );
						update_user_meta($customer_id, $key, $value );
					}
				}
			}
		}
	}

	// Save Order Meta
	public function woo_checkout_update_order_meta($order_id, $posted){
		$order = wc_get_order( $order_id );
		$checkout_fields = WC()->checkout->checkout_fields;
		$ship_to_different_address = isset($posted['ship_to_different_address']) ? $posted['ship_to_different_address'] : false;

		if(!$ship_to_different_address || !WC()->cart->needs_shipping_address()){
			
			$order->update_meta_data('_thwcfe_ship_to_billing', 1);
			// update_post_meta($order_id, '_thwcfe_ship_to_billing', 1);
		}else{
			// update_post_meta($order_id, '_thwcfe_ship_to_billing', 0);
			$order->update_meta_data('_thwcfe_ship_to_billing', 0);
		}

		$disabled_sections = isset($_POST['thwcfe_disabled_sections']) ? wc_clean($_POST['thwcfe_disabled_sections']) : '';
		if($disabled_sections){
			$dis_sections = explode(",", $disabled_sections);
			if(is_array($dis_sections) && !empty($dis_sections)){
				$dis_sections = array_unique($dis_sections);
				$dis_sections = implode(",", $dis_sections);
				// update_post_meta($order_id, '_thwcfe_disabled_sections', $dis_sections);
				$order->update_meta_data('_thwcfe_disabled_sections', $dis_sections);
			}
		}

		$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
		if($disabled_fields){
			$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : false;
			if(is_array($dis_fields) && !empty($dis_fields)){
				$dis_fields = array_unique($dis_fields);
				$dis_fields = implode(",", $dis_fields);
				// update_post_meta($order_id, '_thwcfe_disabled_fields', $dis_fields);
				$order->update_meta_data('_thwcfe_disabled_fields', $dis_fields);
			}
		}

		$repeat_fields = isset( $_POST['thwcfe_repeat_fields'] ) ? wc_clean( $_POST['thwcfe_repeat_fields'] ) : '';
		if($repeat_fields){
			$r_fields = $repeat_fields ? explode(",", $repeat_fields) : false;
			if(is_array($r_fields) && !empty($r_fields)){
				$r_fields = array_unique($r_fields);
				$r_fields = implode(",", $r_fields);
				// update_post_meta($order_id, '_thwcfe_repeat_fields', $r_fields);
				$order->update_meta_data('_thwcfe_repeat_fields', $r_fields);
			}
		}
		$repeat_sections = isset( $_POST['thwcfe_repeat_sections'] ) ? wc_clean( $_POST['thwcfe_repeat_sections'] ) : '';
		if($repeat_sections){
			$r_sections = $repeat_sections ? explode(",", $repeat_sections) : false;
			if(is_array($r_sections) && !empty($r_sections)){
				$r_sections = array_unique($r_sections);
				$r_sections = implode(",", $r_sections);
				// update_post_meta($order_id, '_thwcfe_repeat_sections', $r_sections);
				$order->update_meta_data('_thwcfe_repeat_sections', $r_sections);
			}
		}

		foreach($checkout_fields as $fieldset_key => $fieldset){
			if($this->maybe_skip_fieldset($fieldset_key, $posted)){
				continue;
			}

			foreach($fieldset as $key => $field) {
				if(THWCFE_Utils::is_wc_handle_custom_field($field)){
					continue;
				}	
				if(isset($field['custom']) && $field['custom'] && isset($field['order_meta']) && $field['order_meta']){
					$type = $field['type'];
					$value = false;

					if($type === 'file'){
						$value = isset($posted[$key]) && !empty($posted[$key]) ? $posted[$key] : false;

					}else{
						$value = isset($posted[$key]) && !empty($posted[$key]) ? $posted[$key] : false;

						if($field['type'] === 'checkbox'){
							if($value == 1){
								$value = !empty($field['on_value']) ? $field['on_value'] : $value;
							}else{
								$value = !empty($field['off_value']) ? $field['off_value'] : $value;
							}
						}

						if($value){
							$value  = is_array($value) ? implode(",", $value) : $value;
							$fvalue = $field['default'];
						}
					}

					if($value){
						$value = apply_filters( 'thwcfe_woocommerce_checkout_order_meta_posted_value_'.$key, $value, $order_id, $posted );
						// update_post_meta($order_id, $key, $value);
						$order->update_meta_data($key, $value);
					}
				}
			}
		}
		$order->save();
	}

	private function maybe_skip_fieldset( $fieldset_key, $data ) {
		$ship_to_different_address = isset($data['ship_to_different_address']) ? $data['ship_to_different_address'] : false;

		if ( 'shipping' === $fieldset_key && ( ! $ship_to_different_address || ! WC()->cart->needs_shipping_address() ) ) {
			return true;
		}
		return false;
	}

	/*public function woo_checkout_order_processed($order_id, $posted_data, $order){
		$this->session->clear_data();
	}*/

	public function woo_order_status_processing($order_id, $order){
		if($this->use_extra_cost_calculation_legacy_call()){
			$this->session->clear_data();
		}
	}

	public function woo_remove_cart_item(){
		if($this->use_extra_cost_calculation_legacy_call()){
			$this->session->clear_data();
		}
	}

	public function woo_update_cart_action_cart_updated($cart_updated){
		if($this->use_extra_cost_calculation_legacy_call()){
			$this->session->clear_data();
		}

		return $cart_updated;
	}

	public function template_redirect(){
		if($this->use_extra_cost_calculation_legacy_call()){
			$this->session->clear_data();
			//$this->session->set_cookie();
		}
	}
	/*******************************************
	******** CHECKOUT PROCESS - END ************
	*******************************************/

	/*******************************************
	******** PRICE CALCULATION - START *********
	********************************************/
	public function validate_and_filter_fields($price_infos) {
		if($price_infos && is_array($price_infos)){
			$checkout_fields = $this->get_all_checkout_fields_map();

			if(!empty($checkout_fields)){
				$f_labels = array();

				$cfields = array();
				foreach($price_infos as $name => $price_info){
					$field = isset($checkout_fields[$name]) && is_array($checkout_fields[$name]) ? $checkout_fields[$name] : false;
					if($field){
						$cfields[$name] = $field;
						$rfields = THWCFE_Utils_Repeat::prepare_repeat_fields_single($field);
						if(is_array($rfields)){
							$cfields = array_merge($cfields, $rfields);
						}
					}
				}

				foreach($price_infos as $name => $price_info){
					//$field = isset($checkout_fields[$name]) && is_array($checkout_fields[$name]) ? $checkout_fields[$name] : false;
					$field = isset($cfields[$name]) && is_array($cfields[$name]) ? $cfields[$name] : false;

					if($field){
						$value = isset($price_info['value']) ? $price_info['value'] : '';
						if(is_array($value)){
							$value = implode(",", $price_info['value']);
						}
						$value = !empty($value) ? trim($value) : '';

						$valid = $this->validate_field($name, $value, $field);

						$label = $price_info['label'];
						$label = THWCFE_Utils::preare_fee_name($name, $label, $value, $f_labels);

						$f_labels[] = $label;
						$price_infos[$name]['label'] = $label;

						if(!$valid){
							unset($price_infos[$name]);
						}
					}
				}
			}
		}
		return $price_infos;
	}

	// Validate Checkout Fields
	public function validate_field($name, $value, $field){
		$valid = true;
		if($value && !$this->is_blank($value)){
			$validation = isset($field['validate']) ? $field['validate'] : '';

			if(is_array($validation) && !empty($validation)){
				foreach($validation as $rule){
					switch($rule) {
						case 'number' :
							if(!is_numeric($value)){
								$valid = false;
							}
							break;
						default:
							$custom_validators = $this->get_settings('custom_validators');
							$validator = is_array($custom_validators) && isset($custom_validators[$rule]) ? $custom_validators[$rule] : false;
							if(is_array($validator)){
								$pattern = $validator['pattern'];

								if(preg_match($pattern, $value) === 0) {
									$valid = false;
								}
								break;
							}
					}
				}
			}
		}
		return $valid;
	}

	public function get_extra_cost_from_session() {
		return $this->session->get_extra_cost();
	}

	/*public function save_extra_cost_in_session($price_info) {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);

		if($force_wp_session){
			$this->save_extra_cost_in_wp_session($price_info);
		}else{
			$this->save_extra_cost_in_woo_session($price_info);
		}
	}

	public function get_extra_cost_from_session() {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);
		$extra_cost = array();

		if($force_wp_session){
			$extra_cost = $this->get_extra_cost_from_wp_session();
		}else{
			$extra_cost = $this->get_extra_cost_from_woo_session();
		}
		return $extra_cost;
	}

	public function clear_extra_cost_info_from_session() {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);

		if($force_wp_session){
			$this->clear_extra_cost_info_from_wp_session();
		}else{
			$this->clear_extra_cost_info_from_woo_session();
		}
	}

	// WP Session
	public function save_extra_cost_in_wp_session($price_info) {
		if(!isset($_SESSION) || apply_filters('thwcfe_force_start_session', false)){
			session_start();
		}
		$this->clear_extra_cost_info_from_wp_session();
		$_SESSION['thwcfe-extra-cost-info'] = $price_info;
	}

	public function get_extra_cost_from_wp_session() {
		if(!isset($_SESSION)){
			session_start();
		}
    	$extra_cost = isset($_SESSION['thwcfe-extra-cost-info']) ? $_SESSION['thwcfe-extra-cost-info'] : false;
		return is_array($extra_cost) ? $extra_cost : array();
	}

	public function clear_extra_cost_info_from_wp_session() {
		unset($_SESSION['thwcfe-extra-cost-info']);
	}

	// Woo Session
	public function save_extra_cost_in_woo_session($price_info) {
		if(WC()->session){
			$this->clear_extra_cost_info_from_woo_session();
			WC()->session->set('thwcfe-extra-cost-info', $price_info);
		}
	}

	public function get_extra_cost_from_woo_session() {
		$extra_cost = WC()->session->get('thwcfe-extra-cost-info');
		return is_array($extra_cost) ? $extra_cost : array();
	}

	public function clear_extra_cost_info_from_woo_session() {
		if(WC()->session){
			WC()->session->__unset('thwcfe-extra-cost-info');
		}
	}*/

	// Aborted Request in Session
	public function is_allowed_request($posted, $save=true){
		$curr_request = isset($posted['uid']) ? stripslashes($posted['uid']) : '';
		$is_allowed = true;

		if(is_numeric($curr_request)){
			$last_request = $this->session->get_last_request();

			if(is_numeric($last_request)){
				$is_allowed = $curr_request > $last_request ? true : false;
			}

			if($save){
				$this->session->save_last_request($curr_request);
			}
		}else{
			$is_allowed = false;
		}

		return $is_allowed;
	}

	/*public function is_aborted_request($posted, $save=true){
		$abort_req = isset($posted['abort_req']) ? stripslashes($posted['abort_req']) : '';
		$uid = isset($posted['uid']) ? stripslashes($posted['uid']) : '';
		$is_aborted = false;

		//if($abort_req){
			$aborted_requests = $this->session->get_aborted_requests();
			$is_aborted = in_array($uid, $aborted_requests);

			if($save){
				$this->session->save_aborted_requests($abort_req);
			}
		//}else{
			//$this->session->clear_aborted_requests();
		//}
		return $is_aborted;
	}*/

	/*public function save_aborted_request_info_in_session($aborted_request) {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);

		if($force_wp_session){
			$this->save_aborted_request_info_in_wp_session($aborted_request);
		}else{
			$this->save_aborted_request_info_in_woo_session($aborted_request);
		}
	}
	public function get_aborted_request_info_from_session() {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);
		$aborted_requests = array();

		if($force_wp_session){
			$aborted_requests = $this->get_aborted_request_info_from_wp_session();
		}else{
			$aborted_requests = $this->get_aborted_request_info_from_woo_session();
		}
		return $aborted_requests;
	}
	public function clear_aborted_request_info_from_session() {
		$force_wp_session = apply_filters('thwcfe_force_wp_session', false);

		if($force_wp_session){
			$this->clear_aborted_request_info_from_wp_session();
		}else{
			$this->clear_aborted_request_info_from_woo_session();
		}
	}
	public function save_aborted_request_info_in_wp_session($aborted_request) {
		if(!isset($_SESSION) || apply_filters('thwcfe_force_start_session', false)){
			session_start();
		}
		$aborted_requests = $this->get_aborted_request_info_from_wp_session();
		$aborted_requests[] = $aborted_request;
		$_SESSION['thwcfe-aborted-requests'] = $aborted_requests;
	}
	public function save_aborted_request_info_in_woo_session($aborted_request) {
		if($aborted_request && WC()->session){
			$aborted_requests = $this->get_aborted_request_info_from_woo_session();
			$aborted_requests[] = $aborted_request;
			WC()->session->set('thwcfe-aborted-requests', $aborted_requests);
		}
	}

	public function get_aborted_request_info_from_wp_session() {
		if(!isset($_SESSION)){
			session_start();
		}
    	$aborted_requests = isset($_SESSION['thwcfe-aborted-requests']) ? $_SESSION['thwcfe-aborted-requests'] : false;
		return is_array($aborted_requests) ? $aborted_requests : array();
	}
	public function get_aborted_request_info_from_woo_session() {
		$aborted_requests = WC()->session->get('thwcfe-aborted-requests');
		return is_array($aborted_requests) ? $aborted_requests : array();
	}

	public function clear_aborted_request_info_from_wp_session() {
		unset($_SESSION['thwcfe-aborted-requests']);
	}
	public function clear_aborted_request_info_from_woo_session() {
		if(WC()->session){
			WC()->session->__unset('thwcfe-aborted-requests');
		}
	}*/

	private function use_extra_cost_calculation_legacy_call(){
		$use_legacy_call = apply_filters('thwcfe_use_extra_cost_calculation_legacy_call', false);
		return $use_legacy_call;
	}

	public function thwcfe_calculate_extra_cost() {
		$use_legacy_call = $this->use_extra_cost_calculation_legacy_call();

		if($use_legacy_call){
			$this->thwcfe_calculate_extra_cost_legacy();
		}else{
			//Do nothing....
		}
	}

	public function thwcfe_calculate_extra_cost_legacy() {
		if($this->is_allowed_request($_POST)){
			$price_info_json = isset($_POST['price_info']) ? stripslashes($_POST['price_info']) : '';

			if($price_info_json) {
				$price_info = json_decode($price_info_json, true);
				$price_info = $this->validate_and_filter_fields($price_info);

				//if($this->is_allowed_request($_POST, false)){
					$this->session->save_extra_cost($price_info);
				//}
			}else{
				$this->session->clear_extra_cost_info();
			}
		}
	}

	public function calculate_extra_cost($price_info){
		$fprice = 0;
		$price_type = isset($price_info['price_type']) ? $price_info['price_type'] : '';
		$price 		= isset($price_info['price']) ? $price_info['price'] : 0;
		$multiple   = isset($price_info['multiple']) ? $price_info['multiple'] : 0;
		$name 		= isset($price_info['name']) && !empty($price_info['name']) ? $price_info['name'] : false;
		$value 		= isset($price_info['value']) ? $price_info['value'] : false;

		if($name){
			$price = apply_filters('thwcfe_checkout_field_extra_price_'.$name, $price, $value);
		}

		global $woocommerce;
		$cart_total = $woocommerce->cart->cart_contents_total; //$woocommerce->cart->get_cart_total();
		if($price_type === 'percentage_subtotal'){
			$cart_total = $woocommerce->cart->subtotal;
		}else if($price_type === 'percentage_subtotal_ex_tax'){
			$cart_total = $woocommerce->cart->subtotal_ex_tax;
		}else if($price_type === 'percentage_total'){
			//$cart_total = $woocommerce->cart->subtotal_ex_tax;
		}else if($price_type === 'percentage_total_ex_tax'){
			//$cart_total = $woocommerce->cart->subtotal_ex_tax;
		}

		if($multiple == 1){
			$price_arr = explode(",", $price);
			$price_type_arr = explode(",", $price_type);

			foreach($price_arr as $index => $oprice){
				$oprice_type = isset($price_type_arr[$index]) ? $price_type_arr[$index] : 'normal';

				if($oprice_type === 'percentage' || $oprice_type === 'percentage_subtotal' || $oprice_type === 'percentage_subtotal_ex_tax'){
					if(is_numeric($oprice) && is_numeric($cart_total)){
						$fprice = $fprice + ($oprice/100)*$cart_total;
					}
				}else{
					if(is_numeric($oprice)){
						$fprice = $fprice + $oprice;
					}
				}
			}
		}else{
			if($price_type === 'percentage' || $price_type === 'percentage_subtotal' || $price_type === 'percentage_subtotal_ex_tax'){
				if(is_numeric($price) && is_numeric($cart_total)){
					$fprice = ($price/100)*$cart_total;
				}
			}else if($price_type === 'dynamic'){
				$price_unit = isset($price_info['price_unit']) ? $price_info['price_unit'] : false;

				$qty   = isset($price_info['qty_field']) ? $price_info['qty_field'] : false;
				$qty   = apply_filters('thwcfe_dynamic_price_quantity', $qty, $name);
				$value = !empty($qty) && is_numeric($qty) ? $qty : $value;

				if(is_numeric($price) && is_numeric($value) && is_numeric($price_unit) && $price_unit > 0){
					$fprice = $price*($value/$price_unit);
				}
			}else if($price_type === 'custom'){
				if(is_numeric($value)){
					$fprice = $value;
				}
			}else{
				if(is_numeric($price)){
					$fprice = $price;
				}
			}
		}

		if($name){
			$fprice = apply_filters('thwcfe_checkout_field_extra_cost_'.$name, $fprice, $value);
			$fprice = apply_filters('thwcfe_checkout_field_extra_cost', $fprice, $name, $value);
		}

		return $fprice;
	}

	public function get_posted_value($key){
		$value = isset($_POST[$key]) ? stripslashes($_POST[$key]) : '';

		if(!$value){
			$post_data = isset($_POST['post_data']) ? $_POST['post_data'] : '';

			if($post_data){
				parse_str($post_data, $post_data_arr);
				$value = isset($post_data_arr[$key]) ? stripslashes($post_data_arr[$key]) : '';
			}
		}

		return $value;
	}

	public function get_extra_cost_data(){
		$extra_cost = null;
		$use_legacy_call = $this->use_extra_cost_calculation_legacy_call();

		if($use_legacy_call){
			$extra_cost = $this->get_extra_cost_from_session();
		}else{
			$extra_cost_json = urldecode($this->get_posted_value('thwcfe_price_data'));
			//$extra_cost_json = $this->get_posted_value('thwcfe_price_data');

			if($extra_cost_json) {
				$extra_cost = json_decode($extra_cost_json, true);
				$extra_cost = $this->validate_and_filter_fields($extra_cost);
			}
		}
		return is_array($extra_cost) ? $extra_cost : array();
	}

	public function woo_cart_calculate_fees($cart){
		if(is_checkout()){
			$extra_cost = $this->get_extra_cost_data();
			//$extra_cost = $this->get_extra_cost_from_session();

			foreach($extra_cost as $name => $price_info){
				$taxable = isset($price_info['taxable']) && $price_info['taxable'] === 'yes' ? true : false;
				$tax_class = isset($price_info['tax_class']) && !empty($price_info['tax_class']) ? trim($price_info['tax_class']) : '';
				//$tax_class = isset($price_info['tax_class']) && !empty($price_info['tax_class']) ? trim($price_info['taxable']) : '';

				$fee = $this->calculate_extra_cost($price_info);
				if($fee != 0 || apply_filters('thwcfe_show_zero_fee', false, $fee)){
					if(!empty($cart->recurring_cart_key) && apply_filters('thwcfe_wc_subscriptions_recurring_fee', true, $name, $price_info)){
						$cart->add_fee($price_info['label'], $fee, $taxable, $tax_class);
					}else{
						WC()->cart->add_fee($price_info['label'], $fee, $taxable, $tax_class);
					}
				}
			}
		}
	}

	public function woo_cart_totals_fee_html($cart_totals_fee_html, $fee){
		$cart_fee_names = $this->get_cart_fee_names();
		$show_tax_label = apply_filters('thwcfe_show_tax_label_in_cart_totals_fee_html', true);

		if($show_tax_label && $cart_fee_names && in_array($fee->name, $cart_fee_names)){
			if($fee && is_numeric($fee->total) && $fee->total != 0){
				if(wc_prices_include_tax()){
					if(!$this->display_prices_including_tax()){
						$cart_totals_fee_html .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
					}
				}else{
					if($this->display_prices_including_tax()){
						$cart_totals_fee_html .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
					}
				}
			}
		}
		return $cart_totals_fee_html;
	}

	public function display_prices_including_tax() {
		if($this->woo_version_check('3.3.0')){
			return WC()->cart->display_prices_including_tax();
		}
		return 'incl' === WC()->cart->tax_display_cart ? true : false;
	}

	public function get_cart_fee_names(){
		$names = array();
		$extra_cost = $this->get_extra_cost_data();
		//$extra_cost = $this->get_extra_cost_from_session();
		if(is_array($extra_cost)){
			foreach($extra_cost as $name => $price_info){
				if(isset($price_info['label'])){
					$names[] = $price_info['label'];
				}
			}
		}
		return !empty($names) ? $names : false;
	}
	/*******************************************
	******** PRICE CALCULATION - END ***********
	********************************************/

	/***********************************************************
	******** DISPLAY & SAVE CUSTOM USER META FIELDS - START ***
	***********************************************************/
	public function woo_checkout_get_value($value, $key){
		$user_fields = THWCFE_Utils_Section::get_user_fieldset_full();

		if(is_user_logged_in() && is_array( $user_fields ) && array_key_exists( $key, $user_fields )) {
			$current_user = wp_get_current_user();

			if($meta = get_user_meta( $current_user->ID, $key, true )){
				return $meta;
			}
		}
		return $value;
	}

	public function woo_default_checkout_country($value, $key){
		if($value && apply_filters('thwcfe_country_hidden_field_override_default_value', false, $key, $value)){
			$section_name = $key === 'shipping_country' ? 'shipping' : 'billing';
			$section = $this->get_checkout_section($section_name);
			$fieldset = THWCFE_Utils_Section::get_fieldset($section);

			if($fieldset && isset($fieldset[$key])){
				$field = $fieldset[$key];
				if(isset($field['type']) && $field['type'] === 'hidden'){
					$value = $field['default'] ? $field['default'] : $value;
				}
			}
		}
		return $value;
	}
	/***********************************************************
	******** DISPLAY & SAVE CUSTOM USER META FIELDS - START ***
	***********************************************************/

}

endif;
