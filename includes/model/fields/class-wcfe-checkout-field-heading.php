<?php
/**
 * Checkout Field - Heading
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Heading')):

class WCFE_Checkout_Field_Heading extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'heading';
	}	
	
}

endif;