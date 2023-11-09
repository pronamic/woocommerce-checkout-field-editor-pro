<?php
/**
 * Checkout Field - Paragraph
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Paragraph')):

class WCFE_Checkout_Field_Paragraph extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'paragraph';
	}	
	
}

endif;