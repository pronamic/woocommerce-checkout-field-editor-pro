<?php
/**
 * The custom fields specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils_Field')):

class THWCFE_Utils_Field {
	static $SPECIAL_FIELD_TYPES = array('country', 'state', 'city');
	static $ARRAY_PROPS = array('class', 'input_class', 'label_class', 'title_class', 'subtitle_class', 'validate');
	static $BOOLEAN_PROPS = array('custom_field', 'order_meta', 'user_meta', 'price_field', 'checked', 'required', 'enabled', 'clear', 'show_in_email', 
							'show_in_email_customer', 'show_in_order', 'show_in_thank_you_page');
	static $DEFAULT_FIELD_PROPS = array(
		'type'        => array('name'=>'type', 'value'=>'text'),
		'label' 	  => array('name'=>'title', 'value'=>''),
		'description' => array('name'=>'description', 'value'=>''),
		'placeholder' => array('name'=>'placeholder', 'value'=>''),
		'order' 	  => array('name'=>'order', 'value'=>''),
		'priority'    => array('name'=>'priority', 'value'=>''),
		'autocomplete' => array('name'=>'autocomplete', 'value'=>''),
		'hidden'	   => array('name'=>'hidden', 'value'=>''),
		
		'class' 	  => array('name'=>'cssclass', 'value'=>array()),
		'label_class' => array('name'=>'title_class', 'value'=>array()),
		
		'custom' 	  => array('name'=>'custom_field', 'value'=>0),
		'value' 	  => array('name'=>'value', 'value'=>''),
		'default' 	  => array('name'=>'value', 'value'=>''),
		'validate'	  => array('name'=>'validate', 'value'=>array()),
		
		'required' 	  => array('name'=>'required', 'value'=>0),
		'clear' 	  => array('name'=>'clear', 'value'=>0),
		'enabled' 	  => array('name'=>'enabled', 'value'=>1),

		'country_field' => array('name'=>'country_field', 'value'=>''),
		//'country' => array('name'=>'country', 'value'=>''),
		
		'show_in_email' => array('name'=>'show_in_email', 'value'=>1),
		'show_in_email_customer' => array('name'=>'show_in_email_customer', 'value'=>1),
		'show_in_order' => array('name'=>'show_in_order', 'value'=>1),
		'show_in_thank_you_page' => array('name'=>'show_in_thank_you_page', 'value'=>1),
		'show_in_my_account_page' => array('name'=>'show_in_my_account_page', 'value'=>0),
		
		
		/*$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
		);*/
	);
	static $FIELD_PROPS = array(
		'type' => array('name'=>'type', 'value'=>''),
		'name' => array('name'=>'name', 'value'=>''),
		'label' => array('name'=>'title', 'value'=>''),
		'description' => array('name'=>'description', 'value'=>''),
		'label_class' => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
		'input_class' => array('name'=>'input_class', 'value'=>array(), 'value_type'=>'array'),
		'default'	  => array('name'=>'value', 'value'=>''),
		'validate'	  => array('name'=>'validate', 'value'=>array(), 'value_type'=>'array'),
		'autocomplete' => array('name'=>'autocomplete', 'value'=>''),
		'hidden'	   => array('name'=>'hidden', 'value'=>''),
		'input_mask'  => array('name'=>'input_mask', 'value'=>''),
	
		'placeholder' => array('name'=>'placeholder', 'value'=>''),
		'class' 	  => array('name'=>'cssclass', 'value'=>array(), 'value_type'=>'array'),
		
		'order_meta' => array('name'=>'order_meta', 'value'=>1),
		'user_meta'  => array('name'=>'user_meta', 'value'=>0),
		'disable_select2' => array('name'=>'disable_select2', 'value'=>0),

		
		'checked'  => array('name'=>'checked', 'value'=>1),
		'required' => array('name'=>'required', 'value'=>0),
		'clear'    => array('name'=>'clear', 'value'=>0),
		'enabled'  => array('name'=>'enabled', 'value'=>1),
		// 'enable_country_code' => array('name'=>'enable_country_code', 'value'=>0),
		
		'price' 	 => array('name'=>'price', 'value'=>''),
		'price_type' => array('name'=>'price_type', 'value'=>''),
		'price_unit' => array('name'=>'price_unit', 'value'=>0),
		'taxable' 	 => array('name'=>'taxable', 'value'=>''),
		'tax_class'  => array('name'=>'tax_class', 'value'=>''),
		
		'title' 	  => array('name'=>'title', 'value'=>''),
		'title_type'  => array('name'=>'title_type', 'value'=>''),
		'title_color' => array('name'=>'title_color', 'value'=>''),
		'title_class' => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
		
		'subtitle' 		 => array('name'=>'subtitle', 'value'=>''),
		'subtitle_type'  => array('name'=>'subtitle_type', 'value'=>''),
		'subtitle_color' => array('name'=>'subtitle_color', 'value'=>''),
		'subtitle_class' => array('name'=>'subtitle_class', 'value'=>array(), 'value_type'=>'array'),
		
		'minlength' => array('name'=>'minlength', 'value'=>''),
		'maxlength' => array('name'=>'maxlength', 'value'=>''),
		
		'repeat_x'     => array('name'=>'repeat_x', 'value'=>1),
		'repeat_rules' => array('name'=>'repeat_rules', 'value'=>''),
		'rpt_name_suffix' => array('name'=>'rpt_name_suffix', 'value'=>''),
		'rpt_label_suffix' => array('name'=>'rpt_label_suffix', 'value'=>''),
		'rpt_incl_parent' => array('name'=>'rpt_incl_parent', 'value'=>''),

		'inherit_display_rule'      => array('name'=>'inherit_display_rule', 'value'=>1),
		'inherit_display_rule_ajax' => array('name'=>'inherit_display_rule_ajax', 'value'=>1),
		'auto_adjust_display_rule_ajax'   => array('name'=>'auto_adjust_display_rule_ajax', 'value'=>1),

		'maxsize' => array('name'=>'maxsize', 'value'=>''),
		'accept'  => array('name'=>'accept', 'value'=>''),
		'custom_btn_file_upload' => array('name'=>'custom_btn_file_upload','value'=>''),
		
		'date_format' 	=> array('name'=>'date_format', 'value'=>''),
		'default_date' 	=> array('name'=>'default_date', 'value'=>''),
		'max_date' 	  	=> array('name'=>'max_date', 'value'=>''),
		'min_date' 	    => array('name'=>'min_date', 'value'=>''),
		'year_range' 	=> array('name'=>'year_range', 'value'=>''),
		'number_months' => array('name'=>'number_of_months', 'value'=>''),
		'disabled_days' => array('name'=>'disabled_days', 'value'=>'', 'value_type'=>'array'),
		'disabled_dates' => array('name'=>'disabled_dates', 'value'=>''),
		'html_default_datetime' => array('name'=>'html_default_datetime', 'value'=>''),
		'min_html_datetime' => array('name'=>'min_html_datetime', 'value'=>''),
		'max_html_datetime' => array('name'=>'max_html_datetime', 'value'=>''),
		'html_default_date' => array('name'=>'html_default_date', 'value'=>''),
		'min_html_date' => array('name'=>'min_html_date', 'value'=>''),
		'max_html_date' => array('name'=>'max_html_date', 'value'=>''),
		'html_default_time' => array('name'=>'html_default_time', 'value'=>''),
		'min_html_time' => array('name'=>'min_html_time', 'value'=>''),
		'max_html_time' => array('name'=>'max_html_time', 'value'=>''),
		'html_default_month' => array('name'=>'html_default_month', 'value'=>''),
		'min_html_month' => array('name'=>'min_html_month', 'value'=>''),
		'max_html_month' => array('name'=>'max_html_month', 'value'=>''),
		'html_default_week' => array('name'=>'html_default_week', 'value'=>''),
		'min_html_week' => array('name'=>'min_html_week', 'value'=>''),
		'max_html_week' => array('name'=>'max_html_week', 'value'=>''),

		
		'min_time' 	  => array('name'=>'min_time', 'value'=>''),
		'max_time' 	  => array('name'=>'max_time', 'value'=>''),
		'start_time'  => array('name'=>'start_time', 'value'=>''),
		'time_step'   => array('name'=>'time_step', 'value'=>''),
		'time_format' => array('name'=>'time_format', 'value'=>''),
		'linked_date' => array('name'=>'linked_date', 'value'=>''),
		'disable_time_slot' => array('name'=>'disable_time_slot', 'value'=>''),

		'country_field' => array('name'=>'country_field', 'value'=>''),
		'country' => array('name'=>'country', 'value'=>''),
		
		'show_in_my_account_page' => array('name'=>'show_in_my_account_page', 'value'=>0),
	);

	public static function is_valid_field($field){
		if(isset($field) && $field instanceof WCFE_Checkout_Field){
			return true;
		} 
		return false;
	}
	
	public static function is_enabled($field){
		if($field->get_property('enabled')){
			return true;
		}
		return false;
	}

	public static function is_custom_field($field){
		return $field->custom_field;
	}

	public static function is_valid_enabled($field){
		if(self::is_valid_field($field) && self::is_enabled($field)){
			return true;
		}
		return false;
	}

	public static function is_custom_enabled($field){
		if(self::is_valid_field($field) && self::is_custom_field($field) && self::is_enabled($field)){
			return true;
		}
		return false;
	}
	
	public static function is_user_field($field){
		return $field->get_property('user_meta');
	}

	public static function is_custom_user_field($field){
		if(self::is_custom_enabled($field) && self::is_user_field($field)){
			return true;
		}
		return false;
	}

	public static function is_order_field($field){
		return $field->get_property('order_meta');
	}

	public static function is_html_field($type){
		$is_html = false;
		if($type === 'heading' || $type === 'label'){
			$is_html = true;
		}
		return $is_html;
	}

	public static function prepare_field($field, $name, $props){
		if(!empty($props) && is_array($props)){
			$field->set_property('id', $name);
			$field->set_property('name', $name);
			
			foreach(self::$DEFAULT_FIELD_PROPS as $pname => $property){
				$pvalue = isset($props[$pname]) ? $props[$pname] : $property['value'];
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				
				$field->set_property($property['name'], $pvalue);
			}
			if(isset($props['default'])){
				$dvalue = $props['default'];
				$ftype = array('datetime_local','date','time','month','week');
				if(in_array($props['type'],$ftype)){
					if($props['type'] == 'datetime_local'){
						$field->set_property('html_default_datetime', $dvalue );
					}
					if($props['type'] == 'date'){
						$field->set_property('html_default_date', $dvalue );
					}
					if($props['type'] == 'time'){
						$field->set_property('html_default_time', $dvalue );
					}
					if($props['type'] == 'month'){
						$field->set_property('html_default_month', $dvalue );
					}if($props['type'] == 'week'){
						$field->set_property('html_default_week', $dvalue );
					}
				}
			}
			
			if(isset($props['options']) && is_array($props['options'])){
				$options_object = array();
				foreach($props['options'] as $option_key => $option_text){
					$option_object = array();
					$option_object['key'] = $option_key;
					$option_object['text'] = $option_text;
					
					$options_object[$option_key] = $option_object;
				}
				$field->set_property( 'options', $options_object );
			}else{
				$field->set_property( 'options', array() );
			}
			//$this->set_address_field( isset($field['is_address_field']) ? $field['is_address_field'] : array() ); TODO
		}
		return $field;
	}
	
	public static function prepare_field_from_posted_data($posted, $props){
		$type = isset($posted['i_type']) ? trim(stripslashes($posted['i_type'])) : '';
		$type = empty($type) ? trim(stripslashes($posted['i_original_type'])) : $type;
		
		//$position = isset($posted['i_position']) ? $posted['i_position'] : '';
		//$type    = isset($posted['i_title']) ? $posted['i_title'] : '';
		
		//$name = strtolower($name);
		//$name = is_numeric($name) ? "s_".$name : $name;
			
		$field = self::create_field($type); 
		//$section->set_property('id', $name);
		
		foreach( $props as $pname => $property ){
			$iname  = 'i_'.$pname;
			
			$pvalue = '';
			if($property['type'] === 'checkbox'){
				$pvalue = isset($posted[$iname]) ? $posted[$iname] : 0;
			}else if(isset($posted[$iname])){
				if(is_array($posted[$iname])){
					$pvalue = implode(',', $posted[$iname]);
				}else{
					$pvalue = trim(stripslashes($posted[$iname]));
					$pvalue = wp_kses_post($pvalue);
				}
			}
			
			$field->set_property($pname, $pvalue);
		}
		
		if($type === 'select' || $type === 'multiselect' || $type === 'radio' || $type === 'checkboxgroup'){
			$options_json = isset($posted['i_options']) ? trim(stripslashes($posted['i_options'])) : '';
			$options_arr = self::prepare_options_array($options_json);

			$options_extra = apply_filters('thwcfe_field_options', array(), $field->get_property('name'));
			if(is_array($options_extra) && !empty($options_extra)){
				$options_arr = array_merge($options_arr, $options_extra);
				$options_json = self::prepare_options_json($options_arr);
			}
			
			$field->set_property('options_json', $options_json);
			$field->set_property('options', $options_arr);
		}elseif($type === 'number'){
			$default_value = $field->get_property('value');
			if($default_value && !is_numeric($default_value)){
				$field->set_property('value', '');
			}
		}
		
		$ftype = $field->get_property('type');
		if(!$ftype){
			$field->set_property('type', $type);
		}
		
		//$field->set_property('order', isset($posted['order']) ? trim(stripslashes($posted['order'])) : 0);
		//$field->set_property('custom_field', isset($posted['i_custom_field']) ? trim(stripslashes($posted['i_custom_field'])) : 0);
		
		$field->set_property('name_old', isset($posted['i_name_old']) ? trim(stripslashes($posted['i_name_old'])) : '');
		
		$field->set_property('rules_action', isset($posted['i_rules_action']) ? trim(stripslashes($posted['i_rules_action'])) : '');
		$field->set_property('conditional_rules_json', isset($posted['i_rules']) ? trim(stripslashes($posted['i_rules'])) : '');
		$field->set_property('conditional_rules', THWCFE_Utils_Condition::prepare_conditional_rules($posted, false));
		
		$field->set_property('rules_action_ajax', isset($posted['i_rules_action_ajax']) ? trim(stripslashes($posted['i_rules_action_ajax'])) : '');
		$field->set_property('conditional_rules_ajax_json', isset($posted['i_rules_ajax']) ? trim(stripslashes($posted['i_rules_ajax'])) : '');
		$field->set_property('conditional_rules_ajax', THWCFE_Utils_Condition::prepare_conditional_rules($posted, true));

		$field->set_property('repeat_rules', isset($posted['i_repeat_rules']) ? trim(stripslashes($posted['i_repeat_rules'])) : '');
		
		self::prepare_properties($field);
		return $field;
	}
	
	public static function prepare_properties($field){
		if(apply_filters("thwcfe_sanitize_field_names", true)){
			$name = urldecode( sanitize_title(wc_clean($field->get_property('name'))) );
		}else{
			$name = urldecode( wc_clean($field->get_property('name')) );
		}
		$type = $field->get_property('type');
		
		$field->set_property('name', $name);
		$field->set_property('id', $name);
				
		if($type === 'radio' || $type === 'select' || $type === 'multiselect'){
			foreach($field->get_property('options') as $option_key => $option){
				if(isset($option['price']) && is_numeric($option['price']) && $option['price'] != 0){
					$field->set_property('price_field', 1);
					break;
				}
			}
		}else{
			if((is_numeric($field->price) && $field->price != 0) || $field->price_type === 'custom'){
				$field->set_property('price_field', 1);
			}
		}
		
		if($type === 'label' || $type === 'heading'){
			$field->set_property('price_field', 0);
			$field->set_property('price', 0);
			$field->set_property('price_type', '');
			$field->set_property('price_unit', 0);
			$field->set_property('price_prefix', '');
			$field->set_property('price_suffix', '');
			$field->set_property('taxable', '');
			$field->set_property('tax_class', '');
			$field->set_property('required', 0);
		}

		//If price is set & no price type, set default price type for field
		$price = $field->get_property('price');
		$price_type = $field->get_property('price_type');
		if($price && $price_type == ''){
			$field->set_property('price_type', 'normal');
		}
		
		$field->set_property('property_set', self::get_property_set($field));
		
		//WPML Support
		self::add_wpml_support($field);
		
		return $field;
	}
	
	/*public static function filter_conditional_rules($conditional_rule_sets){
		if(is_account_page() && !is_checkout()){
			$user_conditions = array(WCFE_Condition::USER_ROLE_EQ, WCFE_Condition::USER_ROLE_NE);
			
			if(!empty($conditional_rule_sets) && is_array($conditional_rule_sets)){
				foreach($conditional_rule_sets as $rskey => $conditional_rule_set){
					$conditional_rules = $conditional_rule_set->get_condition_rules();
					if(!empty($conditional_rules) && is_array($conditional_rules)){
						foreach($conditional_rules as $rkey => $conditional_rule){
							$condition_sets = $conditional_rule->get_condition_sets();
							if(!empty($condition_sets) && is_array($condition_sets)){
								foreach($condition_sets as $cskey => $condition_set){				
									$conditions = $condition_set->get_conditions();
									if(!empty($conditions) && is_array($conditions)){
										foreach($conditions as $ckey => $condition){
											if(!in_array($condition->operator, $user_conditions)){
												unset($conditions[$ckey]);
											}
										}
									}
									if(empty($conditions)){
										unset($condition_sets[$cskey]);
									}
								}
							}
							if(empty($condition_sets)){
								unset($conditional_rules[$rkey]);
							}
						}
					}
					if(empty($conditional_rules)){
						unset($conditional_rule_sets[$rskey]);
					}
				}
			}
		}
		return $conditional_rule_sets;
	}*/
		
	public static function show_field($field, $cart_info){
		$valid = true;
		$show = true;
		$conditional_rules = $field->get_property('conditional_rules');
		$conditional_rules = THWCFE_Utils_Condition::filter_conditional_rules($conditional_rules);
		
		if(!empty($conditional_rules)){
			foreach($conditional_rules as $conditional_rule){				
				if(!THWCFE_Utils_Condition::is_satisfied_rules_set($conditional_rule, $cart_info)){
					$valid = false;
				}
			}
			
			if($field->get_property('rules_action') === 'hide'){
				$show = $valid ? false : true;
			}else{
				$show = $valid ? true : false;
			}
		}
		
		$show = apply_filters('thwcfe_show_field_'.$field->name, $show);
		$show = apply_filters('thwcfe_show_field', $show, $field->name);
		return $show;
	}
	
	public static function get_property_set($field){
		if(self::is_valid_field($field)){
			$optionsObj = $field->get_property('options');
			$options = array();
			foreach($optionsObj as &$option){
				$options[$option['key']] = $option['text'];
			}
			
			$props_set = array();
			foreach(self::$FIELD_PROPS as $pname => $props){
				$fvalue = $field->get_property($props['name']);
				
				if(in_array($pname, self::$ARRAY_PROPS) && !empty($fvalue)){
					$fvalue = is_array($fvalue) ? $fvalue : THWCFE_Utils::convert_string_to_array($fvalue);
				}
				
				if(!in_array($pname, self::$BOOLEAN_PROPS)){
					$fvalue = empty($fvalue) ? $props['value'] : $fvalue;
				}
				
				if($pname === 'required'){
					$fvalue = $fvalue ? true : false;
				}
				
				$props_set[$pname] = $fvalue;
			}
			
			if($field->get_property('type') === 'checkbox'){
				$off_value = empty($props_set['on_value']) ? 0 : '';
				$off_value = apply_filters('thwcfe_checkbox_field_off_value', $off_value, $field->name);
				
				$props_set['on_value'] = $field->get_property('value');
				$props_set['off_value'] = $off_value;
				
				if($field->get_property('checked')){
					$props_set['default'] = !empty($props_set['on_value']) ? $props_set['on_value'] : 1;
				}else{
					$props_set['default'] = !empty($props_set['on_value']) ? '' : 0;
				}
			}
			
			$order = is_numeric($field->get_property('order')) ? ($field->get_property('order')+1)*10 : $field->get_property('order');
			$rules_json = $field->get_property('conditional_rules_json');
			
			$props_set['custom'] = self::is_custom_field($field);
			$props_set['priority'] = THWCFE_Utils::is_blank($order) ? $field->get_property('priority') : $order;
			$props_set['label'] = THWCFE_i18n::t($props_set['label']);
			
			$props_set['options'] = $options;
			$props_set['options_object'] = $optionsObj;
			$props_set['rules_action'] = $field->get_property('rules_action_ajax'); 
			$props_set['rules'] = $field->get_property('conditional_rules_ajax_json');
			$props_set['has_non_ajax_rules'] = empty($rules_json) ? false : true;
			
			return $props_set;
		}else{
			return false;
		}
	}
			
	public static function get_option_array($field){
		$options_array = array();
		$options = $field->get_property('options');
		if($options && is_array($options)){
			foreach($options as $key => $option){
				$options_array[$option['key']] = $option['text'];
			}
		}
		return $options_array;
	}
	
	public static function prepare_options_array($options_json){
		$options_json = rawurldecode($options_json);
		$options_arr = json_decode($options_json, true);
		$options = array();
		
		if($options_arr){
			foreach($options_arr as $option){
				$option['key'] = empty($option['key']) ? $option['text'] : $option['key'];
				$options[$option['key']] = $option;
			}
		}
		return $options;
	}

	public static function prepare_options_json($options){
		$options_json = json_encode($options);
		$options_json = rawurlencode($options_json);
		return $options_json;
	}
	
	public static function create_field($type, $name = false, $field_args = false){
		$field = false;
		
		if(isset($type)){
			if($type === 'text'){
				$field = new WCFE_Checkout_Field_InputText();
			}else if($type === 'hidden'){
				$field = new WCFE_Checkout_Field_Hidden();
			}else if($type === 'password'){
				$field = new WCFE_Checkout_Field_Password();
			}else if($type === 'textarea'){
				$field = new WCFE_Checkout_Field_Textarea();
			}else if($type === 'select'){
				$field = new WCFE_Checkout_Field_Select();
			}else if($type === 'multiselect'){
				$field = new WCFE_Checkout_Field_Multiselect();
			}else if($type === 'radio'){
				$field = new WCFE_Checkout_Field_Radio();
			}else if($type === 'checkbox'){
				$field = new WCFE_Checkout_Field_Checkbox();
			}else if($type === 'checkboxgroup'){
				$field = new WCFE_Checkout_Field_CheckboxGroup();
			}else if($type === 'datepicker'){
				$field = new WCFE_Checkout_Field_DatePicker();
			}else if($type === 'timepicker'){
				$field = new WCFE_Checkout_Field_TimePicker();
			}else if($type === 'file'){
				$field = new WCFE_Checkout_Field_File();
			}else if($type === 'heading'){
				$field = new WCFE_Checkout_Field_Heading();
			}else if($type === 'label'){
				$field = new WCFE_Checkout_Field_Label();
			}else if($type === 'country'){
				$field = new WCFE_Checkout_Field_Country();
			}else if($type === 'email'){
				$field = new WCFE_Checkout_Field_Email();
			}else if($type === 'state'){
				$field = new WCFE_Checkout_Field_State();
			}else if($type === 'city'){
				$field = new WCFE_Checkout_Field_City();
			}else if($type === 'tel'){
				$field = new WCFE_Checkout_Field_Tel();
			}else if($type === 'phone'){
				$field = new WCFE_Checkout_Field_Tel();
			}else if($type === 'number'){
				$field = new WCFE_Checkout_Field_Number();
			}else if($type === 'url'){
				$field = new WCFE_Checkout_Field_Url();
			}else if($type === 'datetime_local'){
				$field = new WCFE_Checkout_Field_DatetimeLocal();
			}else if($type === 'date'){
				$field = new WCFE_Checkout_Field_Date();
			}else if($type === 'time'){
				$field = new WCFE_Checkout_Field_Html_Time();
			}else if($type === 'month'){
				$field = new WCFE_Checkout_Field_Html_Month();
			}else if($type === 'week'){
				$field = new WCFE_Checkout_Field_Html_Week();
			}else if($type === 'paragraph'){
				$field = new WCFE_Checkout_Field_Paragraph();
			}
		}else{
			$field = new WCFE_Checkout_Field_InputText();
		}
		
		if($field && $name && $field_args){
			self::prepare_field($field, $name, $field_args);
		}
		return $field;
	}
				
	public static function add_wpml_support($field){
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Title - '.$field->name, $field->title );
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Subtitle - '.$field->name, $field->subtitle );
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Placeholder - '.$field->name, $field->placeholder );
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Description - '.$field->name, $field->description );
		
		$options = $field->get_property('options');
		foreach($options as $option){
			WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Option - '.$field->name.' - '.$option['key'], $option['text'] );
		}
	}
}

endif;