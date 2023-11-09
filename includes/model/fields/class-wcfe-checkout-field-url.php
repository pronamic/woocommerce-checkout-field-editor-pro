<?php
/**
 * Checkout Field - Url
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Html')):

class WCFE_Checkout_Field_Url extends WCFE_Checkout_Field{
    public function __construct() {
        $this->type = 'url';
    }


}

endif;
