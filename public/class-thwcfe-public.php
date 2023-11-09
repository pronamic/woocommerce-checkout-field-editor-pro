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

if(!class_exists('THWCFE_Public')):

class THWCFE_Public extends WCFE_Checkout_Fields_Utils{
	public $plugin_name;
	public $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function define_public_hooks(){
		$advanced_settings = $this->get_advanced_settings();

		add_filter('woocommerce_localisation_address_formats', array($this, 'woo_localisation_address_formats'), 20, 2);
		add_filter('woocommerce_formatted_address_replacements', array($this, 'woo_formatted_address_replacements'), 20, 2);
		add_filter('woocommerce_order_formatted_billing_address', array($this, 'woo_order_formatted_billing_address'), 20, 2);
		add_filter('woocommerce_order_formatted_shipping_address', array($this, 'woo_order_formatted_shipping_address'), 20, 2);

		add_filter('woocommerce_form_field_hidden', array($this, 'woo_form_field_hidden'), 10, 4);
		add_filter('woocommerce_form_field_heading', array($this, 'woo_form_field_heading'), 10, 4);
		add_filter('woocommerce_form_field_label', array($this, 'woo_form_field_label'), 10, 4);
		add_filter('woocommerce_form_field_textarea', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_checkbox', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_checkboxgroup', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_password', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_text', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_email', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_tel', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_number', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_select', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_multiselect', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_radio', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_datepicker', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_timepicker', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_file', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_file_default', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_url', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_datetime_local', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_date', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_time', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_month', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_week', array($this, 'woo_form_field'), 10, 4);
		add_filter('woocommerce_form_field_paragraph', array($this, 'woo_form_field_paragraph'), 10, 4);


		if($this->get_setting_value($advanced_settings, 'enable_conditions_country') === 'yes'){
			$hp_fc = apply_filters('thwcfe_woocommerce_form_field_country_hook_priority', 10);
			add_filter('woocommerce_form_field_country', array($this, 'woo_form_field'), $hp_fc, 4);
		}
		if($this->get_setting_value($advanced_settings, 'enable_conditions_state') === 'yes'){
			add_filter('woocommerce_form_field_state', array($this, 'woo_form_field'), 10, 4);
		}

		add_action('wp_ajax_thwcfe_file_upload', array($this, 'ajax_file_upload'));
		add_action('wp_ajax_nopriv_thwcfe_file_upload', array($this, 'ajax_file_upload'));

		add_action('wp_ajax_thwcfe_remove_uploaded', array($this, 'ajax_remove_uploaded'));
		add_action('wp_ajax_nopriv_thwcfe_remove_uploaded', array($this, 'ajax_remove_uploaded'));

		add_action( 'wp_ajax_nopriv_append_country_prefix_in_billing_phone', array($this,'ajax_country_prefix_in_billing_phone'));
		add_action( 'wp_ajax_append_country_prefix_in_billing_phone', array($this,'ajax_country_prefix_in_billing_phone'));

		add_filter('thwcfe_form_field_wrapper_attributes', array($this, 'form_field_wrapper_attributes'), 10, 3);		
	}
/**** Country code feature
	// function ajax_country_prefix_in_billing_phone(){

	//     $billing_calling_code = '';
	//     $billing_country_code = isset( $_POST['billing_country_code'] ) ? $_POST['billing_country_code'] : '';
	//     if( $billing_country_code ){
	//         $billing_calling_code = WC()->countries->get_country_calling_code( $billing_country_code );
	//         $billing_calling_code = is_array( $billing_calling_code ) ? $billing_calling_code[0] : $billing_calling_code;

 //    	}
 //    	echo $billing_calling_code;
 //    	die();
 //    }
****/
	public function wcfe_add_error($msg, $errors=false){
		if($errors){
			$errors->add('validation', $msg);
		}else if(defined('WC_VERSION') && version_compare(WC_VERSION, '2.3.0', '>=')){
			wc_add_notice($msg, 'error');
		} else {
			WC()->add_error($msg);
		}
	}

	public function get_dp_prevent_close_onselect(){
		$flag = apply_filters('thwcfe_date_picker_prevent_popup_close_onselect', false);
		return is_bool($flag) ? $flag : false;
	}

	private  function get_locale_override_value($key, $settings=false, $default=false){
		$value = '';

		if($settings){
			$value = THWCFE_Utils::get_setting_value($settings, $key);
		}else{
			$value = THWCFE_Utils::get_settings($key);
		}

		return $value === 'undefined' ? $default : $value;
	}

	public function is_override_label($settings=false, $country=false){
		$override_label = $this->get_locale_override_value('enable_label_override', $settings, true);
		$override_label = $override_label ? true : false;
		return apply_filters('thwcfe_address_field_override_label', $override_label, $country);
	}

	public function is_override_placeholder($settings=false, $country=false){
		$override_ph = $this->get_locale_override_value('enable_placeholder_override', $settings, true);
		$override_ph = $override_ph ? true : false;
		return apply_filters('thwcfe_address_field_override_placeholder', $override_ph, $country);
	}

	public function is_override_class($settings=false, $country=false){
		$override_class = $this->get_locale_override_value('enable_class_override', $settings, false);
		$override_class = $override_class ? true : false;
		return apply_filters('thwcfe_address_field_override_class', $override_class, $country);
	}

	public function is_override_priority($settings=false, $country=false){
		$override_priority = $this->get_locale_override_value('enable_priority_override', $settings, true);
		$override_priority = $override_priority ? true : false;
		return apply_filters('thwcfe_address_field_override_priority', $override_priority, $country);
	}

	public function is_override_required_prop($settings=false, $country=false){
		$override_required = $this->get_locale_override_value('enable_required_override', $settings, false);
		$override_required = $override_required ? true : false;
		return apply_filters('thwcfe_address_field_override_required', $override_required, $country);
	}

	public function prepare_address_fields($fieldset, $country, $original = false, $sname = 'billing'){
		if(apply_filters('thwcfe_skip_address_fields_override_with_locale', false)){
			return $fieldset;
		}

		$locale = WC()->countries->get_country_locale();
		$override_required_prop = $this->is_override_required_prop();

		if($override_required_prop && is_array($fieldset)){
			foreach($fieldset as $name => $field) {
				if(isset($fieldset[$name]['required']) && $fieldset[$name]['required']){
					$fieldset[$name]['class'][] = 'thwcfe-required';
				}else{
					$fieldset[$name]['class'][] = 'thwcfe-optional';
				}
			}
		}

		if(isset($locale[ $country ]) && is_array($locale[ $country ])) {
			$states = WC()->countries->get_states( $country );

			foreach($locale[ $country ] as $key => $value){
				$fname = $sname.'_'.$key;

				if(is_array($value) && isset($fieldset[$fname])){
					/*if(isset($value['required'])){
						$fieldset[$fname]['required'] = $value['required'];
					}*/

					/*if($override_required_prop){
						if(isset($fieldset[$fname]['required']) && $fieldset[$fname]['required']){
							$fieldset[$fname]['class'][] = 'thwcfe-required';
						}else{
							$fieldset[$fname]['class'][] = 'thwcfe-optional';
						}
					}*/

					if(!$override_required_prop){
						if(isset($original[$fname]['required'])){
							$fieldset[$fname]['required'] = $original[$fname]['required'];
						}
						/*if(isset($value['required'])){
							$fieldset[$fname]['required'] = $value['required'];
						}else if(isset(var)){
							$fieldset[$fname]['required'] = $value['required'];
						}*/
					}

					/*if(!$override_required_prop && isset($value['required'])){
						$fieldset[$fname]['required'] = $value['required'];
					}*/

					if($key === 'state'){
						if(is_array($states) && empty($states)){
							$fieldset[$fname]['hidden']   = true;
							$fieldset[$fname]['required'] = false;
						}
					}else{
						if(isset($value['hidden'])){
							$fieldset[$fname]['hidden']   = $value['hidden'];
							$fieldset[$fname]['required'] = false;
						}
					}
				}
			}
		}

		return $fieldset;
	}

	public function prepare_address_fields_my_account($fieldset, $original_fieldset = false, $sname = 'billing'){
		if(!empty($fieldset) && !empty($original_fieldset) && is_array($fieldset) && is_array($original_fieldset)){
			$priority = 0;
			foreach($original_fieldset as $okey => $ofield) {
				$priority = isset($ofield['priority']) && is_numeric($ofield['priority']) && $ofield['priority'] > $priority ? $ofield['priority'] : $priority;
			}

			foreach($fieldset as $key => $field) {
				$show = apply_filters('thwcfe_show_edit_address_form_field_'.$key, true, $sname, $field);

				if(isset($field['custom']) && $field['custom'] && $show){
					$priority += 10;
					$required = isset($field['required']) && $field['required'] ? true : false;
					$ftype = isset($field['type']) ? $field['type'] : 'text';
					$ftype = $ftype === 'hidden' ? 'text' : $ftype;

					$custom_field = array();
					$custom_field['type'] = $ftype;
					$custom_field['label'] = __($field['label'],'woocommerce-checkout-field-editor-pro');
					$custom_field['placeholder'] = __($field['placeholder'], 'woocommerce-checkout-field-editor-pro');
					$custom_field['class'] = $field['class'];
					$custom_field['description'] = __($field['description'],'woocommerce-checkout-field-editor-pro');
					$custom_field['label_class'] = $field['label_class'];
					$custom_field['input_class'] = $field['input_class'];
					//$custom_field['default'] = $field['default'];
					$custom_field['validate'] = $field['validate'];
					//$custom_field['required'] = $field['required'];
					$custom_field['required'] = isset($field['rules']) && !empty($field['rules']) ? false : $required;
					$custom_field['priority'] = $priority;
					$custom_field['user_meta'] = $field['user_meta'];

					if($ftype === 'select' || $ftype === 'multiselect' || $ftype === 'radio' || $ftype === 'checkboxgroup'){
						$custom_field['options'] = $field['options'];
						$custom_field['options_object'] = $field['options_object'];
					}else if($ftype === 'checkbox'){
						$custom_field['on_value'] = $field['on_value'];
						$custom_field['off_value'] = $field['off_value'];
					}

					if(isset($field['rules']) && !empty($field['rules'])){
						$custom_field['required'] = false;
						$custom_field['validate'] = '';
					}

					$original_fieldset[$key] = $custom_field;
				}
			}
		}
		return $original_fieldset;
	}

