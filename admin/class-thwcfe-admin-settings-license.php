<?php
/**
 * The admin license settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Settings_License')):

class THWCFE_Admin_Settings_License extends THWCFE_Admin_Settings{
	protected static $_instance = null;
	
	public $ame_data_key;
	public $ame_deactivate_checkbox;
	public $ame_activation_tab_key;
	public $ame_deactivation_tab_key;

	public function __construct() {
		parent::__construct();
		
		$this->page_id = 'license_settings';
		$this->data_prefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( THWCFE_SOFTWARE_TITLE ) );
		$this->data_prefix = str_ireplace( 'woocommerce', 'th', $this->data_prefix );
		$this->ame_data_key             = $this->data_prefix . '_data';
		$this->ame_deactivate_checkbox  = $this->data_prefix . '_deactivate_checkbox';
		$this->ame_activation_tab_key   = $this->data_prefix . '_license_activate';
		$this->ame_deactivation_tab_key = $this->data_prefix . '_license_deactivate';
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	} 	
	
	public function render_page(){
		settings_errors();
		$this->render_tabs();
		$this->output_content();
	}

	private function output_content(){
		echo do_shortcode('[th_checkout_field_editor_for_woocommerce_license_form]');
	}
}

endif;