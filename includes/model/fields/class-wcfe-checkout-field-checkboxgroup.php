<?php
/**
 * Checkout Field - Checkboxgroup
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_CheckboxGroup')):

class WCFE_Checkout_Field_CheckboxGroup extends WCFE_Checkout_Field{
	public $options = array();
	
	public function __construct() {
		$this->type = 'checkboxgroup';
	}	
}

endif;