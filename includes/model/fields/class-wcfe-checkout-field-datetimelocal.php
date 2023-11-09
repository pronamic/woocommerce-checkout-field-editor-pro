<?php
/**
 * Checkout Field - Datetime Local
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_DatetimeLocal')):

class WCFE_Checkout_Field_DatetimeLocal extends WCFE_Checkout_Field{
	
	public $html_default_datetime = '';
	public $min_html_datetime = '';
	public $max_html_datetime = '';
	public function __construct() {
		$this->type = 'datetime-local';
	}

	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('html_default_datetime', isset($field['html_default_datetime']) ? $field['html_default_datetime'] : '');
			$this->set_property('min_html_datetime', isset($field['min_html_datetime']) ? $field['min_html_datetime'] : '');
			$this->set_property('max_html_datetime', isset($field['max_html_datetime']) ? $field['max_html_datetime'] : '');
		}
	}
}

endif;