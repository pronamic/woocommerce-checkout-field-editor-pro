<?php
/**
 * The file that defines the plugin helper functions.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Functions')):

class THWCFE_Functions {
	
	/**
	 * 
	 * @since     2.9.0
	 * @return    string    The custom meta value.
	 */
	public function get_custom_order_meta($order_id, $meta_key) {
		$meta_value = false;
		$order = wc_get_order($order_id);
		if($order){
			$meta_value = $order->get_meta($meta_key, true);
		}
		return $meta_value;
	}

	public function get_custom_order_meta_price($order_id, $meta_key) {
		$fee_data = false;
		$fields = THWCFE_Utils::get_checkout_fields_full();

		if(is_array($fields) && isset($fields[$meta_key])){
			$field = $fields[$meta_key];
			$value = $this->get_custom_order_meta($order_id, $meta_key);
			$fees = $this->get_fees($order_id);
			$name = THWCFE_Utils::preare_fee_name($meta_key, $field->get_property('title'), $value);

			if(is_array($fees) && isset($fees[$name])){
				$fee_data = $fees[$name];
				$fee_data['name'] = $meta_key;
				$fee_data['value'] = $value;
			}
		}
		return $fee_data;
	}

	public function get_fees($order_id) {
		$fee_info = array();
		$order = wc_get_order($order_id);
		if($order){
			$fees = $order->get_fees();
			foreach($fees as $fee){
				$fee_data = array();
				$fee_data['label'] = $fee->get_name();
				$fee_data['amount'] = $fee->get_amount();

				$fee_info[$fee->get_name()] = $fee_data;
			}
		}
		return $fee_info;
	}

}

endif;