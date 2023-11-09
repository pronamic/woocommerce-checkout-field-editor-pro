<?php
/**
 * Checkout Field - Month
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Html_Month')):

class WCFE_Checkout_Field_Html_Month extends WCFE_Checkout_Field{
	
	public $html_default_month = '';
	public $min_html_month = '';
	public $max_html_month = '';
	public function __construct() {
		$this->type = 'month';
	}

	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('html_default_month', isset($field['html_default_month']) ? $field['html_default_month'] : '');
			$this->set_property('min_html_month', isset($field['min_html_month']) ? $field['min_html_month'] : '');
			$this->set_property('max_html_month', isset($field['max_html_month']) ? $field['max_html_month'] : '');
		}
	}
}

endif;