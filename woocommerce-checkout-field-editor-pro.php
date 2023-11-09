<?php
/**
 * Plugin Name: 	Checkout Field Editor for WooCommerce (Pro)
 * Plugin URI:  	https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/
 * Description: 	Design woocommerce checkout form in your own way, customize checkout fields(Add, Edit, Delete and re arrange fields).
 * Version:     	3.6.0
 * Author:      	ThemeHigh
 * Author URI:  	https://www.themehigh.com
 * Update URI: 		https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/
 *
 * Text Domain: 	woocommerce-checkout-field-editor-pro
 * Domain Path: 	/languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 8.1
 */
 
if(!defined('WPINC')){	die; }

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    
	    if(in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce')){
	        return true;
	    }else{
	        return false; 
	    }
	}
}

if(is_woocommerce_active()) {	
	define('THWCFE_VERSION', '3.6.0');
	!defined('THWCFE_SOFTWARE_TITLE') && define('THWCFE_SOFTWARE_TITLE', 'WooCommerce Checkout Field Editor');
	!defined('THWCFE_FILE_') && define('THWCFE_FILE_', __FILE__);
	!defined('THWCFE_PATH') && define('THWCFE_PATH', plugin_dir_path( __FILE__ ));
	!defined('THWCFE_URL') && define('THWCFE_URL', plugins_url( '/', __FILE__ ));
	!defined('THWCFE_BASE_NAME') && define('THWCFE_BASE_NAME', plugin_basename( __FILE__ ));


	// Update mechanisam related constants
	!defined('THWCFE_UPDATE_API_URL') && define('THWCFE_UPDATE_API_URL', 'https://www.themehigh.com');
	!defined('THWCFE_PRODUCT_ID') && define('THWCFE_PRODUCT_ID', 12);

	// !Warning: Item identifier is an internal identifier for product. We may update product name later in store & plugin. But item identifier must be same all time after releasing the plugin. It used to save details on customer database, cron event & generate license form short code.
	!defined('THWCFE_ITEM_IDENTIFIER') && define('THWCFE_ITEM_IDENTIFIER', 'Checkout Field Editor for WooCommerce');
	!defined('THWCFE_LICENSE_PAGE_URL') && define('THWCFE_LICENSE_PAGE_URL', admin_url('admin.php?page=th_checkout_field_editor_pro&tab=license_settings'));		

	/**
	 * The code that runs during plugin activation.
	 */
	function activate_thwcfe($network_wide) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwcfe-activator.php';
		THWCFE_Activator::activate($network_wide);
	}
	
	/**
	 * The code that runs during plugin deactivation.
	 */
	function deactivate_thwcfe() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwcfe-deactivator.php';
		THWCFE_Deactivator::deactivate();
	}
	
	register_activation_hook( __FILE__, 'activate_thwcfe' );
	register_deactivation_hook( __FILE__, 'deactivate_thwcfe' );


	if(!class_exists('THWCFE_EDD_Updater_Helper') ) {
		require_once( plugin_dir_path( __FILE__ ) . 'class-thwcfe-edd-updater-helper.php' );
	}

	if (!class_exists( 'THWCFE_EDD_Updater' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . '/class-thwcfe-edd-updater.php' );
	}


	/**
	 * Initialize plugin updater & updater helper.
	 *
	 * @return void
	 */
	function init_thwcfe_updater(){

		$helper_data = array(
			'api_url' => THWCFE_UPDATE_API_URL,
			'item_id' => THWCFE_PRODUCT_ID, 
			'item_identifier' => THWCFE_ITEM_IDENTIFIER,
			'license_page_url' => THWCFE_LICENSE_PAGE_URL,
		);
		// Setup the updater helper.
		$thwcfe_updater_helper = new THWCFE_EDD_Updater_Helper( __FILE__, $helper_data );

		/**
		 * Initialize the updater. Hooked into `init` to work with the wp_version_check cron job, which allows auto-updates.
		*/
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// retrieve our license key from the DB.
		$license_data = $thwcfe_updater_helper->get_license_data();
		$license_key = isset($license_data['license_key']) ? $license_data['license_key'] : false;

		// setup the updater
		$edd_updater = new THWCFE_EDD_Updater(
			THWCFE_UPDATE_API_URL,
			__FILE__,
			array(
				'version' => THWCFE_VERSION,
				'license' => $license_key,
				'item_id' => THWCFE_PRODUCT_ID,
				'author'  => 'ThemeHigh',
				'beta'    => false,
			)
		);
	}
	add_action( 'init', 'init_thwcfe_updater', 10 );


	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-thwcfe.php';
	
	/**
	 * Begins execution of the plugin.
	 */
	function run_thwcfe() {
		$plugin = new THWCFE();
		$plugin->run();
	}
	run_thwcfe();

	/**
	 * Returns helper class instance.
	 */
	function get_thwcfe_helper(){
		return new THWCFE_Functions();
	}	
}

add_action( 'before_woocommerce_init', 'thwcfe_before_woocommerce_init_hpos' ) ;
function thwcfe_before_woocommerce_init_hpos() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}