	public function validate_custom_my_account_field($field, $posted, $errors=false){
		$type = is_array($field) && isset($field['type']) ? $field['type'] : 'text';

		if($type === 'file'){
			$key = is_array($field) && isset($field['name']) ? $field['name'] : '';

			if(isset($_FILES[$key])){
				$file = $_FILES[$key];
				$result = $this->validate_file($field, $file);

				if(is_array($result) && $result['status'] === "ERROR"){
					$this->wcfe_add_error($result['error'], $errors);
				}
			}
		}else{
			$this->validate_custom_field($field, $posted, $errors);
		}
	}

    public function validate_custom_field($field, $posted, $fieldset_key='', $errors=false, $return=false){
    	$err_msgs = array();
		$key = isset($field['name']) ? $field['name'] : false;
		
		if($key){
			$flabel = isset($field['label']) ? $field['label'] : '';

			if($fieldset_key && ($fieldset_key == 'shipping' || $fieldset_key == 'billing')){
				switch ( $fieldset_key ) {
					case 'shipping':
						/* translators: %s: field label */
						$field_label = sprintf( _x( 'Shipping %s', 'woocommerce-checkout-field-editor-pro' ), $flabel );
						break;
					case 'billing':
						/* translators: %s: field label */
						$field_label = sprintf( _x( 'Billing %s', 'woocommerce-checkout-field-editor-pro' ), $flabel );
						break;
				}
			}else{
				$field_label = __($flabel, 'woocommerce-checkout-field-editor-pro');
			}

			$field_label = apply_filters('thwcfe_validation_message_field_label', $field_label, $field, $fieldset_key);

			$value = isset($posted[$key]) ? $posted[$key] : '';
			$validators = isset($field['validate']) ? $field['validate'] : '';
			$options_object = isset($field['options_object']) ? $field['options_object'] : array();
			//if($value && is_array($validators) && !empty($validators)){
			if(is_array($validators) && !empty($validators)){
				foreach($validators as $vname){

					if($vname === 'number'){
						if(!is_numeric($value)){
							$err_msgs[] = '<strong>'. $field_label .'</strong> '. __('is not a valid number.','woocommerce-checkout-field-editor-pro');
						}
					}else if($vname === 'url'){						
						if (!filter_var($value, FILTER_VALIDATE_URL)) {
							$err_msgs[] = '<strong>'. $field_label .'</strong> '. __('is not a valid url.','woocommerce-checkout-field-editor-pro');

						}
					}else{
						$custom_validators = $this->get_settings('custom_validators');
						$validator = is_array($custom_validators) && isset($custom_validators[$vname]) ? $custom_validators[$vname] : false;

						if(is_array($validator)){
							$pattern = $validator['pattern'];
							$pattern = apply_filters('thwcfe_custom_validator_pattern', $pattern, $field);
							if(preg_match($pattern, $value) === 0) {
								//$this->wcfe_add_error($value, $errors);
								$err_msgs[] = sprintf(__($validator['message'], 'woocommerce-checkout-field-editor-pro'), $flabel);
							}
						}else{
							$con_validators = $this->get_settings('confirm_validators');
							$cnf_validator = is_array($con_validators) && isset($con_validators[$vname]) ? $con_validators[$vname] : false;

							if(is_array($cnf_validator)){
								$cfield = $cnf_validator['pattern'];
								$cfield = apply_filters('thwcfe_confirm_validator_pattern', $cfield, $field);
								$cvalue = $posted[$cfield];

								if($value && $cvalue && $value != $cvalue) {
									$err_msgs[] = sprintf(__($cnf_validator['message'], 'woocommerce-checkout-field-editor-pro'), $flabel );
								}
							}
						}
					}
				}
			}

			if(!empty($field['input_mask']) && $field['type'] === 'text' || $field['type'] === 'tel'){
				$name  = $field['name'];
				$unvalidated_fields = isset($_POST['thwcfe_unvalidated_fields']) ? stripslashes($_POST['thwcfe_unvalidated_fields']) : '';
				$unvalidated_fields  = $unvalidated_fields ? explode(",", $unvalidated_fields) : array();

				if(is_array($unvalidated_fields)){
					$unvalidated_fields = array_unique($unvalidated_fields);
					foreach ($unvalidated_fields as $field_name) {

						if($field_name === $name){
							$err_msgs[] = 
								sprintf(
									/* translators: 1: Field name */
					   		 		__( '<strong>%1$s field:</strong> Please fill in the displayed format', 'woocommerce-checkout-field-editor-pro' ),
					    		$field_label
					    	);
						}
					}
				}
			}

			$minlength = isset($field['minlength']) ? $field['minlength'] : '';
			if($minlength){
				$minlength_error = false;
				$str_length_function = apply_filters('thwcfe_field_value_length_calculation_function','strlen', $field);
				if($str_length_function == 'mb_strlen'){
					$encoding = mb_internal_encoding();
					$encoding = apply_filters('thwcfe_mb_strlen_encoding', $encoding);
					if($minlength > mb_strlen($value, $encoding)){
						$minlength_error = true;
					}
				}else{
					if($minlength > strlen($value)){
						$minlength_error = true;
					}
				}

				if($minlength_error){
					$err_msgs[] = sprintf(
					    /* translators: 1: Field name 2: Minimum length */
					    __( '<strong>%1$s field:</strong> The text entered is less than the minimum length. Minimum %2$s characters are required.', 'woocommerce-checkout-field-editor-pro' ),
					    $field_label,
					    $minlength
					);
				}
			}

			$maxlength = isset($field['maxlength']) ? $field['maxlength'] : '';

			$f_type = isset($field['type']) ? $field['type'] : '';
			if((($f_type == 'multiselect') or ($f_type == 'checkboxgroup')) and $maxlength){
				$t = 0;
				$options = isset($field['options']) ? $field['options'] : array();
				$options_keys = array_keys($options);
				$valArray = explode(', ', $value);
				foreach($options_keys as $key){
					if(in_array($key, $valArray)){
						$t++;
					}
					if($t > $maxlength){
						$err_msgs[] = sprintf(
						    /* translators: 1: Field name 2: Maximum length */
						    __( '<strong>%1$s field:</strong> You can only select %2$s items.', 'woocommerce-checkout-field-editor-pro' ),
						    $field_label,
						    $maxlength
						);
						break;
					}
				}
			}else if($options_object && apply_filters('thwcfe_enable_field_alteration_validation',false)){
				$err_msgs [] = $this-> validate_options_field($field,$value);	
			}else{
				if($maxlength){
					$maxlength_error = false;
					$str_length_function = apply_filters('thwcfe_field_value_length_calculation_function','strlen', $field);
					if($str_length_function == 'mb_strlen'){
						$encoding = mb_internal_encoding();
						$encoding = apply_filters('thwcfe_mb_strlen_encoding', $encoding);
						if($maxlength < mb_strlen($value, $encoding)){
							$maxlength_error = true;
						}
					}else{
						if($maxlength < strlen($value)){
							$maxlength_error = true;
						}
					}

					if($maxlength_error){
						$err_msgs[] = sprintf(
						    /* translators: 1: Field name 2: Maximum length */
						    __( '<strong>%1$s field:</strong> The text entered exceeds the maximum length. Maximum %2$s characters are allowed.', 'woocommerce-checkout-field-editor-pro' ),
						    $field_label,
						    $maxlength
						);
					}
				}
			}
		}

		$mintime = isset($field['min_html_time']) ? $field['min_html_time'] : '';
		
		if($mintime){
			$mintime_error = false;
			if(strtotime($mintime) > strtotime($value)){
				$mintime_error = true;
			}
			if($mintime_error){
				$err_msgs[] = sprintf(
					/* translators: 1: Field name 2: Minimum time */
					__( '<strong>%1$s field:</strong> The time entered is lesser than the minimum time. Value must be %2$s or later.', 'woocommerce-checkout-field-editor-pro' ),
					$field_label,
					$mintime
				);
			}
		}

		$maxtime = isset($field['max_html_time']) ? $field['max_html_time'] : '';

		if($maxtime){
			$maxtime_error = false;
			if(strtotime($maxtime) < strtotime($value)){
				$maxtime_error = true;
			}
			if($maxtime_error){
				$err_msgs[] = sprintf(
					/* translators: 1: Field name 2: Maximum time */
					__( '<strong>%1$s field:</strong> The time entered is greater than the maximum time. Value must be %2$s or earlier.', 'woocommerce-checkout-field-editor-pro' ),
					$field_label,
					$maxtime
				);
			}
		}

		$minDate = isset($field['min_html_datetime']) ? $field['min_html_datetime'] : '';

		if($minDate){
			$mindate_error = false;
			if(strtotime($minDate) > strtotime($value)){
				$mindate_error = true;
			}
			if($mindate_error){
				$err_msgs[] = sprintf(
					/* translators: 1: Field name 2: Minimum time */
					__( '<strong>%1$s field:</strong> The date time entered is lesser than the minimum value. Value must be %2$s or later.', 'woocommerce-checkout-field-editor-pro' ),
					$field_label,
					$minDate
				);
			}
		}

		$maxDate = isset($field['max_html_datetime']) ? $field['max_html_datetime'] : '';

		if($maxDate){
			$maxdate_error = false;
			if(strtotime($maxDate) < strtotime($value)){
				$maxdate_error = true;
			}
			if($maxdate_error){
				$maxDate = str_replace("T", ",", $maxDate);
				$err_msgs[] = sprintf(
					/* translators: 1: Field name 2: Maximum datetime local */
					__( '<strong>%1$s field:</strong> The date time local entered is greater than the maximum value. Value must be %2$s or earlier.', 'woocommerce-checkout-field-editor-pro' ),
					$field_label,
					$maxDate
				);
			}
		}

		if($err_msgs){
			if($errors || !$return){
				foreach($err_msgs as $err_msg){
					$this->wcfe_add_error($err_msg, $errors);
				}
			}
		}

		return !empty($err_msgs) ? $err_msgs : false;
	}
    
