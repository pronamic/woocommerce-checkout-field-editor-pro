<?php
/**
 * Checkout Field - Time
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Html_Time')):

class WCFE_Checkout_Field_Html_Time extends WCFE_Checkout_Field{
	
	public $html_default_time = '';
	public $min_html_time = '';
	public $max_html_time = '';
	public function __construct() {
		$this->type = 'time';
	}

	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('html_default_time', isset($field['html_default_time']) ? $field['html_default_time'] : '');
			$this->set_property('min_html_time', isset($field['min_html_time']) ? $field['min_html_time'] : '');
			$this->set_property('max_html_time', isset($field['max_html_time']) ? $field['max_html_time'] : '');
		}
	}
}

endif;