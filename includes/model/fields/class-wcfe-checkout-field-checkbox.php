<?php
/**
 * Checkout Field - Checkbox
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Checkbox')):

class WCFE_Checkout_Field_Checkbox extends WCFE_Checkout_Field{
	public $checked = 0;
	
	public function __construct() {
		$this->type = 'checkbox';
	}	
	
	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_property('checked', isset($field['checked']) ? $field['checked'] : 0 );
		}
	}
}

endif;