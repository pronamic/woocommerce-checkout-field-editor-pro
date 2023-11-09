<?php
if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_WC_PDF_Invoices_Packing_Slips_Handler')):

class WCFE_WC_PDF_Invoices_Packing_Slips_Handler extends WCFE_Checkout_Fields_Utils{
	const DISPLAY_TABLE = 'table';
	const DISPLAY_ROW   = 'row';
	const DISPLAY_CELL  = 'cell';

	public function __construct() {
		//$display_position = apply_filters('thwcfe_wpo_wcpdf_display_hook', 'wpo_wcpdf_after_order_data');
		//add_action( $display_position, array($this, 'display_custom_fields'), 10, 2 );

		add_action('wpo_wcpdf_before_order_data', array($this, 'display_before_order_data'), 10, 2);
		add_action('wpo_wcpdf_after_order_data', array($this, 'display_after_order_data'), 10, 2);

		add_action('wpo_wcpdf_before_document', array($this, 'display_before_document'), 10, 2);
		add_action('wpo_wcpdf_after_document', array($this, 'display_after_document'), 10, 2);
		add_action('wpo_wcpdf_before_order_details', array($this, 'display_before_order_details'), 10, 2);
		add_action('wpo_wcpdf_after_order_details', array($this, 'display_after_order_details'), 10, 2);
		add_action('wpo_wcpdf_after_document_label', array($this, 'display_after_document_label'), 10, 2);

		add_action('wpo_wcpdf_before_billing_address', array($this, 'display_before_billing_address'), 10, 2);
		add_action('wpo_wcpdf_after_billing_address', array($this, 'display_after_billing_address'), 10, 2);
		add_action('wpo_wcpdf_before_shipping_address', array($this, 'display_before_shipping_address'), 10, 2);
		add_action('wpo_wcpdf_after_shipping_address', array($this, 'display_after_shipping_address'), 10, 2);
		//add_action('wpo_wcpdf_before_item_meta', array($this, 'display_before_item_meta'), 10, 3);
		//add_action('wpo_wcpdf_after_item_meta', array($this, 'display_after_item_meta'), 10, 3);

		add_action('wpo_wcpdf_before_customer_notes', array($this, 'display_before_customer_notes'), 10, 2);
		add_action('wpo_wcpdf_after_customer_notes', array($this, 'display_after_customer_notes'), 10, 2);
	}

	public function is_enabled($position){
		$enabled = $position === apply_filters('thwcfe_wpo_wcpdf_display_position', 'after_order_data') ? true : false;
		return $enabled; 
	}

