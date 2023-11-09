<?php
/**
 * Checkout Field - Date
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Date')):

class WCFE_Checkout_Field_Date extends WCFE_Checkout_Field{
	
	public $html_default_date = '';
	public $min_html_date = '';
	public $max_html_date = '';
	public function __construct() {
		$this->type = 'date';
	}

	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('html_default_date', isset($field['html_default_date']) ? $field['html_default_date'] : '');
			$this->set_property('min_html_date', isset($field['min_html_date']) ? $field['min_html_date'] : '');
			$this->set_property('max_html_date', isset($field['max_html_date']) ? $field['max_html_date'] : '');
		}
	}
}

endif;