	public function validate_options_field($field,$value){
		$price_info_json = urldecode($_POST['thwcfe_price_data']);
		$price_infos = json_decode($price_info_json, true);
		$options_object = isset($field['options_object']) ? $field['options_object'] : array();
		$altered = false;
		$err_msgs = '';
		foreach ($options_object as $key => $options) {
			if(isset($options['key']) && $options['key'] === $value){
				if($price_infos){
					foreach ($price_infos as $key => $price_info) {
						if($key === $field['name']){
							if( ($options['key'] === $price_info['value']) && ($options['price'] == $price_info['price']) ){
								$altered = false;	
							}else{
								$altered = true;
							}
						}
					}	
				}else{
					if(array_key_exists($value,$options_object) && $options['key'] === $value ){
						if(isset($options['price']) && $options['price']){
							$altered = true;
						}else{
							$altered = false;
						}
					}
				}	
				
			}else if(!is_array($value) && !array_key_exists($value,$options_object)){
				$altered = true;	
			}
		
		}
		if($altered){
			$err_msgs = __('An inappropriate change detected', 'woocommerce-checkout-field-editor-pro');	
		}
		return $err_msgs;			
	}

	public function validate_file($field, $file){
		$errors = array();
		$errors['status'] = 'SUCCESS';

		if($file){
			$file_type = THWCFE_Utils::get_posted_file_type($file);
			$file_size = isset($file['size']) ? $file['size'] : false;

			if($file_type && $file_size){
				$name  = isset($field['name']) ? $field['name'] : '';
				$title = isset($field['title']) ? $field['title'] : '';
				$title = __($title, 'woocommerce-checkout-field-editor-pro');
				$maxsize = isset($field['maxsize']) ? $field['maxsize'] : '';
				$accept = isset($field['accept']) ? $field['accept'] : '';
				$file_type = strtolower($file_type);

				$maxsize = apply_filters('thwcfe_file_upload_maxsize', $maxsize, $name);
				$maxsize_bytes = is_numeric($maxsize) ? $maxsize*1048576 : false;

				$accept = apply_filters('thwcfe_file_upload_accepted_file_types', $accept, $name);
				$allowed = $accept && !is_array($accept) ? array_map('trim', explode(",", $accept)) : $accept;

				if(is_array($allowed) && !empty($allowed) && !in_array($file_type, $allowed)){
					//$err_msg = '<strong>'. $title .':</strong> '. THWCFE_i18n::t( 'Invalid file type.' );
					
					$err_msg = sprintf(
							/* translators: %s: Accepted types */
						__('Invalid file type, allowed types are %s', 'woocommerce-checkout-field-editor-pro'), $accept);
					$errors['error'] = $err_msg;
					$errors['status'] = 'ERROR';

				}else if($maxsize_bytes && is_numeric($maxsize_bytes) && $file_size >= $maxsize_bytes){

					$err_msg = sprintf(
						/* translators: %s: Maximum size */
						__('Uploaded file should not exceed %sMB.','woocommerce-checkout-field-editor-pro'), $maxsize);
					$errors['error'] = $err_msg;
					$errors['status'] = 'ERROR';
				}
			}
		}
		return $errors;
	}

	public function ajax_file_upload(){
		if(! check_ajax_referer( 'file_handling_nonce', 'security' )){
			die();
		}

		$posted_data = isset($_POST) ? $_POST : array();
		$file_data = isset($_FILES) ? $_FILES : array();
		$data = array_merge($posted_data, $file_data);

		$file_response = array();

		$file = $data['file'];
		$fname = $data['field_name'];

		$fieldset = WCFE_Checkout_Fields_Utils::get_all_checkout_fields_map();
		$field = $fieldset[$fname];

		$prev_field_value = isset($data['prev_value']) ? $data['prev_value'] : '';
		$prev_field_value = stripslashes($prev_field_value);
		$prev_field_value = json_decode($prev_field_value);
		
		if( isset( $file['name'] ) && is_array( $file['name'] ) ){
			$count = count( $file['name'] );
			for( $i=0; $i < $count; $i++ ){

				$file_new = wp_list_pluck( $file, $i );
				if(is_array($file_new) && isset($file_new['name'])){

					$file_new['name'] = apply_filters('thwcfe_uploaded_file_name', $file_new['name'], $file_new, $fname, $i);

					$uploaded = $this->validate_file($field, $file_new);

					if($uploaded && $uploaded['status'] === "SUCCESS"){
						$uploaded = $this->upload_file($file_new);
					}

					$response = array();
					if($uploaded && !isset($uploaded['error'])){
						$file_size = isset($file_new['size']) ? $file_new['size'] : false;

						$response['response'] = "SUCCESS";
						$response['uploaded']['name'] = $file_new['name'];
						$response['uploaded']['url']  = $uploaded['url'];
						$response['uploaded']['file'] = $uploaded['file'];
						$response['uploaded']['type'] = $uploaded['type'];
						$response['uploaded']['size'] = $file_size;
					}else{
						$response['response'] = "ERROR";
						$response['error'] 	  = $uploaded['error'];
					}
					$file_response[$file_new['name']] = $response;
				}
			}
		}
		if( !empty($prev_field_value)){
			foreach($prev_field_value as $key => $value){
				$value_arr = (array) $value;
				$file_new['name'] = $value_arr['name'];
				$response['response'] = "SUCCESS";
				$response['uploaded']['name'] = $value_arr['name'];
				$response['uploaded']['url']  = $value_arr['url'];
				$response['uploaded']['file'] = $value_arr['file'];
				$response['uploaded']['type'] = $value_arr['type'];
				$response['uploaded']['size'] = $value_arr['size']; 

				$file_response[$file_new['name']] = $response;
			}	
		}
		$file_response = apply_filters('thwcfe_file_uploaded', $file_response);
		echo json_encode($file_response);
		die();
	}

	public function ajax_remove_uploaded(){
		if(! check_ajax_referer( 'file_handling_nonce', 'security' )){
			die();
		}

		if(isset($_POST) && isset($_POST['file']) && $_POST['file']){
			$wp_upload_dir = wp_get_upload_dir();
			$upload_base_dir = isset($wp_upload_dir['basedir']) ? $wp_upload_dir['basedir'] : '';

			// Fix for WAMP & XAMPP server in Windows machine
			$file_path = str_replace("\\\\","\\",$_POST['file']);

			if (!(strpos($file_path, $upload_base_dir) !== false)) {
				die();
			}

			$response = array();

			$user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
			$field_name = isset($_POST['field_name']) ? sanitize_key($_POST['field_name']) : '';
			$file = isset($_POST['file']) ? $_POST['file'] : '';

			$new_file_data = false;
			if($user_id){
				$new_file_data = $this->update_user_file_data($user_id, $field_name, $file);
			}

			$result = false;
			if(file_exists($file)){
				$result = unlink($file);
			}

			//$file = $_POST['file'];
			//$result = unlink($file);

			if($result){
				$response['response'] = "SUCCESS";
			}else{
				$response['response'] = "ERROR";
				$response['error'] = 'File does not exist';
			}

			$response = apply_filters('thwcfe_file_removed', $response);
			echo json_encode($response);
		}else{
			$response['response'] = "SUCCESS";
			$response['error'] = '';

			echo json_encode($response);
		}
		die();
	}

	// public function uploaded_file($file, $name='', $field=null){
	// 	$result = false;

	// 	if(is_array($file)){
	// 		$uploaded = $this->validate_file($field, $file);

	// 		if($uploaded && $uploaded['status'] === "SUCCESS"){
	// 			$uploaded = $this->upload_file($file);
	// 		}

	// 		if($uploaded && !isset($uploaded['error'])){
	// 			$file_size = isset($file['size']) ? $file['size'] : false;

	// 			$result['response'] = "SUCCESS";
	// 			$result['uploaded']['name'] = $file['name'];
	// 			$result['uploaded']['url'] = $uploaded['url'];
	// 			$result['uploaded']['file'] = $uploaded['file'];
	// 			$result['uploaded']['type'] = $uploaded['type'];
	// 			$result['uploaded']['size'] = $file_size;
	// 		}else{
	// 			$result['response'] = "ERROR";
	// 			$result['error'] = $uploaded['error'];
	// 		}
	// 	}
	// 	return $result;
	// }