	public function display_before_order_data($template_type, $order){
		if($this->is_enabled('before_order_data')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_ROW);
		}
	}
	public function display_after_order_data($template_type, $order){
		if($this->is_enabled('after_order_data')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_ROW);
		}
	}

	public function display_before_document($template_type, $order){
		if($this->is_enabled('before_document')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_after_document($template_type, $order){
		if($this->is_enabled('after_document')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_before_order_details($template_type, $order){
		if($this->is_enabled('before_order_details')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_after_order_details($template_type, $order){
		if($this->is_enabled('after_order_details')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_after_document_label($template_type, $order){
		if($this->is_enabled('after_document_label')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}

	public function display_before_billing_address($template_type, $order){
		if($this->is_enabled('before_billing_address')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_after_billing_address($template_type, $order){
		if($this->is_enabled('after_billing_address')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_before_shipping_address($template_type, $order){
		if($this->is_enabled('before_shipping_address')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	public function display_after_shipping_address($template_type, $order){
		if($this->is_enabled('after_shipping_address')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_TABLE);
		}
	}
	/*public function display_before_item_meta($template_type, $item, $order){
		if($this->is_enabled('before_item_meta')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_CELL);
		}
	}
	public function display_after_item_meta($template_type, $item, $order){
		if($this->is_enabled('after_item_meta')){
			$this->display_custom_fields($template_type, $order, self::DISPLAY_CELL);
		}
	}*/

	public function display_before_customer_notes($template_type, $order){
		if($this->is_enabled('before_customer_notes')){
			$display = $template_type == 'invoice' ? self::DISPLAY_CELL : self::DISPLAY_TABLE;
			$this->display_custom_fields($template_type, $order, $display);
		}
	}
	public function display_after_customer_notes($template_type, $order){
		if($this->is_enabled('after_customer_notes')){
			$display = $template_type == 'invoice' ? self::DISPLAY_CELL : self::DISPLAY_TABLE;
			$this->display_custom_fields($template_type, $order, $display);
		}
	}

	public function display_custom_fields($template_type, $order, $display='') {
		$fields = $this->get_display_fields($template_type);
		$html = $this->display_wcpdfips_custom_fields($order, $fields, $display);
		
		if($html){
			if($display === self::DISPLAY_ROW || $display === self::DISPLAY_CELL){
				echo $html;
			}else{
				echo '<table class="thwcfe-wcpdf-table" style="margin-bottom: 20px;"><tbody>';
				echo $html;
				echo '</tbody></table>';
			}
		}
	}

	/*public function display_custom_fields($template_type, $order) {
		if ($template_type == 'invoice') {
			$fields = $this->get_invoice_fields();
			$this->display_wcpdfips_custom_fields($order, $fields);
			
		}else if ($template_type == 'packing-slip') {
			$fields = $this->get_packing_slip_fields();
			$this->display_wcpdfips_custom_fields($order, $fields);
		}
	}*/
	
	public function display_wcpdfips_custom_fields($order, $fields, $display='') {
		$html = '';

		if(is_array($fields) && !empty($fields)){
			$user_id = $order->get_user_id();
			
			$order_id = false;
			if($this->woo_version_check()){
				$order_id = $order->get_id();
			}else{
				$order_id = $order->id;
			}
			$is_nl2br = apply_filters('thwcfe_nl2br_custom_field_value', true);
			
			foreach($fields as $key => $field) {
				$type = isset($field['type']) && $field['type'] ? $field['type'] : 'text';
				$value = '';
				if($user_id && isset($field['user_meta']) && $field['user_meta']){
					$value = get_user_meta( $user_id, $key, true );
				} else if(isset($field['order_meta']) && $field['order_meta']){
					// $value = get_post_meta( $order_id, $key, true );
					$value = $order->get_meta( $key, true );
				}

				if($type === 'file'){
					$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
				}else{
					//$value = THWCFE_Utils::get_option_text_from_value($field, $value);
					$value = is_array($value) ? implode(", ", $value) : $value;
				}
				
				//if($is_nl2br && $type === 'textarea'){
				if($is_nl2br){
					$value = nl2br($value);
				}

				if($value){
					if($display === self::DISPLAY_CELL){
						$html .= '<dl class="'.$key.'"><dt>'. __($field['title'], 'woocommerce-checkout-field-editor-pro') .':</dt><dd>'. $value .'</dd></dl>';
					}else{
						$html .= '<tr class="'.$key.'"><th>'. __($field['title'], 'woocommerce-checkout-field-editor-pro') .':</th><td>'. $value .'</td></tr>';
					}
				}else if($type === 'heading' || $type === 'label'){
					if($display === self::DISPLAY_CELL){
						$html .= '<dl class="'.$key.'"><dt>'. __($field['title'], 'woocommerce-checkout-field-editor-pro') .'</dt><dd></dd></dl>';
					}else{
						$html .= '<tr class="'.$key.'"><th colspan="2">'. __($field['title'], 'woocommerce-checkout-field-editor-pro') .'</th></tr>';
					}
				}
			}
		}

		return $html;
	}

	public function get_display_fields($template_type){
		$fields = array();

		if ($template_type == 'invoice') {
			$fields = $this->get_invoice_fields();

		}else if ($template_type == 'packing-slip') {
			$fields = $this->get_packing_slip_fields();
		}

		return is_array($fields) ? $fields : array();
	}
	
	public function get_invoice_fields(){
		return $this->get_fields('pdf_invoice_fields');
	}
	
	public function get_packing_slip_fields(){
		return $this->get_fields('pdf_packing_slip_fields');
	}
	
	public function get_fields($settings_name){
		$fields = array();
		$fields_str = $this->get_settings($settings_name);
		
		if(!empty($fields_str)){
			$fields_arr = explode(",", $fields_str);
			
			if(is_array($fields_arr) && !empty($fields_arr)){
				$sections = $this->get_checkout_sections();	
				
				if($sections){
					foreach($sections as $sname => $section){	
						$fieldset = THWCFE_Utils_Section::get_fields($section);
						
						if($fieldset && is_array($fieldset)){
							foreach($fieldset as $key => $field){
								if(THWCFE_Utils_Field::is_custom_field($field) && THWCFE_Utils_Field::is_enabled($field) && in_array($key, $fields_arr)){
									$nfield = array();
									$nfield['name'] = $field->get_property('name');
									$nfield['type'] = $field->get_property('type');
									$nfield['title'] = $field->get_property('title');
									$nfield['order_meta'] = $field->get_property('order_meta');
									$nfield['user_meta'] = $field->get_property('user_meta');
									
									$fields[$key] = $nfield;
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