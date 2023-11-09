<?php
/**
 * The application scope class to retreive data.
 *
 * @link       https://themehigh.com
 * @since      3.0.4
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Data')):

class THWCFE_Data {
	protected static $_instance = null;
	private $products = array();
	private $categories = array();

	public function __construct() {

	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function load_products_ajax(){
		$productsList = array();
		$value = isset($_POST['value']) ? stripslashes($_POST['value']) : '';
		$count = 0;

		$limit = apply_filters('thwcfe_load_products_per_page', 100);

		if(!empty($value)){
			$value_arr = $value ? explode(',', $value) : false;

			$args = array(
			    'include' => $value_arr,
				'orderby' => 'name',
				'order' => 'ASC',
				'return' => 'ids',
				'limit' => $limit,
				'type'  => $this->get_all_product_types(),
			);
			
			$products = $this->get_products($args);
			
			if(is_array($products) && !empty($products)){
				foreach($products as $pid){
					$productsList[] = array("id" => $pid, "text" => get_the_title($pid). "(#" .$pid. ")", "selected" => true);
				}
			}

			$count = count($products);

		}else{
			$term = isset($_POST['term']) ? stripslashes($_POST['term']) : '';
			$page = isset($_POST['page']) ? stripslashes($_POST['page']) : 1;

		    $status = apply_filters('thwcfe_load_products_status', 'publish');

		    $args = array(
				's' => $term,
			    'limit' => $limit,
			    'page'  => $page,
			    'status' => $status,
				'orderby' => 'name',
				'order' => 'ASC',
				'return' => 'ids',
				'type'  => $this->get_all_product_types(),
			);

			$products = $this->get_products($args);

			if(is_array($products) && !empty($products)){
				foreach($products as $pid){
					$productsList[] = array("id" => $pid, "text" => get_the_title($pid) . "(#" .$pid. ")" );
				}
			}

			$count = count($products);
		}

		$morePages = $count < $limit ? false : true;

		$results = array(
			"results" => $productsList,
			"pagination" => array( "more" => $morePages )
		);

		wp_send_json_success($results);
  		die();
	}

	public function load_product_types_ajax(){
		$productsList = array();
		$unavailable_product_types = array('grouped','external','variation'); 
		
		$value = isset($_POST['value']) ? stripslashes($_POST['value']) : '';

		if(!empty($value)){
			$value_arr = $value ? explode(',', $value) : false;

			$product_types = $value_arr;

			if(is_array($product_types) && !empty($product_types)){
				foreach($product_types as $pid){
					$productsList[] = array("id" => $pid, "text" => $pid, "selected" => true);
					
				}
			}

		}else{
			
			$product_types = $this->get_all_product_types();

			if(is_array($product_types) && !empty($product_types)){
				foreach($product_types as $pid){
					if(!in_array($pid,$unavailable_product_types)){
						$productsList[] = array("id" => $pid, "text" => $pid );
					}		
				}
			}
		}

		$results = array(
			"results" => $productsList,
		);
		wp_send_json_success($results);
  		die();
	}

	public function get_products($args){
		$products = false;
		$is_wpml_active = THWCFE_Utils::is_wpml_active();

		if($is_wpml_active){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;

			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
			$current_lang = $sitepress->get_current_language();
			$sitepress->switch_lang($default_lang);

			$products = wc_get_products($args);

			$sitepress->switch_lang($current_lang);
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}else{
			$products = wc_get_products($args);
		}

		return $products;
	}

	/**
	 * Get all product types in store incluiding product variation
	 *
	 * @return Array
	 */ 
	public function get_all_product_types(){
		$product_types = array_merge( array_keys( wc_get_product_types() ));
		array_push($product_types, "variation");
		apply_filters('thwcfe_rules_product_types',  $product_types);
		return $product_types;
	}
}

endif;