	public function upload_file($file, $name='', $field=null){
		$upload = false;

		if(is_array($file)){
			if(!function_exists('wp_handle_upload')){
				require_once(ABSPATH. 'wp-admin/includes/file.php');
				require_once(ABSPATH. 'wp-admin/includes/media.php');
			}

			add_filter('upload_dir', array('WCFE_Checkout_Fields_Utils', 'upload_dir'));
			//add_filter('upload_mimes', array('THWEPO_Utils', 'upload_mimes'));
			$upload = wp_handle_upload($file, array('test_form' => false));
			remove_filter('upload_dir', array('WCFE_Checkout_Fields_Utils', 'upload_dir'));
			//remove_filter('upload_mimes', array('THWEPO_Utils', 'upload_mimes'));

			/*if($upload && !isset($upload['error'])){
				echo "File is valid, and was successfully uploaded.\n";
			} else {
				echo $upload['error'];
			}*/
		}
		return $upload;
	}

	public function update_user_file_data($user_id, $field_name, $file){
		$saved_json = get_user_meta( $user_id, $field_name, true );
		$files_arr = json_decode($saved_json, true);
		if(empty($files_arr) or !is_array($files_arr)){
			return array();
		}

		//array_search($file, array_column($files_arr, 'file'), true);
		//array_search not works. It return wrong key instead of original key
		foreach($files_arr as $key => $file_arr){
			if($file_arr['file'] === $file){
				unset($files_arr[$key]);
			}
		}

		if(!empty($files_arr)){
			$new_json = json_encode($files_arr);
		}else{
			$new_json = '';
		}
		
		update_user_meta($user_id, $field_name, $new_json);
		return $new_json;
	}

	/****************************************
	******** CUSTOM FIELD TYPES - START ****
	****************************************/
	public function skip_form_field_filter($name){
		$skip = false;
		$ignore_fields = apply_filters('thwcfe_ignore_fields', array());
		if(is_array($ignore_fields) && !empty($ignore_fields) && in_array($name, $ignore_fields)){
			$skip = true;
		}
		Return $skip;
	}

	public function output_checkout_form_hidden_fields(){
		$this->output_price_data_hidden_field();
		$this->output_disabled_field_names_hidden_field();
		$this->output_repeat_field_names_hidden_field();
		$this->output_input_mask_unvalidated_hidden_field();
	}

	public function output_price_data_hidden_field(){
		echo '<input type="hidden" id="thwcfe_price_data" name="thwcfe_price_data" value="" />';
	}

	public function output_disabled_field_names_hidden_field(){
		echo '<input type="hidden" id="thwcfe_disabled_fields" name="thwcfe_disabled_fields" value=""/>';
		echo '<input type="hidden" id="thwcfe_disabled_sections" name="thwcfe_disabled_sections" value=""/>';
	}

	public function output_repeat_field_names_hidden_field(){
		$rfields = THWCFE_Utils_Repeat::prepare_repeat_fields_json();
		$rsections = THWCFE_Utils_Repeat::prepare_repeat_sections_json();
		echo '<input type="hidden" id="thwcfe_repeat_fields" name="thwcfe_repeat_fields" value="'.$rfields.'"/>';
		echo '<input type="hidden" id="thwcfe_repeat_sections" name="thwcfe_repeat_sections" value="'.$rsections.'"/>';
	}

	public function output_input_mask_unvalidated_hidden_field(){
		echo '<input type="hidden" id="thwcfe_unvalidated_fields" name="thwcfe_unvalidated_fields" value=""/>';
	}

	public function prepare_price_data_string($args){
		$price_info = '';
		if($this->is_price_field($args)){
			$label = !empty($args['title']) ? __($args['title'],'woocommerce-checkout-field-editor-pro') : $args['name'];
			$taxable = isset($args['taxable']) ? $args['taxable'] : 'no';
			$tax_class = isset($args['tax_class']) ? $args['tax_class'] : '';

			$price_type = isset($args['price_type']) && !empty($args['price_type']) ? $args['price_type'] : 'normal';
			$price 		= isset($args['price']) && is_numeric($args['price']) ? $args['price'] : 0;
			$price_unit = isset($args['price_unit']) && !empty($args['price_unit']) ? $args['price_unit'] : 0;

			$price_info  = 'data-price="'.$price.'" data-price-type="'.$price_type.'" data-price-label="'.esc_attr($label).'" ';
			$price_info .= 'data-price-unit="'.$price_unit.'" data-taxable="'.$taxable.'" data-tax-class="'.$tax_class.'"';
		}
		return $price_info;
	}

	public function prepare_price_data_option_field_string($args){
		$price_data = '';
		$label     = isset($args['title']) && !empty($args['title']) ? __($args['title'],'woocommerce-checkout-field-editor-pro') : $args['name'];
		$taxable   = isset($args['taxable']) ? $args['taxable'] : 'no';
		$tax_class = isset($args['tax_class']) ? $args['tax_class'] : '';

		$price_data = 'data-price-label="'.esc_attr($label).'" data-taxable="'.$taxable.'" data-tax-class="'.$tax_class.'"';

		return $price_data;
	}

	public function prepare_price_data_option_string($args){
		$price_info = '';
		if( isset($args['price']) && !empty($args['price']) ){
			$price_info = 'data-price="'.$args['price'].'" data-price-type="'.$args['price_type'].'"';
		}
		return $price_info;
	}

	public function prepare_ajax_conditions_data_section($section){
		$data_str = false;
		if($section->get_property('conditional_rules_ajax_json')){
			$rules_action = $section->get_property('rules_action_ajax') ? $section->get_property('rules_action_ajax') : 'show';
			$rules = urldecode($section->get_property('conditional_rules_ajax_json'));
			$rules = esc_js($rules);

			$data_str = 'id="'.$section->name.'" data-rules="'. esc_attr($rules) .'" data-rules-action="'. $rules_action .'" data-rules-elm="section"';
		}
		return $data_str;
	}

	public function woo_form_field_heading($field, $key, $args, $value){
		if($this->skip_form_field_filter($key)){
    		return $ofield;
    	}

    	$args['class'][] = 'thwcfe-html-field-wrapper';

		//$field = '<h3 class="form-row '.esc_attr(implode(' ', $args['class'])).'" id="'.esc_attr($key).'_field">'. THWCFE_i18n::t($args['label']) .'</h3>';
		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			$rules = urldecode($args['rules']);
			$rules = esc_js($rules);
			$args['class'][] = 'thwcfe-conditional-field';
		}
		$data_rules = 'data-rules="'.esc_attr($rules).'" data-rules-action="'.$rules_action.'"';

		$title_html = $this->get_title_html($args);
		$field  = '';
		if(!empty($title_html)){
			$field .= '<div class="form-row '.esc_attr(implode(' ', $args['class'])).'" id="'.esc_attr($key).'_field" data-name="'.esc_attr($key).'" '.$data_rules.' >'. $title_html .'</div>';
		}
		return $field;

