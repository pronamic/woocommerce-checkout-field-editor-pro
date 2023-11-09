<?php
/**
 * Checkout Field - Password
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Label')):

class WCFE_Checkout_Field_Label extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'label';
	}	
	
}

endif;