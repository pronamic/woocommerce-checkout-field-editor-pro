<?php
/**
 * Checkout Field Properties
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field')):

class WCFE_Checkout_Field{
	public $custom_field = 0;
	public $order = '';
	public $priority = '';
	public $id = '';
	public $autocomplete = '';
	public $hidden = '';
	
	public $name = '';	
	public $name_old = '';
	public $type = '';
	public $order_meta = 1;
	public $user_meta = 0;
	public $disable_select2 = 0;
	
	public $value = '';
	public $placeholder = '';
	public $validate = '';
	public $cssclass = '';
	public $input_class = '';
	public $description = '';
	public $input_mask = '';
	//public $cssclass_str = '';
	
	public $price_field = false;
	public $price = 0;
	public $price_unit = 0;
	public $price_type = '';
	public $taxable = '';
	public $tax_class = '';
	
	public $required = 0;
	public $enabled = 1;
	public $clear = 0;
	// public $enable_country_code = 0;
	
	public $show_in_email = 1;
	public $show_in_email_customer = 1;
	public $show_in_order = 1;
	public $show_in_thank_you_page = 1;
	public $show_in_my_account_page = 0;
	
	public $title = '';
	public $title_type  = '';
	public $title_color = '';
	public $title_class = '';
	///public $title_class_str = '';
	
	public $subtitle = '';
	public $subtitle_type  = '';
	public $subtitle_color = '';
	public $subtitle_class = '';
	//public $subtitle_class_str = '';
	
	public $minlength = '';
	public $maxlength = '';

	public $repeat_x = 1;
	public $repeat_rules = '';
	public $rpt_name_suffix = '';
	public $rpt_label_suffix = '';
	public $rpt_incl_parent = 0;
	public $inherit_display_rule = 1;
	public $inherit_display_rule_ajax = 1;
	public $auto_adjust_display_rule_ajax = 1;
	
	public $options_json = '';
	public $options = array();
	
	//public $validator_arr = array();
	
	public $rules_action = '';
	public $rules_action_ajax = '';
	
	public $conditional_rules_json = '';
	public $conditional_rules = array();
	
	public $conditional_rules_ajax_json = '';
	public $conditional_rules_ajax = array();
	
	public $property_set = false;
		
	public function __construct(){}	
	
	public function set_property($name, $value){
		if(property_exists($this, $name)){
			$this->$name = $value;
		}
	}
	
	public function get_property($name){
		if(property_exists($this, $name)){
			return $this->$name;
		}else{
			return '';
		}
	}
}

endif;