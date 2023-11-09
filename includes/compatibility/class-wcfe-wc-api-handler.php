<?php
if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_WC_API_Handler')):

class WCFE_WC_API_Handler extends WCFE_Checkout_Fields_Utils{

	public function __construct() {
		add_filter("woocommerce_webhook_payload", array( $this, 'woo_webhook_payload' ), 10, 4);

		//WooCommerce - Add custom post_meta data to the order REST API response.
		add_filter('woocommerce_api_order_response', array( $this, 'woo_api_order_response'), 20, 4);

		add_filter('woocommerce_rest_prepare_shop_order_object', array( $this, 'woo_rest_prepare_shop_order_object'), 10, 3);
	}

	private function get_order_meta_value($name, $field, $order_id, $user_id){
		$value = '';
		$type  = $field->get_property('type');
		$order = wc_get_order( $order_id );

		if($field->get_property('order_meta') && $order_id){
			// $value = get_post_meta( $order_id, $name, true );
			$value = $order->get_meta($name, true);

		}else if($field->get_property('user_meta') && $user_id){
			$value = get_user_meta( $user_id, $name, true );
		}

		if($type === 'file' && apply_filters('thwcfe_api_display_only_the_name_of_uploaded_file', true, $name)){
			$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
		}else{
			//$meta_value = THWCFE_Utils::get_option_text_from_value($field, $meta_value);
			$value = is_array($value) ? implode(", ", $value) : $value;
		}

		return $value;
	}

	private function get_customer_meta_value($name, $field, $user_id){
		$value = '';

		if($user_id){
			$type  = $field->get_property('type');
			$value = get_user_meta( $user_id, $name, true );

			if($type === 'file' && apply_filters('thwcfe_api_display_only_the_name_of_uploaded_file', true, $name)){
				$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
			}else{
				//$value = THWCFE_Utils::get_option_text_from_value($field, $value);
				$value = is_array($value) ? implode(", ", $value) : $value;
			}
		}

		return $value;
	}
	
	public function woo_webhook_payload($payload, $resource, $resource_id, $id) {
		$sections = $this->get_checkout_sections();
		
		if($resource === "order"){
			$order_id = isset($payload["id"]) ? $payload["id"] : false;
			$user_id  = isset($payload["customer_id"]) ? $payload["customer_id"] : false;
			
			if($sections && is_array($sections)){
				foreach($sections as $sname => $section){					
					if(THWCFE_Utils_Section::is_valid_section($section)){
						$fields = THWCFE_Utils_Section::get_fields($section);
						if($fields){
							foreach($fields as $name => $field){	
								if( THWCFE_Utils_Field::is_custom_enabled($field) ){
									$payload[$sname][$name] = $this->get_order_meta_value($name, $field, $order_id, $user_id);
								}
							}
						}
					}
				}
			}	
								
		}else if($resource === "customer"){
			$user_id = isset($payload["id"]) ? $payload["id"] : false;
			
			if($sections && is_array($sections) && $user_id){
				foreach($sections as $sname => $section){
					if(THWCFE_Utils_Section::is_valid_section($section)){
						$fields = THWCFE_Utils_Section::get_fields($section);
						if($fields){
							foreach($fields as $name => $field){	
								if( THWCFE_Utils_Field::is_custom_user_field($field) ){									
									$payload[$sname][$name] = $this->get_customer_meta_value($name, $field, $user_id);
								}
							}
						}
					}
				}
			}
		}
		return $payload;
	}

	public function woo_api_order_response($order_data, $order, $fields, $server) {
		$custom_fields = apply_filters('thwcfe_woo_api_order_response_fields', array());

		if(is_array($custom_fields)){
			foreach ($custom_fields as $key) {
				// $order_data[$key] = get_post_meta( $order->id, $key, true );
				$order_data[$key] = $order->get_meta( $key, true);
			}
		}
		return $order_data;
	}

	public function woo_rest_prepare_shop_order_object($response, $object, $request) {
		$sections = $this->get_checkout_sections();
		$order_id = $object->get_id();
		$user_id  = $object->get_customer_id();
		
		if($sections && is_array($sections)){
			foreach($sections as $sname => $section){					
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					if($fields){
						foreach($fields as $name => $field){	
							if( THWCFE_Utils_Field::is_custom_enabled($field) ){
								$response->data[$sname][$name] = $this->get_order_meta_value($name, $field, $order_id, $user_id);
							}
						}
					}
				}
			}
		}

		return $response;
	}
}

endif;