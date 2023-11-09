<?php

use OM4\Zapier\Trigger\Base;

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_WC_Zapier_Handler')):

class WCFE_WC_Zapier_Handler extends WCFE_Checkout_Fields_Utils{
	private $trigger_keys = array(
		'wc.new_order', // New Order
		'wc.order_status_change' // New Order Status Change
	);

	public function __construct() {
		$zapier_version = $this->get_zapier_version();

		if($zapier_version && $zapier_version < '1.9.0'){
			foreach ( $this->trigger_keys as $trigger_key ) {
				add_filter( "wc_zapier_data_{$trigger_key}", array( $this, 'zapier_data_override_legacy' ), 10, 4 );
			}
			add_action( "thwcfe-checkout-fields-updated", array( $this, 'checkout_fields_updated_legacy' ), 10, 0 );
		}else if($zapier_version && $zapier_version < '2.0.0'){
			foreach ( $this->trigger_keys as $trigger_key ) {
				add_filter( "wc_zapier_data_{$trigger_key}", array( $this, 'zapier_data_override' ), 20, 2 );
			}
			add_action( "thwcfe-checkout-fields-updated", array( $this, 'checkout_fields_updated' ), 10, 0 );
		}

		/*
		else if($zapier_version && $zapier_version < '1.9.4'){
			foreach ( $this->trigger_keys as $trigger_key ) {
				add_filter( "wc_zapier_data_{$trigger_key}", array( $this, 'zapier_data_override_194' ), 20, 2 );
			}
			add_action( "thwcfe-checkout-fields-updated", array( $this, 'checkout_fields_updated' ), 10, 0 );
		}
		*/
	}

	private function get_zapier_version(){
		$data = get_plugin_data( WP_PLUGIN_DIR."/woocommerce-zapier/woocommerce-zapier.php", false, false );
		if(is_array($data) && isset($data['Version'])){
			return $data['Version'];
		}
		return false;
	}

	private function get_order_meta_value($name, $field, $order_id){
		$value = '';
		$order = wc_get_order( $order_id );

		if(THWCFE_Utils_Field::is_valid_field($field)){
			$type = $field->get_property('type');

			if($field->get_property('order_meta')){
				// $value = get_post_meta( $order_id, $name, true );
				$value = $order->get_meta( $name, true );
			}else if($field->get_property('user_meta')){
				//$value = get_user_meta( $user_id, $name, true );
			}

			if($type === 'file' && apply_filters('thwcfe_zapier_display_only_the_name_of_uploaded_file', true, $name)){
				$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
			}else{
				//$value = THWCFE_Utils::get_option_text_from_value($field, $value);
				$value = is_array($value) ? implode(", ", $value) : $value;
			}
		}

		return $value;
	}

	/**
	 * When sending WooCommerce Order data to Zapier, also send any additional checkout fields
	 * that have been created by the Checkout Field Editor plugin.
	 *
	 * @param         array  $order_data Order data that will be overridden.
	 * @param Base    $trigger Trigger that initiated the data send.
	 *
	 * @return mixed
	 */
	public function zapier_data_override( $order_data, Base $trigger ) {
		$sections = $this->get_checkout_sections();
		$order_id = $order_data['id'];
		
		$checkout_payload = new Payload();
		if($order_id && $sections && is_array($sections)){
			foreach($sections as $sname => $section){
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					if($fields){
						foreach($fields as $name => $field){	
							if(THWCFE_Utils_Field::is_enabled($field) && ! isset( $order_data[$name] ) ) {
								if ( $trigger->is_sample() ) {
									// Sending sample data: Send the label of the custom checkout field as the field's value.
									$checkout_payload->$field_name = $field->get_property('title');
								} else {
									// Sending real data: Send the saved value of this checkout field.
									// If the order doesn't contain this custom field, an empty string will be used as the value.
									$checkout_payload->$field_name = $this->get_order_meta_value($name, $field, $order_id);
								}
							}
						}
					}
				}
			}
		}
		return $order_data + $checkout_payload->to_array();
	}

	public function zapier_data_override_194( $order_data, Base $trigger ) {
		$sections = $this->get_checkout_sections();
		$order_id = $order_data['id'];
		
		if($order_id && $sections && is_array($sections)){
			foreach($sections as $sname => $section){
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					if($fields){
						foreach($fields as $name => $field){	
							if(THWCFE_Utils_Field::is_enabled($field) && ! isset( $order_data[$name] ) ) {
								if ( $trigger->is_sample() ) {
									// Sending sample data: Send the label of the custom checkout field as the field's value.
									$order_data[$name] = $field->get_property('title');
								} else {
									// Sending real data: Send the saved value of this checkout field.
									// If the order doesn't contain this custom field, an empty string will be used as the value.
									$order_data[$name] = $this->get_order_meta_value($name, $field, $order_id);
								}
							}
						}
					}
				}
			}
		}
		return $order_data;
	}

	/**
	 * Executed whenever the checkout fields are updated/saved.
	 * Schedule the feed refresh to occur asynchronously.
	 */
	public function checkout_fields_updated( ) {
		$wc_zapier = WC_Zapier();
		$wc_zapier::resend_sample_data_async( $this->trigger_keys );
	}

	/*****************************************
	 ****** ZAPIER OLD VERSION SUPPORT *******
	 *****************************************/

	/**
	 * When sending WooCommerce Order data to Zapier, also send any additional checkout fields
	 * that have been created by the Checkout Field Editor plugin.
	 *
	 * @param             array  $order_data Order data that will be overridden.
	 * @param WC_Zapier_Trigger  $trigger Trigger that initiated the data send.
	 *
	 * @return mixed
	 */
	public function zapier_data_override_legacy( $order_data, WC_Zapier_Trigger $trigger ) {
		$sections = $this->get_checkout_sections();
		$order_id = $order_data['id'];
		
		if($order_id && $sections && is_array($sections)){
			foreach($sections as $sname => $section){
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					if($fields){
						foreach($fields as $name => $field){	
							if(THWCFE_Utils_Field::is_enabled($field) && ! isset( $order_data[$name] ) ) {
								if ( $trigger->is_sample() ) {
									// Sending sample data: Send the label of the custom checkout field as the field's value.
									$order_data[$name] = $field->get_property('title');
								} else {
									// Sending real data: Send the saved value of this checkout field.
									// If the order doesn't contain this custom field, an empty string will be used as the value.
									$order_data[$name] = $this->get_order_meta_value($name, $field, $order_id);
								}
							}
						}
					}
				}
			}
		}
		return $order_data;
	}

	public function checkout_fields_updated_legacy( ) {
		WC_Zapier::resend_sample_data_async( $this->trigger_keys );
	}

	/***********************************************
	 ****** ZAPIER OLD VERSION SUPPORT - END *******
	 ***********************************************/

}

endif;