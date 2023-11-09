<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE')):

class THWCFE {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.9.0
	 * @access   protected
	 * @var      THWCFE_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.9.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.9.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.9.0
	 */
	public function __construct() {
		if(defined( 'THWCFE_VERSION')){
			$this->version = THWCFE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-checkout-field-editor-pro';
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		$this->loader->add_action( 'init', $this, 'init' );
		
		$this->set_compatibility();
	}
	
	public function init(){
		$this->define_constants();
	}
	
	private function define_constants(){
		!defined('THWCFE_ASSETS_URL_ADMIN') && define('THWCFE_ASSETS_URL_ADMIN', THWCFE_URL . 'admin/assets/');
		!defined('THWCFE_ASSETS_URL_PUBLIC') && define('THWCFE_ASSETS_URL_PUBLIC', THWCFE_URL . 'public/assets/');
		!defined('THWCFE_WOO_ASSETS_URL') && define('THWCFE_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - THWCFE_Loader. Orchestrates the hooks of the plugin.
	 * - THWCFE_i18n. Defines internationalization functionality.
	 * - THWCFE_Admin. Defines all hooks for the admin area.
	 * - THWCFE_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.9.0
	 * @access   private
	 */
	private function load_dependencies() {
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwcfe-autoloader.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwcfe-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwcfe-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thwcfe-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwcfe-public-checkout.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwcfe-public-myaccount.php';

		$this->loader = new THWCFE_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the THWCFE_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.9.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new THWCFE_i18n($this->get_plugin_name());
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}
	
	/*private function init_auto_updater(){
		if(!class_exists('THWCFE_Auto_Update_License') ) {
			$api_url = 'https://themehigh.com/';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-thwcfe-auto-update-license.php';
			THWCFE_Auto_Update_License::instance(__FILE__, THWCFE_SOFTWARE_TITLE, THWCFE_VERSION, 'plugin', $api_url, THWCFE_i18n::TEXT_DOMAIN);
		}
	}*/
	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.9.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new THWCFE_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_filter( 'woocommerce_screen_ids', $plugin_admin, 'add_screen_id' );
		$this->loader->add_filter( 'plugin_action_links_'.THWCFE_BASE_NAME, $plugin_admin, 'plugin_action_links' );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2 );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'print_js_variables', 10 );
		$this->loader->add_action('admin_footer', $plugin_admin,'quick_links',10);

		$general_settings = new THWCFE_Admin_Settings_General();
		$this->loader->add_action( 'after_setup_theme', $general_settings, 'define_admin_hooks' );

		$wcfe_data = THWCFE_Data::instance();
		$this->loader->add_action('wp_ajax_thwcfe_load_products', $wcfe_data, 'load_products_ajax');
    	$this->loader->add_action('wp_ajax_nopriv_thwcfe_load_products', $wcfe_data, 'load_products_ajax');
    	$this->loader->add_action('wp_ajax_thwcfe_load_product_type', $wcfe_data, 'load_product_types_ajax');
    	$this->loader->add_action('wp_ajax_nopriv_thwcfe_load_product_type', $wcfe_data, 'load_product_types_ajax');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.9.0
	 * @access   private
	 */
	private function define_public_hooks() {
		//if(!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX)){
			$plugin_checkout = new THWCFE_Public_Checkout( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_checkout, 'enqueue_styles_and_scripts' );
		
			$plugin_myaccount = new THWCFE_Public_MyAccount( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_myaccount, 'enqueue_styles_and_scripts' );
		//}

		$order_data = new THWCFE_Order_Data();
	}

	private function set_compatibility(){
		$this->loader->add_filter('thwcfe_custom_checkout_fields', 'THWCFE_Utils', 'get_custom_checkout_fields', 10, 2);
		$this->loader->add_filter('thwcfe_custom_checkout_fields_and_values', 'THWCFE_Utils', 'get_custom_checkout_fields_and_values', 10, 3);

		//WMSC Support
		//$this->loader->add_filter('thwmsc_hooked_sections', 'THWCFE_Utils', 'get_hooked_sections', 10, 2);
		$this->loader->add_filter('thwmsc_has_hooked_sections', 'THWCFE_Utils', 'has_hooked_sections', 10, 2);

		//WooCommerce CSV Export Support
		if(THWCFE_Utils::get_settings('enable_csv_export_support') === 'yes'){
			new WCFE_Checkout_Fields_Export_Handler();
		}
		
		//WooCommerce PDF Invoice & Packing Slip Support
		if(THWCFE_Utils::get_settings('enable_wcpdf_invoice_packing_slip_support') === 'yes'){
			new WCFE_WC_PDF_Invoices_Packing_Slips_Handler();
		}
		
		//WooCommerce API Support
		new WCFE_WC_API_Handler();
		
		//WooCommerce Zapier Support
		if(is_plugin_active('woocommerce-zapier/woocommerce-zapier.php') && THWCFE_Utils::get_settings('enable_wc_zapier_support') === 'yes'){
			new WCFE_WC_Zapier_Handler();
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.9.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.9.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.9.0
	 * @return    THWCFE_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.9.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

endif;