<?php
/**
 * Checkout Field Editor Export Handler
 *
 * @author      ThemeHiGH
 * @category    Admin
 */
 
if(!defined('ABSPATH')){ exit; }
 
if(!class_exists('WCFE_Checkout_Fields_Export_Handler')):
 
class WCFE_Checkout_Fields_Export_Handler extends WCFE_Checkout_Fields_Utils {
	private $fields;

	public function __construct() {
		$this->fields = $this->get_export_fields();

		// Customer / Order CSV Export column headers/data
		add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'thwcfe_order_csv_export_order_headers' ), 10, 2 );
		add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'thwcfe_customer_order_csv_export_order_row' ), 10, 4 );

		// Newly added filters for customer export
		add_filter( 'wc_customer_order_csv_export_customer_headers', array( $this, 'thwcfe_order_csv_export_customer_headers' ), 10, 2 );
		add_filter( 'wc_customer_order_csv_export_customer_row', array( $this, 'thwcfe_order_csv_export_customer_row' ), 10, 4 );
	}

	/**
	 * Adds support for Customer/Order CSV Export by adding a vendor column header
	 */
	public function thwcfe_order_csv_export_order_headers($headers, $csv_generator) {
		$field_headers = array();

		foreach ( $this->fields as $name => $options ) {
			$field_headers[ $name ] = $options['title'];
		}

		return array_merge( $headers, $field_headers );
	}

	/**
	 * Adds support for Customer/Order CSV Export by adding checkout editor field data
	 */
	public function thwcfe_customer_order_csv_export_order_row( $order_data, $order, $csv_generator ) {
		$field_data = array();
		
		$user_id = $order->get_user_id();
		$order_id = false;
		if($this->woo_version_check()){
			$order_id = $order->get_id();
		}else{
			$order_id = $order->id;
		}

		foreach ( $this->fields as $key => $field ) {
			$type = isset($field['type']) && $field['type'] ? $field['type'] : 'text';
			$value = '';
			if(isset($field['order_meta']) && $field['order_meta']){
				// $value = get_post_meta( $order_id, $key, true );
				$value = $order->get_meta( $key, true );
			}else if($user_id && isset($field['user_meta']) && $field['user_meta']){
				$value = get_user_meta( $user_id, $key, true );
			}

			if($type === 'file' && apply_filters('thwcfe_csv_export_display_only_the_name_of_uploaded_file', true, $key)){
				$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
			}else{
				//$value = THWCFE_Utils::get_option_text_from_value($field, $value);
				$value = is_array($value) ? implode(", ", $value) : $value;
			}

			$field_data[$key] = $value;
			//$field_data[ $key ] = get_post_meta( $order_id, $key, true );
			// $field_data[ $key ] = $order->get_meta( $key, true );
		}

		$new_order_data = array();

		if(isset($csv_generator->export_format) && ($csv_generator->export_format == 'default_one_row_per_item' || $csv_generator->export_format == 'legacy_one_row_per_item')){
			foreach($order_data as $data){
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}
		} else if(apply_filters('thwcfe_order_csv_export_one_row_per_item', false)){
			foreach($order_data as $data){
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}
		} else {
			$new_order_data = array_merge( $field_data, $order_data );
		}

		return $new_order_data;
	}

	public function thwcfe_order_csv_export_customer_headers( $headers, $csv_generator ){
		$field_headers = array();

		foreach ( $this->fields as $name => $options ) {
			if( isset($options['user_meta']) && $options['user_meta']){
				$field_headers[ $name ] = $options['title'];
			}
		}

		return array_merge( $headers, $field_headers );
	}

	public function thwcfe_order_csv_export_customer_row( $customer_data, $user, $csv_generator ){
		foreach ( $this->fields as $key => $field ) {
			if($user->ID && isset($field['user_meta']) && $field['user_meta']){
				$type  = isset($field['type']) && $field['type'] ? $field['type'] : 'text';
				$value = get_user_meta( $user->ID, $key, true );

				if($type === 'file' && apply_filters('thwcfe_csv_export_display_only_the_name_of_uploaded_file', true, $key)){
					$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
				}else{
					$value = is_array($value) ? implode(", ", $value) : $value;
				}
				
				$customer_data[$key] = $value;
			}
		}

		return $customer_data;
	}

	/**
	 * Get all checkout fields
	 */
	private function get_fields() {
		/*$fields = array();

		$billing_fields = $this->get_checkout_fields('billing');
		if($billing_fields !== false){
			$fields = array_merge( $fields, $billing_fields );
		}

		$shipping_fields = $this->get_checkout_fields('shipping');
		if($shipping_fields !== false){
			$fields = array_merge( $fields, $shipping_fields );
		}

		$additional_fields = $this->get_checkout_fields('additional');
		if($additional_fields !== false){
			$fields = array_merge( $fields, $additional_fields );
		}*/
		$fields = $this->get_export_fields();
		return $fields;
	}
	
	public function get_export_fields(){
		$fields = array();
		$export_fields_str = $this->get_settings('csv_export_columns');
		
		if(!empty($export_fields_str)){
			$export_fields_arr = explode(",", $export_fields_str);
			
			if(is_array($export_fields_arr) && !empty($export_fields_arr)){
				$sections = $this->get_checkout_sections();	
				
				if($sections){
					foreach($sections as $sname => $section){	
						$temp_fields = THWCFE_Utils_Section::get_fields($section);
						if($temp_fields && is_array($temp_fields)){
							foreach($temp_fields as $key => $field){
								if(THWCFE_Utils_Field::is_custom_field($field) && THWCFE_Utils_Field::is_enabled($field) && in_array($key, $export_fields_arr)){
									$exp_field = array();
									$exp_field['name'] = $field->get_property('name');
									$exp_field['type'] = $field->get_property('type');
									$exp_field['title'] = $field->get_property('title');
									$exp_field['order_meta'] = $field->get_property('order_meta');
									$exp_field['user_meta'] = $field->get_property('user_meta');
									
									$fields[$key] = $exp_field;
								}
							}
						}
					}
				}
			}
		}
		return $fields;
	}
}

endif;
new WCFE_Checkout_Fields_Export_Handler();