		//$field = $this->get_title_html($args);
		//return $field;
	}

	public function woo_form_field_paragraph($field, $key, $args, $value){
		if($this->skip_form_field_filter($key)){
    		return $ofield;
    	}
    	$args['class'][] = 'thwcfe-html-field-wrapper';

		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			$rules = urldecode($args['rules']);
			$rules = esc_js($rules);
			$args['class'][] = 'thwcfe-conditional-field';
		}
		$data_rules = 'data-rules="'.esc_attr($rules).'" data-rules-action="'.$rules_action.'"';

		$title_html = '';
		if(isset($args['label']) && !empty($args['label'])){
			$title_style = isset($args['title_color']) && !empty($args['title_color']) ? 'style="display:block; color:'.$args['title_color'].';"' : 'style="display:block;"';

			$title_html .= '<p class="'. implode(' ', $args['label_class']) .'" '. $title_style .'>'. __($args['label'], 'woocommerce-checkout-field-editor-pro') .'</ p >';
		}
		$subtitle_html = '';
		if(isset($args['subtitle']) && !empty($args['subtitle'])){
			$subtitle_type  = isset($args['subtitle_type']) && !empty($args['subtitle_type']) ? $args['subtitle_type'] : 'span';
			$subtitle_style = isset($args['subtitle_color']) && !empty($args['subtitle_color']) ? 'style="color:'. $args['subtitle_color'] .';"' : '';
			$subtitle_class = isset($args['subtitle_class']) && is_array($args['subtitle_class']) ? implode(' ', $args['subtitle_class']) : $args['subtitle_class'];

			$subtitle_html .= '<'. $subtitle_type .' class="'. $subtitle_class .'" '. $subtitle_style .'>';
			$subtitle_html .= __($args['subtitle'],'woocommerce-checkout-field-editor-pro') .'</'. $subtitle_type .'>';
		}

		if(!empty($subtitle_html)){
			$title_html .= $subtitle_html;
		}

		$field  = '';
		if(!empty($title_html)){
			$title_html = apply_filters( 'thwcfe_paragraph_change_field_value', $title_html, $key);
			$field .= '<div class="form-row '.esc_attr(implode(' ', $args['class'])).'" id="'.esc_attr($key).'_field" data-name="'.esc_attr($key).'" '.$data_rules.' >'. $title_html .'</div>';
		}
		return $field;
	}

	public function woo_form_field_label($field, $key, $args, $value){
		if($this->skip_form_field_filter($key)){
    		return $ofield;
    	}

    	$args['class'][] = 'thwcfe-html-field-wrapper';

		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			$rules = urldecode($args['rules']);
			$rules = esc_js($rules);
			$args['class'][] = 'thwcfe-conditional-field';
		}
		$data_rules = 'data-rules="'.esc_attr($rules).'" data-rules-action="'.$rules_action.'"';

		$title_html = $this->get_title_html($args);
		$field  = '';
		if(!empty($title_html)){
			$field .= '<div class="form-row '.esc_attr(implode(' ', $args['class'])).'" id="'.esc_attr($key).'_field" data-name="'.esc_attr($key).'" '.$data_rules.' >'. $title_html .'</div>';
		}
		return $field;
	}

	public function get_title_html($args){
		$title_html = '';
		if(isset($args['label']) && !empty($args['label'])){
			$title_type  = isset($args['title_type']) && !empty($args['title_type']) ? $args['title_type'] : 'label';
			$title_style = isset($args['title_color']) && !empty($args['title_color']) ? 'style="display:block; color:'.$args['title_color'].';"' : 'style="display:block;"';

			$title_html .= '<'. $title_type .' class="'. implode(' ', $args['label_class']) .'" '. $title_style .'>'. __($args['label'], 'woocommerce-checkout-field-editor-pro') .'</'. $title_type .'>';
		}

		$subtitle_html = '';
		if(isset($args['subtitle']) && !empty($args['subtitle'])){
			$subtitle_type  = isset($args['subtitle_type']) && !empty($args['subtitle_type']) ? $args['subtitle_type'] : 'span';
			$subtitle_style = isset($args['subtitle_color']) && !empty($args['subtitle_color']) ? 'style="color:'. $args['subtitle_color'] .';"' : '';
			$subtitle_class = isset($args['subtitle_class']) && is_array($args['subtitle_class']) ? implode(' ', $args['subtitle_class']) : $args['subtitle_class'];

			$subtitle_html .= '<'. $subtitle_type .' class="'. $subtitle_class .'" '. $subtitle_style .'>';
			$subtitle_html .= __($args['subtitle'], 'woocommerce-checkout-field-editor-pro') .'</'. $subtitle_type .'>';
		}

		$html = $title_html;
		if(!empty($subtitle_html)){
			$html .= $subtitle_html;
		}

		return $html;
	}

	public function file_remove_button_html($key, $value, $args){
		$html = '';
		$type = isset($args['type']) ? $args['type'] : '';

		if($type === 'file'){
			$disp_name = '';

			if($value){
				$value = str_replace('\\','\\\\',$value);
				$value_arr = json_decode($value, true);
				$value = is_array($value_arr) && isset($value_arr['name']) ? $value_arr['name'] : '';

				$disp_name = WCFE_Checkout_Fields_Utils::get_file_display_name($value_arr);
			}
			$display = $disp_name ? 'block' : 'none';

			$html .= '<span class="thwcfe-uloaded-files" style="display:'.$display.';">';
			$html .= '<span class="thwcfe-upload-preview" style="margin-right:15px;">'.$disp_name.'</span>';
			$html .= '</span>';
			$html .= '<span class="thwcfe-file-upload-status" style="display:none;"><img src="'.THWCFE_ASSETS_URL_PUBLIC.'css/loading.gif" style="width:32px;"/></span>';
			$html .= '<span class="thwcfe-file-upload-msg" style="display:none; color:red;"></span>';
		}
		return $html;
	}

	public function file_change_button_html($key, $disp_name, $args){
		$display = $disp_name ? '' : 'none';

		$html  = '<span class="thwcfe-uloaded-files" style="display:'.$display.';">';
		$html .= '<span class="thwcfe-upload-preview" style="margin-right:15px;">'.$disp_name.'</span>';
		$html .= '<span onclick="thwcfeChangeUploaded(this, event)" class="thwcfe-remove-uploaded" title="Change uploaded" style="cursor:pointer; display:'.$display.'; color:red;">Change</span>';
		$html .= '</span>';

		return $html;
	}

	public function form_field_wrapper_attributes($attributes, $key, $args){
		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			$rules = urldecode($args['rules']);
			$rules = esc_js($rules);
			$args['class'][] = 'thwcfe-conditional-field';

			$attributes[] = 'data-rules="'. esc_attr($rules) .'"';
			$attributes[] = 'data-rules-action="'. $rules_action .'"';
		}

		return $attributes;
	}

	/**
     * Outputs a checkout/address form field.
     *
     * @subpackage  Forms
     * @param string $key
     * @param mixed $args
     * @param string $value (default: null)
     * @todo This function needs to be broken up in smaller pieces
     */
    public function woo_form_field($ofield, $key, $args, $value = null ) {
    	if($this->skip_form_field_filter($key)){
    		return $ofield;
    	}

        $defaults = array(
            'type'              => 'text',
            'label'             => '',
            'description'       => '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            'autocomplete'      => false,
            'id'                => $key,
            'class'             => array(),
            'label_class'       => array(),
            'input_class'       => array(),
            'return'            => false,
            'options'           => array(),
            'custom_attributes' => array(),
            'validate'          => array(),
            'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
        );

        $value = is_string($value) ? html_entity_decode($value) : $value;
		$value = apply_filters( 'thwcfe_woocommerce_form_field_value_'.$key, $value ); //Deprecated
		$value = apply_filters( 'thwcfe_woocommerce_form_field_value', $value, $key );

        $args = wp_parse_args( $args, $defaults );
		$args['name'] = $key;
        $args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

		if(isset($args['label'])){
			$args['label'] = __($args['label'], 'woocommerce-checkout-field-editor-pro');
			$args['label'] = stripslashes($args['label']);		
			$rn = false;

			$r_exp  = isset($args['repeat_rules']) ? $args['repeat_rules'] : false;
			if($r_exp){
				$rn = is_numeric($rn) ? $rn : THWCFE_Utils_Repeat::get_repeat_times($r_exp, $key);
				$title = $args['label'];
				if((apply_filters('thwcfe_field_name_edit_repeat_rule',false) && $rn == 1)) {
					if(preg_match('/\d/', $title)){
						$title = preg_replace('/\d/', '', $title);

						$args['label'] = $title;
					}
				}
			}
		}

		if(isset($args['description'])){
			$args['description'] = __($args['description'], 'woocommerce-checkout-field-editor-pro');
		}

		if(isset($args['placeholder'])){
			$args['placeholder'] = __($args['placeholder'], 'woocommerce-checkout-field-editor-pro');
		}

		$args['input_class'][] = 'thwcfe-input-field';
		$args['class'][] = 'thwcfe-input-field-wrapper';
		$validations = array();

		$required = '';
        if ( $args['required'] ) {
        	$validations[]   = 'validate-required';
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
		}

        if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

        if ( is_null( $value ) || (is_string($value) && $value === '')) {
        	$value = $args['default'];	
		}

        // Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] && is_numeric($args['maxlength']) ) {
			if(isset($args['type']) && $args['type'] === 'multiselect'){
				$args['custom_attributes']['data-maxselections'] = absint( $args['maxlength'] );
			}else{
				$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
			}
		}
		if ( isset($args['minlength'])  && $args['minlength'] && is_numeric($args['minlength']) )  {
			
			$args['custom_attributes']['minlength'] = absint( $args['minlength'] );
		}

		$disable_autocomplete = apply_filters('thwcfe_disable_checkout_field_autocomplete', false, $key);
		$autocomplete = $disable_autocomplete ? 'off' : $args['autocomplete'];
		$autocomplete = $autocomplete ? $autocomplete : 'off';
		$args['custom_attributes']['autocomplete'] = $autocomplete;

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( (isset($args['readonly']) && true === $args['readonly']) || true === apply_filters('thwcfe_is_readonly_field_'.$key, false)) {
			$args['custom_attributes']['readonly'] = 'readonly';
		}

        if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

        if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			if(is_string($args['rules'])){
				$rules = urldecode($args['rules']);
            }
			$rules = esc_js($rules);
			$args['class'][] = 'thwcfe-conditional-field';
		}

        $field           = '';
		$label_id        = $args['id'];
		$validations_str = implode(" ", $validations);
		$sort            = is_numeric($args['priority']) ? $args['priority'] : '';
        $field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '" data-rules="'. esc_attr($rules) .'" data-rules-action="'.$rules_action.'" data-validations="'.$validations_str.'">%3$s</p>';
        
        switch ( $args['type'] ) {
            case 'country' :
                $field .= $this->woo_form_field_fragment_country( $key, $args, $value, $custom_attributes );
                break;

            case 'state' :
				$field .= $this->woo_form_field_fragment_state( $key, $args, $value, $custom_attributes );
                break;

            case 'textarea' :
				$field .= $this->woo_form_field_fragment_textarea( $key, $args, $value, $custom_attributes );
                break;

            case 'checkbox' :
                $field = $this->woo_form_field_fragment_checkbox( $key, $args, $value, $custom_attributes, $required );
                break;

			case 'checkboxgroup' :
                $field = $this->woo_form_field_fragment_checkboxgroup( $key, $args, $value, $custom_attributes, $required );
                break;

            case 'password' :
            case 'text' :
            case 'email' :
            case 'tel' :
            case 'number' :
                $field .= $this->woo_form_field_fragment_general( $key, $args, $value, $custom_attributes );
                break;

            case 'select' :
				$field .= $this->woo_form_field_fragment_select( $key, $args, $value, $custom_attributes );
                break;

			case 'multiselect' :
				$field .= $this->woo_form_field_fragment_multiselect( $key, $args, $value, $custom_attributes );
                break;

            case 'radio' :
				$label_id = current( array_keys( $args['options'] ) );
				$field .= $this->woo_form_field_fragment_radio( $key, $args, $value, $custom_attributes);
                break;

			case 'datepicker' :
				$field .= $this->woo_form_field_fragment_datepicker( $key, $args, $value, $custom_attributes );
                break;

			case 'timepicker' :
				$field .= $this->woo_form_field_fragment_timepicker( $key, $args, $value, $custom_attributes );
                break;

			case 'file' :
				$field .= $this->woo_form_field_fragment_file( $key, $args, $value, $custom_attributes );
                break;

            case 'file_default' :
				$field .= $this->woo_form_field_fragment_file_default( $key, $args, $value, $custom_attributes );
                break;
            case 'url' :
				$field .= $this->woo_form_field_fragment_url( $key, $args, $value, $custom_attributes );
                break;
            case 'datetime_local' :
            	$field .= $this->woo_form_field_fragment_datetime_local( $key, $args, $value, $custom_attributes );
                break;
            case 'date' :
            	$field .= $this->woo_form_field_fragment_date( $key, $args, $value, $custom_attributes );
                break;
            case 'time' :
            	$field .= $this->woo_form_field_fragment_time( $key, $args, $value, $custom_attributes );
                break;
            case 'month':
            	$field .= $this->woo_form_field_fragment_month( $key, $args, $value, $custom_attributes );
                break;
            case 'week' :
            	$field .= $this->woo_form_field_fragment_week( $key, $args, $value, $custom_attributes );
            	break;
        }

        if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field_html .= '<span class="woocommerce-input-wrapper">';

			$desc_html = '';
			if($args['description']){
				$desc_class = apply_filters('thwcfe_woocommerce_form_field_description_class', array('description'), $key);

				$desc_html = '<span class="' . esc_attr(implode(' ', $desc_class)) . '" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . $args['description'] . '</span>';
			}

			if(apply_filters('thwcfe_display_field_description_below_label', false, $key)){
				if ( $desc_html ) {
					$field_html .= $desc_html;
					if($args['type'] === 'radio' || $args['type'] === 'checkboxgroup' || $args['type'] === 'file'){
						$field_html .= '<br/>';
					}
				}

				$field_html .= $this->prepare_file_preview_html($value, $args['type']);
				$field_html .= $field;				
				//$field_html .= $this->file_remove_button_html($key, $value, $args);
			}else{
				$field_html .= $this->prepare_file_preview_html($value, $args['type']);
				$field_html .= $field;
				//$field_html .= $this->file_remove_button_html($key, $value, $args);

				if ( $desc_html ) {
					if($args['type'] === 'radio' || $args['type'] === 'checkboxgroup' || $args['type'] === 'file'){
						$field_html .= '<br/>';
					}
					$field_html .= $desc_html;
				}
			}

			if ( in_array("thwcfe-char-count", $args['input_class']) || in_array("thwcfe-char-left", $args['input_class']) ) {
                $field_html .= '<span id="'.$args['id'].'-char-count" class="thpl-char-count" style="float: right;"></span><div class="clear"></div>';
            }

            $field_html .= '</span>';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
			
			return apply_filters('thwcfe_field_html', $field, $key, $args);
        }

		return $ofield;
    }

	public function woo_form_field_fragment_country( $key, $args, $value, $custom_attributes ) {
		$field = '';

		$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

		if ( 1 === count( $countries ) ) {

			$field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

			$field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ', $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';

		} else {

			if (isset($args['placeholder'])) {
		    	$custom_attributes[] = 'data-placeholder="' . esc_attr($args['placeholder']) . '"';
		    }

			$field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state country_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . '><option value="">' . esc_html__( 'Select a country / region&hellip;', 'woocommerce' ) . '</option>';

			foreach ( $countries as $ckey => $cvalue ) {
				$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
			}

			$field .= '</select>';

			$field .= '<noscript><button type="submit" name="woocommerce_checkout_update_totals" value="' . esc_attr__( 'Update country / region', 'woocommerce' ) . '">' . esc_html__( 'Update country / region', 'woocommerce' ) . '</button></noscript>';

		}

		return $field;
	}

	public function woo_form_field_fragment_state( $key, $args, $value, $custom_attributes ) {
		$field = '';

		/* Get country this state field is representing */
		$for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
		$states      = WC()->countries->get_states( $for_country );

		if ( is_array( $states ) && empty( $states ) ) {

			$field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';

			$field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

		} elseif ( ! is_null( $for_country ) && is_array( $states ) ) {

			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '">
				<option value="">' . esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

			foreach ( $states as $ckey => $cvalue ) {
				$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
			}

			$field .= '</select>';

		} else {

			$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

		}

		return $field;
	}

	public function woo_form_field_fragment_textarea( $key, $args, $value, $custom_attributes ) {
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		$field  = '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" ';
		$field .= 'placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
		$field .= ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '');
		$field .= ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '');
		$field .= implode( ' ', $custom_attributes ) .' '.$price_info.'>'. esc_textarea( $value ) .'</textarea>';

		return $field;
	}

	public function woo_form_field_fragment_checkbox( $key, $args, $value, $custom_attributes, $required ) {
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		/*$args['default'] = !empty($args['default']) ? $args['default'] : 1;
		$checked = (isset($args['checked']) && $args['checked']) ? 'checked' : '';*/

		$on_value = !empty($args['on_value']) ? $args['on_value'] : 1;
		if($value){
			$value = $on_value;
		}
		if(is_user_logged_in() && isset($args['user_meta']) && $args['user_meta']){
			$checked = checked( $value, $on_value, false );
		}else{
			$checked = checked( $value, $on_value, false );
			if(!$checked){
				$checked = (isset($args['checked']) && $args['checked']) ? 'checked="checked"' : '';
			}
		}

		$field  = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';
        $field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ). '" value="'. $on_value .'" '. $checked .' '.$price_info.' /> '. $args['label'] . $required . '</label>';

		return $field;
	}

	public function woo_form_field_fragment_checkboxgroup( $key, $args, $value, $custom_attributes, $required ) {
		$field = '';
		if(!empty($args['options_object'])) {
			$options_list = apply_filters( 'thwcfe_input_field_options_'.$key, $args['options_object'] );
			$options_per_line = apply_filters('thwcfe_checkboxgroup_options_per_line', 1, $key);
			$is_price_field = $this->is_price_option($options_list);

			//$value = empty($value) ? $args['default'] : $value;
			$value = is_array($value) ? $value : explode(',', $value);
			$value = !empty($value) ? array_map('trim', $value) : $value;

			$index = 1;
			foreach($options_list as $option) {
				$option_key = $option['key'];
				$option_text = __($option['text'], 'woocommerce-checkout-field-editor-pro');

				$price_info = $this->prepare_price_data_option_string($option);
				$price_data = '';
				$args['input_class'] = THWCFE_Utils::remove_by_value('thwcfe-price-field', $args['input_class']);

				if($is_price_field){
					$args['input_class'][] = 'thwcfe-price-field';
					$price_data = $this->prepare_price_data_option_field_string($args);
				}

				/*if( isset($option['price']) && !empty($option['price']) ){
					$args['input_class'][] = 'thwcfe-price-field';

					//$label = !empty($args['title']) ? THWCFE_i18n::t($args['title']) : $args['name'];
					//$price_data = 'data-price-label="'.esc_attr($label).'"';
					$price_data = $this->prepare_price_data_option_field_string($args);
				}*/

				$checked = in_array($option_key, $value) ? 'checked="checked"' : '';

				$field .= '<label for="'. esc_attr($args['id']) .'_'. esc_attr($option_key) .'" style="display:inline; margin-right: 10px;" ';
				$field .= 'class="checkbox ' . implode( ' ', $args['label_class'] ) .'" '. implode( ' ', $custom_attributes ) .'>';
        		$field .= '<input type="checkbox" data-multiple="1" class="input-checkbox '. esc_attr(implode(' ', $args['input_class'])) .'" name="'. esc_attr($key) .'[]" ';
				$field .= $price_info.' '.$price_data.' ';
				$field .= 'id="' .esc_attr($args['id']) .'_'. esc_attr($option_key). '" value="'. $option_key .'" '. $checked .' /> '. $option_text .'</label>';

				if(is_array($args['class']) && in_array("valign", $args['class'])){
					$breakline = (is_numeric($options_per_line) && $options_per_line > 0 && fmod($index, $options_per_line) == 0) ? true : false;
					$field .= $breakline ? '<br/>' : '';
				}

				$index++;
			}
		}
		return $field;
	}

	public function woo_form_field_fragment_general( $key, $args, $value, $custom_attributes ) {
		$price_info = $this->prepare_price_data_string($args);
		$input_mask = '';
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if(isset($args['input_mask']) && !empty($args['input_mask'])){
			$args['input_class'][] = 'thwcfe-mask-input';
			$input_mask = $args['input_mask'];
		}
		// if(isset($args['enable_country_code']) && $args['enable_country_code']){
		// 	$args['input_class'][] = 'thwcfe-country-code';	
		// }
		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '" data-mask-pattern="'. esc_attr($input_mask) .'" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $value ) . '" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_hidden($field, $key, $args, $value){
		if($this->skip_form_field_filter($key)){
    		return $ofield;
    	}

		$price_info = $this->prepare_price_data_string($args);

		$css_class = array();
		$css_class[] = 'thwcfe-input-field';
		if($this->is_price_field($args)){
			$css_class[] = 'thwcfe-price-field';
		}

		$value = apply_filters( 'thwcfe_woocommerce_form_field_value_'.$key, $value ); //Deprecated
		$value = apply_filters( 'thwcfe_woocommerce_form_field_value', $value, $key );
		if(is_null($value) || (is_string($value) && $value === '')){
            $value = $args['default'];
        }

		$rules = '';
		$rules_action = '';
		if(isset($args['rules']) && !empty($args['rules'])){
			$rules_action = isset($args['rules_action']) ? $args['rules_action'] : 'show';
			$rules = urldecode($args['rules']);
			$rules = esc_js($rules);
			$css_class[] = 'thwcfe-conditional-field';
		}

		$field  = '<input type="hidden" id="'. esc_attr($key) .'" name="'. esc_attr($key) .'" value="'. esc_attr( $value ) .'" ';
		$field .= 'class="'.esc_attr(implode(' ', $css_class)).'" '.$price_info.' data-rules="'. esc_attr($rules) .'" data-rules-action="'.$rules_action.'" />';
		return $field;
	}

	public function woo_form_field_fragment_select( $key, $args, $value, $custom_attributes ) {
		$field   = '';
		$options = '';

		if(!empty($args['options_object'])){
			$show_price = apply_filters('thwcfe_display_field_option_price', true, $key, 'select');
			//$options_list = apply_filters( 'thwcfe_input_field_options_'.$key, $args['options_object'] ); //DEPRECATED 26-03-2018
			$options_list = apply_filters( 'thwcfe_input_field_options', $args['options_object'], $key );
			$price_field = false;

			/*if(isset($args['placeholder']) && !empty( $args['placeholder'])){
				$options .= '<option disabled="">'. esc_attr( $args['placeholder'] ) .'</option>';
			}*/

			foreach($options_list as $option){
				$option_key = $option['key'];
				$option_text = __($option['text'],'woocommerce-checkout-field-editor-pro');

				$price_info = $this->prepare_price_data_option_string($option);
				if( isset($option['price']) && !empty($option['price']) ){
					$price_field = true;

					if($show_price){
						$price_html = THWCFE_Utils_Price::get_price_html_option($key, $option);
						if(!empty($option_key) && !empty($option_text)){
							$option_text .= !empty($price_html) ? $price_html : '';
						}
					}
				}

				if ( '' === $option_key ) {
					// If we have a blank option, select2 needs a placeholder.
					if ( empty( $args['placeholder'] ) ) {
						$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
					}
					$custom_attributes[] = 'data-allow_clear="true"';
				}
				$options .= '<option value="'. esc_attr($option_key) .'" '. selected($value, $option_key, false) .' '.$price_info.' >'. esc_attr( $option_text ) .'</option>';
			}

			$price_data = '';
			if($price_field){
				$args['input_class'][] = 'thwcfe-price-field';
				$args['input_class'][] = 'thwcfe-price-option-field';

				//$label = !empty($args['title']) ? THWCFE_i18n::t($args['title']) : $args['name'];
				//$price_data = 'data-price-label="'.esc_attr($label).'"';
				$price_data = $this->prepare_price_data_option_field_string($args);
			}
			if($this->get_settings('disable_select2_for_select_fields') != 'yes'){
				if(!$args['disable_select2']){
					$args['input_class'][] = 'thwcfe-enhanced-select';
				}
			}

			$field .= '<select name="'.esc_attr($key).'" id="'.esc_attr($args['id']).'" class="select '.esc_attr(implode(' ', $args['input_class'])).'" ';
			$field .= implode(' ', $custom_attributes) .' data-placeholder="'. esc_attr($args['placeholder']) .'" '.$price_data.'>'. $options .'</select>';
		}
		return $field;
	}

	public function woo_form_field_fragment_multiselect( $key, $args, $value, $custom_attributes ) {
		$options = $field = '';

		if(!empty($args['options_object'])){
			$options_list = apply_filters( 'thwcfe_input_field_options_'.$key, $args['options_object'] );

			$price_field = false;
			$value = is_array($value) ? $value : explode(',', $value);
			$value = !empty($value) ? array_map('trim', $value) : $value;

			foreach($options_list as $option){
				$option_key = $option['key'];
				$option_text = __($option['text'], 'woocommerce-checkout-field-editor-pro');

				$selected = in_array($option_key, $value) ? 'selected="selected"' : '';

				$price_info = $this->prepare_price_data_option_string($option);
				if( isset($option['price']) && !empty($option['price']) ){
					$price_field = true;
				}

				if ( '' === $option_key ) {
					// If we have a blank option, select2 needs a placeholder.
					if(empty( $args['placeholder'])) {
						$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
					}
					$custom_attributes[] = 'data-allow_clear="true"';
				}
				$options .= '<option value="'. esc_attr($option_key) .'" '. $selected .' '.$price_info.' >'. esc_attr( $option_text ) .'</option>';
			}

			$price_data = '';
			if($price_field){
				$args['input_class'][] = 'thwcfe-price-field';
				$args['input_class'][] = 'thwcfe-price-option-field';

				//$label = !empty($args['title']) ? THWCFE_i18n::t($args['title']) : $args['name'];
				//$price_data = 'data-price-label="'.esc_attr($label).'"';
				$price_data = $this->prepare_price_data_option_field_string($args);
			}

			if($this->get_settings('disable_select2_for_select_fields') != 'yes'){
				if(!$args['disable_select2']){
					$args['input_class'][] = 'thwcfe-enhanced-multi-select';
				}
			}

			$field .= '<select multiple="multiple" name="'. esc_attr($key) .'[]" id="'. esc_attr($args['id']) .'" ';
			$field .= 'class="'. esc_attr(implode(' ', $args['input_class'])) .'" ';
			$field .= implode(' ', $custom_attributes) .' data-placeholder="'. esc_attr($args['placeholder']) .'" '.$price_data.'>'. $options .'</select>';
		}
		return $field;
	}

	public function woo_form_field_fragment_radio( $key, $args, $value, $custom_attributes) {
		$field = '';
		$show_price = apply_filters('thwcfe_display_field_option_price', true, $key, 'select');
		$price_field = false;
		if(!empty($args['options_object'])) {
			$options_list = apply_filters( 'thwcfe_input_field_options_'.$key, $args['options_object'] );

			$is_price_field = $this->is_price_option($options_list);

			foreach($options_list as $option) {
				$option_key = $option['key'];
				$option_text = __($option['text'],'woocommerce-checkout-field-editor-pro');

				$price_info = $this->prepare_price_data_option_string($option);

				if( isset($option['price']) && !empty($option['price']) ){
					$price_field = true;
					if($show_price){
						$price_html = THWCFE_Utils_Price::get_price_html_option($key, $option);
						if(!empty($option_key) && !empty($option_text)){
							$option_text .= !empty($price_html) ? $price_html : '';
						}
					}
				}

				$price_data = '';
				//if( isset($option['price']) && !empty($option['price']) ){
				if($is_price_field){
					$args['input_class'][] = 'thwcfe-price-field';

					//$label = !empty($args['title']) ? THWCFE_i18n::t($args['title']) : $args['name'];
					//$price_data = 'data-price-label="'.esc_attr($label).'"';
					$price_data = $this->prepare_price_data_option_field_string($args);
				}

				$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" ';
				$field .= $price_info.' '.$price_data.' ';
				$field .= 'name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
				$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" ';
				$field .= 'class="radio ' . implode( ' ', $args['label_class'] ) . '" style="display:inline; margin-right: 10px;"> '. $option_text .'</label>';

				if(in_array("valign", $args['class'])){
					$field .= '<br/>';
				}
			}
		}
		return $field;
	}

	public function woo_form_field_fragment_datepicker( $key, $args, $value, $custom_attributes ) {
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		$dateFormat = isset($args['date_format']) ? $args['date_format'] : $this->get_jquery_date_format(wc_date_format());
		$defaultDate = isset($args['default_date']) ? $args['default_date'] : '';
		$maxDate = isset($args['max_date']) ? $args['max_date'] : '';
		$minDate = isset($args['min_date']) ? $args['min_date'] : '';
		$yearRange = isset($args['year_range']) ? $args['year_range'] : '-100:+1';
		$numberOfMonths = isset($args['number_months']) ? $args['number_months'] : 1;
		$disabledDays = isset($args['disabled_days']) ? $args['disabled_days'] : '';
		$disabledDates = isset($args['disabled_dates']) ? $args['disabled_dates'] : '';

		$minDate = apply_filters( 'thwcfe_min_date_date_picker_'.$key, $minDate );
		$maxDate = apply_filters( 'thwcfe_max_date_date_picker_'.$key, $maxDate );
		$disabledDays = apply_filters( 'thwcfe_disabled_days_date_picker_'.$key, $disabledDays );
		$disabledDates = apply_filters( 'thwcfe_disabled_dates_date_picker_'.$key, $disabledDates );
		
		$wp_start_of_week = get_option('start_of_week', 0);
		$firstDay = apply_filters( 'thwcfe_date_picker_first_day', $wp_start_of_week, $key );

		$field  = '<input type="text" class="thwcfe-checkout-date-picker input-text '. esc_attr(implode(' ', $args['input_class'])) .'" name="'. esc_attr($key) .'" ';
		$field .= 'id="'. esc_attr($args['id']) .'" placeholder="'. esc_attr($args['placeholder']) .'" value="'. esc_attr($value) .'" ';
		$field .= implode(' ', $custom_attributes) .' '.$price_info.' ';
		$field .= 'data-date-format="'. $dateFormat .'" data-default-date="'. $defaultDate .'" data-max-date="'. $maxDate .'" data-min-date="'. $minDate .'" ';
		$field .= 'data-year-range="'. $yearRange .'" data-number-months="'. $numberOfMonths .'" data-first-day="'. $firstDay .'" ';
		$field .= 'data-disabled-days="'. $disabledDays .'" data-disabled-dates="'. $disabledDates .'" />';

		return $field;
	}

	public function woo_form_field_fragment_timepicker( $key, $args, $value, $custom_attributes ) {
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		$args['min_time']  = isset($args['min_time']) ? $args['min_time'] : '';
		$args['max_time']  = isset($args['max_time']) ? $args['max_time'] : '';
		$args['start_time']  = isset($args['start_time']) ? $args['start_time'] : '';
		$args['time_step'] = isset($args['time_step']) ? $args['time_step'] : '';
		$args['time_format'] = isset($args['time_format']) ? $args['time_format'] : '';
		$args['linked_date'] = isset($args['linked_date']) ? $args['linked_date'] : '';
		$args['disable_time_slot'] = isset($args['disable_time_slot']) ? $args['disable_time_slot'] : '';

		$args['min_time'] = apply_filters( 'thwcfe_min_time_time_picker_'.$key, $args['min_time'] );
		$args['max_time'] = apply_filters( 'thwcfe_max_time_time_picker_'.$key, $args['max_time'] );
		$args['start_time'] = apply_filters( 'thwcfe_start_time_time_picker_'.$key, $args['start_time'] );
		$args['time_step'] = apply_filters( 'thwcfe_time_step_time_picker_'.$key, $args['time_step'] );
		$args['linked_date'] = apply_filters( 'thwcfe_linked_date_time_picker_'.$key, $args['linked_date'] );

		if(!empty($args['linked_date'])){
			$args['input_class'][] = 'thwcfe-linked-date-'.$args['linked_date'];
		}

		$field  = '<input type="text" class="thwcfe-checkout-time-picker input-text '. esc_attr(implode(' ', $args['input_class'])) .'" name="'. esc_attr($key) .'" ';
		$field .= 'id="'. esc_attr($args['id']) .'" placeholder="'. esc_attr($args['placeholder']) .'" value="'. esc_attr($value) .'" ';
		$field .= implode(' ', $custom_attributes) .' '.$price_info.' data-start-time="'.$args['start_time'].'" data-disable-time="'.$args['disable_time_slot'].'"data-linked-date="'.$args['linked_date'].'" ';
		$field .= 'data-min-time="'.$args['min_time'].'" data-max-time="'.$args['max_time'].'" data-step="'.$args['time_step'].'" data-format="'.$args['time_format'].'" />';

		return $field;
	}

	public function woo_form_field_fragment_file( $key, $args, $value, $custom_attributes ) {
		$price_info = $this->prepare_price_data_string($args);
		$value_json = esc_attr($value);

		$hinput_class = array();
		$hinput_class[] = 'thwcfe-input-field';
		if($this->is_price_field($args)){
			$hinput_class[] = 'thwcfe-price-field';
		}

		$input_class = $args['input_class'];
		if(($ckey = array_search('thwcfe-input-field', $input_class)) !== false){
		    unset($input_class[$ckey]);
		}

		$field = '';
		$custom_file_field_attr = '';

		$file_name = array();

		if($value){
			$value = str_replace('\\','\\\\',$value);
			$value_arr = json_decode($value, true);
			// $value = is_array($value_arr) && isset($value_arr['name']) ? $value_arr['name'] : '';

			if(is_array($value_arr)){
				$file_name = wp_list_pluck($value_arr, 'name');
				$custom_file_field_attr = 'style="display:none;"';
			}
		}

		$custom_file_field_attr .= ' multiple';
		$file_upload_btn_text = 'Upload a File';
		$file_upload_btn_text = apply_filters('thwcfe_change_file_upload_btn_text', $file_upload_btn_text, $key);

		$field .= '<input type="hidden" class="thwcfe-checkout-file-value input-text '.esc_attr(implode(' ', $hinput_class)) .'" name="'.esc_attr($key).'" id="'.esc_attr($args['id']).'" '.$price_info.' value="'.$value_json.'" ';
		$field .= 'data-file-name="'.implode(", ",$file_name).'"';
		$field .= 'data-nonce="'. wp_create_nonce( 'file_handling_nonce' ) .'"';
		$field .= implode(' ', $custom_attributes) . ' />';
		if(isset($args['custom_btn_file_upload']) && $args['custom_btn_file_upload']){
			$field .= '<span class="upload-btn-wrapper"><button class="button thwcfe-btn-file-upload">'.__($file_upload_btn_text, 'woocommerce-checkout-field-editor-pro').'</button><input type="' . esc_attr( $args['type'] ) . '"  class="thwcfe-checkout-file thwcfe-checkout-file-btn '.esc_attr(implode(' ', $input_class)) .'" name="'. esc_attr($key) .'_file" id="'. esc_attr($args['id']) .'_file" placeholder="' . esc_attr($args['placeholder']) . '" value="" '.$custom_file_field_attr.' /></span>';	
		}else{
			$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="thwcfe-checkout-file '.esc_attr(implode(' ', $input_class)) .'" name="'. esc_attr($key) .'_file" ';
			$field .= 'id="'. esc_attr($args['id']) .'_file" placeholder="' . esc_attr($args['placeholder']) . '" value="" '.$custom_file_field_attr.' />';
		}
		
		return $field;
	}

	public function woo_form_field_fragment_file_default( $key, $args, $value, $custom_attributes ) {
		$field = '';
		$value_json = esc_attr($value);
		$args['type'] = 'file';
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		$disp_name = '';
		if($value){
			$value = str_replace('\\','\\\\',$value);
			$value_arr = json_decode($value, true);
			$value = is_array($value_arr) && isset($value_arr['name']) ? $value_arr['name'] : '';

			if($value){
				$custom_attributes[] = 'style="display:none;"';
			}

			$disp_name = WCFE_Checkout_Fields_Utils::get_file_display_name($value_arr);
			if($disp_name){
				$disp_name_prefix = apply_filters('thwcfe_my-account_file_name_prefix', '', $key);
				$disp_name = $disp_name_prefix.''.$disp_name;
			}
		}

		$field .= $this->file_change_button_html($key, $disp_name, $args);
		$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="thwcfe-input-file input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $value ) . '" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_fragment_url($key, $args, $value, $custom_attributes) {
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}

		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $value ) . '" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;

	}

	public function woo_form_field_fragment_datetime_local($key, $args, $value, $custom_attributes){
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if ( is_null( $value ) || (is_string($value) && $value === '')) {
		 	$value = isset($args['html_default_datetime']) ? $args['html_default_datetime'] : '';
		}

		$maxDate = isset($args['max_html_datetime']) ? $args['max_html_datetime'] : '';
		$minDate = isset($args['min_html_datetime']) ? $args['min_html_datetime'] : '';

		$field  = '<input type="datetime-local" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $value ) . '" ';
		$field .= 'max="'. $maxDate .'" min="'. $minDate .'" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_fragment_date($key, $args, $value, $custom_attributes){
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if ( is_null( $value ) || (is_string($value) && $value === '')) {
		 	$value = isset($args['html_default_date']) ? $args['html_default_date'] : '';
		}
		$maxDate = isset($args['max_html_date']) ? $args['max_html_date'] : '';
		$minDate = isset($args['min_html_date']) ? $args['min_html_date'] : '';

		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '"  value="' . esc_attr( $value ) . '" ';
		$field .= 'max="'. $maxDate .'" min="'. $minDate .'" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_fragment_time($key, $args, $value, $custom_attributes){
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if ( is_null( $value ) || (is_string($value) && $value === '')) {
		 	$value = isset($args['html_default_time']) ? $args['html_default_time'] : '';
		}
		$maxTime = isset($args['max_html_time']) ? $args['max_html_time'] : '';
		$minTime = isset($args['min_html_time']) ? $args['min_html_time'] : '';
		
		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '"  value="' . esc_attr( $value ) . '" ';
		$field .= 'max="'. $maxTime .'" min="'. $minTime .'" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_fragment_month( $key, $args, $value, $custom_attributes ){
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if ( is_null( $value ) || (is_string($value) && $value === '')) {
		 	$value = isset($args['html_default_month']) ? $args['html_default_month'] : '';
		}
		$maxMonth = isset($args['max_html_month']) ? $args['max_html_month'] : '';
		$minMonth = isset($args['min_html_month']) ? $args['min_html_month'] : '';
		
		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '"  value="' . esc_attr( $value ) . '" ';
		$field .= 'max="'. $maxMonth .'" min="'. $minMonth .'" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	public function woo_form_field_fragment_week( $key, $args, $value, $custom_attributes ){
		$price_info = $this->prepare_price_data_string($args);
		if($this->is_price_field($args)){
			$args['input_class'][] = 'thwcfe-price-field';
		}
		if ( is_null( $value ) || (is_string($value) && $value === '')) {
		 	$value = isset($args['html_default_week']) ? $args['html_default_week'] : '';
		}
		$maxWeek = isset($args['max_html_week']) ? $args['max_html_week'] : '';
		$minWeek = isset($args['min_html_week']) ? $args['min_html_week'] : '';
		
		$field  = '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" ';
		$field .= 'id="' . esc_attr( $args['id'] ) . '"  value="' . esc_attr( $value ) . '" ';
		$field .= 'max="'. $maxWeek .'" min="'. $minWeek .'" ';
		$field .= implode( ' ', $custom_attributes ) . ' '.$price_info.' />';

		return $field;
	}

	private function prepare_file_preview_html($value, $type='', $hyperlink=true){
		if($type != 'file'){
			return '';
		}

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
   /****************************************
	******** CUSTOM FIELD TYPES - END ******
	****************************************/
}

endif;
