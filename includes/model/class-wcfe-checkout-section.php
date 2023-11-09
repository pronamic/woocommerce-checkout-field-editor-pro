<?php
/**
 * 
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Section')):

class WCFE_Checkout_Section {
	public $id = '';
	public $name = '';
	public $position = '';
	public $order = '';
	public $type = '';
	public $cssclass = '';
	public $enabled = true;
	
	public $custom_section = 1;
	public $show_title = 1;
	public $show_title_my_account = 1;
		
	public $title = '';
	public $title_type  = '';
	public $title_color = '';
	public $title_position = '';
	public $title_class = '';
	
	public $subtitle = '';
	public $subtitle_type  = '';
	public $subtitle_color = '';
	public $subtitle_position = '';
	public $subtitle_class = '';
	
	public $rules_action = '';
	public $rules_action_ajax = '';
	
	public $conditional_rules_json = '';
	public $conditional_rules = array();
	
	public $conditional_rules_ajax_json = '';
	public $conditional_rules_ajax = array();

	public $repeat_rules = '';
	public $rpt_name_suffix = '';
	public $rpt_label_suffix = '';
	public $rpt_incl_parent = 0;
	public $inherit_display_rule = 1;
	public $inherit_display_rule_ajax = 1;
	public $auto_adjust_display_rule_ajax = 1;
	
	public $fields = array();
	public $condition_sets = array();
	
	public function __construct() {}	
		
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