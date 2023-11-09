<?php
/**
 * Checkout Field - Week
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Html_Week')):

class WCFE_Checkout_Field_Html_Week extends WCFE_Checkout_Field{
	
	public $html_default_week = '';
	public $min_html_week = '';
	public $max_html_week = '';
	public function __construct() {
		$this->type = 'week';
	}

	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('html_default_week', isset($field['html_default_week']) ? $field['html_default_week'] : '');
			$this->set_property('min_html_week', isset($field['min_html_week']) ? $field['min_html_week'] : '');
			$this->set_property('max_html_week', isset($field['max_html_week']) ? $field['max_html_week'] : '');
		}
	}
}

endif;