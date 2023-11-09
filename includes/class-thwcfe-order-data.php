<?php
/**
 *
 * @link       https://themehigh.com
 * @since      3.0.6.8
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Order_Data')):
 
class THWCFE_Order_Data {
	const VIEW_ADMIN_ORDER    = 'admin_order';
	const VIEW_CUSTOMER_ORDER = 'customer_order';
	const VIEW_ORDER_EMAIL    = 'emails';

	public function __construct() {
		$this->order_meta_fields_admin();
		$this->order_meta_fields_customer();
		$this->order_meta_fields_email();
		
				
	}
	
	public function order_meta_fields_admin(){
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'admin_order_data_after_order_details'), 20, 1);
		add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'admin_order_data_after_billing_address'), 20, 1);
		add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'admin_order_data_after_shipping_address'), 20, 1);
		
		add_action( 'add_meta_boxes', array($this, 'add_meta_boxes' ));
		add_action( 'woocommerce_process_shop_order_meta', array($this, 'thwcfe_save_billing_details' ));
		
	}

	public function order_meta_fields_customer(){
		add_action('woocommerce_order_details_after_order_table', array($this, 'display_custom_fields_in_order_details_page_customer'), 20, 1);
		// WC subscriptions - Display custom fields in my account page
		add_action('woocommerce_subscription_details_after_subscription_table', array($this, 'display_custom_fields_in_order_details_page_customer'), 20, 1);
	}

	public function order_meta_fields_email(){
		add_filter('woocommerce_email_customer_details_fields', array($this, 'woo_hide_default_customer_fields_in_emails'), 10, 3);

		add_action('woocommerce_email_customer_details', array($this, 'woo_email_customer_details'), 15, 4);
		add_filter('woocommerce_email_customer_details_fields', array($this, 'woo_email_customer_details_fields'), 10, 3);
		add_action('woocommerce_email_order_meta', array($this, 'woo_email_order_meta'), 20, 4);
		$hp_email_order_meta_fields = apply_filters('thwcfe_email_order_meta_fields_priority', 10);
		add_filter('woocommerce_email_order_meta_fields', array($this, 'woo_email_order_meta_fields'), $hp_email_order_meta_fields, 3);
	}

	public function add_meta_boxes() {
		if(apply_filters('thwcfe_edit_order_custom_checkout_fields', false)){

			add_meta_box(sanitize_title( 'thwcfe_custom_fields' ), __('Custom Checkout Fields','woocommerce-checkout-field-editor-pro'), array($this, 'thwcfe_custom_file_upload_field'), 'shop_order', 'normal', 'core');
		}
    }

    public function thwcfe_save_billing_details($order_id){
    	if(apply_filters('thwcfe_edit_order_custom_checkout_fields', false)){

	    	$order = wc_get_order( $order_id );
	    	$args = $this->prepare_args_admin_order_view();
			$sections = $this->get_order_meta_sections($order, self::VIEW_ADMIN_ORDER, $args);
			$customer_id  = $order->get_user_id();
			
			foreach ($sections as $sname => $section_data) {
				$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
				foreach ($fields as $fname => $field) {				
					$type = $field->type;
					$field_arr = (array)$field;
					if($type === 'file' ){
						// update_post_meta( $order_id, $fname, wc_clean( $_POST[ $fname ] ) );
						$order->update_meta_data($fname, wc_clean( $_POST[ $fname ] ));
						update_user_meta( $customer_id, $fname, wc_clean( $_POST[ $fname ] ) );
					}
				}
			}
			$order->save();
		}
    }

    public function thwcfe_custom_file_upload_field(){
    	global $post;

    	$order = wc_get_order( $post->ID );
    	$args = $this->prepare_args_admin_order_view();
    	$sections = $this->get_order_meta_sections($order, self::VIEW_ADMIN_ORDER, $args);
		foreach ($sections as $sname => $section_data) {
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			foreach ($fields as $fname => $field) {
				
				$type = $field->type;
				$field_arr = (array)$field;
				if($type === 'file' ){
					$value = (new THWCFE_Functions)->get_custom_order_meta($post->ID, $fname);
					
					$html = '';
					$html .= '<table class="form-table" role="presentation"><tbody><tr class ="thwcfe-input-field-wrapper"><th scope="row"><label class ="thwcfe-order-label">'.$field->title.'</label></th>';
    				$html .='<td>'.THWCFE_Utils::prepare_file_preview_html($value).'';
    				$html .= THWCFE_Utils::form_field_file_html($fname,$field_arr,$value).'</td></tr></tbody></table>';

    				echo $html;
				}
			}
		}
    }


	/******************************************************
	 **** DISPLAY CUSTOM FIELDS IN ADMIN ORDER - START ****
	 ******************************************************/
	public function admin_order_data_after_order_details($order){
		$html = '';
		$args = $this->prepare_args_admin_order_view();
		$args['exclude']   = array('billing', 'shipping');
		$args['no_repeat'] = array('additional');
		$sections = $this->get_order_meta_sections($order, self::VIEW_ADMIN_ORDER, $args);

		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;

			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_ADMIN_ORDER, $args);

				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_ADMIN_ORDER);
					$html .= $fields_html;
				}
			}
		}

		if($html){
			echo '<p style="clear: both; margin: 0 !important;"></p>';
			echo $html;
		}
	}

	public function admin_order_data_after_billing_address($order){
		$args = $this->prepare_args_admin_order_view();
		$fields = $this->get_order_meta_fields_by_section_name($order, 'billing', self::VIEW_ADMIN_ORDER, $args);
		echo '<div style="clear:both; padding:5px 0 0;">';
		echo $this->get_order_meta_fields_html($order, $fields, self::VIEW_ADMIN_ORDER, $args);
		echo '</div>';
	}
	
	public function admin_order_data_after_shipping_address($order){
		if(!THWCFE_Utils::is_ship_to_billing($order)){
			$args = $this->prepare_args_admin_order_view();
			$fields = $this->get_order_meta_fields_by_section_name($order, 'shipping', self::VIEW_ADMIN_ORDER, $args);
			echo '<div style="clear:both; padding:5px 0 0;">';
			echo $this->get_order_meta_fields_html($order, $fields, self::VIEW_ADMIN_ORDER, $args);
			echo '</div>';
		}
	}

	private function prepare_args_admin_order_view(){
		$args = array(
			'fname_prefix'   => '',
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_admin_order', false),
		);
		return $args;
	}
	/****************************************************
	 **** DISPLAY CUSTOM FIELDS IN ADMIN ORDER - END ****
	 ****************************************************/


	/**********************************************************
	 **** DISPLAY CUSTOM FIELDS IN CUSTOMERT ORDER - START ****
	 **********************************************************/
	public function display_custom_fields_in_order_details_page_customer($order){
		$args = array(
			'fname_prefix'   => '',
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_thankyou_page', false),
			'exclude'        => array(),
			'no_repeat'      => array('billing', 'shipping', 'additional'),
		);

		$sections = $this->get_order_meta_sections($order, self::VIEW_CUSTOMER_ORDER, $args);

		$html = '';
		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_CUSTOMER_ORDER);
				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_CUSTOMER_ORDER);
					$html .= $fields_html;
				}
				
			}
		}

		if($html){
			do_action( 'thwcfe_order_details_before_custom_fields_table', $order ); 
			?>
			<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
				<?php
					do_action( 'thwcfe_order_details_before_custom_fields', $order );
					echo $html;
					do_action( 'thwcfe_order_details_after_custom_fields', $order ); 
				?>
			</table>
			<?php
			do_action( 'thwcfe_order_details_after_custom_fields_table', $order ); 
		}
	}
	/********************************************************
	 **** DISPLAY CUSTOM FIELDS IN CUSTOMERT ORDER - END ****
	 ********************************************************/


	/*************************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - START ****
	 *************************************************/
	public function woo_hide_default_customer_fields_in_emails($ofields, $sent_to_admin, $order){
		try{
			$fieldset = WCFE_Checkout_Fields_Utils::get_all_checkout_fields();
			$default_fields = array('customer_note', 'billing_email', 'billing_phone');
			
			if($fieldset && is_array($fieldset)){
				foreach($default_fields as $name) {
					if(isset($fieldset[$name])){
						$field = $fieldset[$name];
						
						if(THWCFE_Utils_Field::is_valid_field($field)){	
							$show_field = false;

							if($sent_to_admin && $field->get_property('show_in_email')){
								$show_field = true;			
							}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
								$show_field = true;
							}
							
							if(!$show_field){
								unset($ofields[$name]);
							}
						}
					}
				}
			}
		}catch(Exception $e){
			//THWCFE_Utils::write_log('Error in WCFE Utils', $e);
		}
		return $ofields;
	}

	public function woo_email_customer_details($order, $sent_to_admin = false, $plain_text = false, $email = false){
		$settings     = THWCFE_Utils::get_advanced_settings();
		$position     = THWCFE_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFE_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position === 'woocommerce_email_customer_details_fields' && $html_enabled){
			$this->display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email);
		}
	}

	public function woo_email_customer_details_fields($ofields, $sent_to_admin = false, $order = false){
		$settings     = THWCFE_Utils::get_advanced_settings();
		$position     = THWCFE_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFE_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position === 'woocommerce_email_customer_details_fields' && !$html_enabled){
			$ofields = $this->prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order);
		}
		return $ofields;
	}

	public function woo_email_order_meta($order, $sent_to_admin = false, $plain_text = false, $email = false){
		$settings     = THWCFE_Utils::get_advanced_settings();
		$position     = THWCFE_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFE_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position != 'woocommerce_email_customer_details_fields' && $html_enabled){
			$this->display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email);
		}
	}

	public function woo_email_order_meta_fields($ofields, $sent_to_admin = false, $order = false){
		$settings     = THWCFE_Utils::get_advanced_settings();
		$position     = THWCFE_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFE_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position != 'woocommerce_email_customer_details_fields' && !$html_enabled){
			$ofields = $this->prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order);
		}
		return $ofields;
	}

	private function prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order){
		$custom_fields = array();
		$args          = $this->prepare_args_order_email($sent_to_admin);
		$sections      = $this->get_order_meta_sections($order, self::VIEW_ORDER_EMAIL, $args);
		$order_id      = THWCFE_Utils::get_order_id($order);
		
		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields)){
				foreach($fields as $name => $field) {
					if(THWCFE_Utils::is_wc_handle_custom_field($field)){
						continue;
					}				
					//if($this->is_show_field($field, self::VIEW_ORDER_EMAIL, $args)){
						$type = $field->get_property('type');

						if(!THWCFE_Utils_Field::is_html_field($type)){
							$field_data = $this->prepare_single_field_data($order_id, $name, $field, self::VIEW_ORDER_EMAIL, $args);
							$custom_fields[$name] = $field_data;
						}
					//}
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}

	private function display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email){
		$html = '';
		$args = $this->prepare_args_order_email($sent_to_admin);
		$args['plain_text'] = $plain_text;
		$sections = $this->get_order_meta_sections($order, self::VIEW_ORDER_EMAIL, $args);
		
		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_ORDER_EMAIL, $args);

				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_ORDER_EMAIL);
					$html .= $fields_html;
				}
			}
		}

		if($html){
			echo $html;
		}	
	}

	private function prepare_args_order_email($sent_to_admin){
		$args = array(
			'sent_to_admin'  => $sent_to_admin,
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_email', false),
			'exclude'        => array(),
			'no_repeat'      => array('billing', 'shipping', 'additional'),
		);
		return $args;
	}
	/***********************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - END ****
	 ***********************************************/

	/***********************************************
	 **** DISPLAY SECTION TITLE - START ****
	 ***********************************************/
	private function is_show_section_title($section, $context){
		$show = true;

		if($context === self::VIEW_ADMIN_ORDER){

		}elseif($context === self::VIEW_CUSTOMER_ORDER){

		}elseif($context === self::VIEW_ORDER_EMAIL){
			
		}

		return apply_filters('thwcfe_show_section_title_in_'.$context, $show, $section->name);
	}

	private function get_section_title_html($section, $context=false){
		$html = '';

		if($this->is_show_section_title($section, $context)){
			$title = $section->get_property('title');

			if($title){
				$title    = __($title, 'woocommerce-checkout-field-editor-pro');
				$subtitle = $section->get_property('subtitle');
				$subtitle = apply_filters('thwcfe_section_subtitle', $subtitle, $section->name, $context);
				$subtitle = $subtitle ? __($subtitle,'woocommerce-checkout-field-editor-pro') : '';

				if($context === self::VIEW_ADMIN_ORDER){
					$html = $this->get_section_title_html_admin_order($title, $subtitle);

				}elseif($context === self::VIEW_CUSTOMER_ORDER){
					$html = $this->get_section_title_html_customer_order($title, $subtitle);

				}elseif($context === self::VIEW_ORDER_EMAIL){
					$html = $this->get_section_title_html_order_emails($title, $subtitle);
				}
			}
		}
		
		return $html;
	}

	private function get_section_title_html_admin_order($title, $subtitle){
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		$html = '<h3>'. $title .'</h3>';
		return $html;
	}

	private function get_section_title_html_customer_order($title, $subtitle){
		$html = '';
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		if(is_account_page() && apply_filters('thwcfe_display_section_title_customer_order',true)){
			$html = '<tr><th colspan="2" class="thwcfe-section-title">'. $title .'</th></tr>';
		}else if(apply_filters('thwcfe_display_section_title_customer_order',true)){
			$html = '<tr><th colspan="2" class="thwcfe-section-title">'. $title .'</th></tr>';
		}
		return $html;
	}

	private function get_section_title_html_order_emails($title, $subtitle){
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		$html = '<h3>'. $title .'</h3>';
		return $html;
	}
	/***********************************************
	 **** DISPLAY SECTION TITLE - END ****
	 ***********************************************/

	/***********************************************
	 **** DISPLAY SECTION FIELDS - START ****
	 ***********************************************/
	private function is_show_field($field, $context, $args=array()){
		$show = true;

		if($context === self::VIEW_ADMIN_ORDER){
			$show = $field->get_property('show_in_order');

		}elseif($context === self::VIEW_CUSTOMER_ORDER){
			$show = $field->get_property('show_in_thank_you_page');

		}elseif($context === self::VIEW_ORDER_EMAIL){
			$sent_to_admin = isset($args['sent_to_admin']) ? $args['sent_to_admin'] : false;

			if($sent_to_admin){
				$show = $field->get_property('show_in_email');					
			}else{
				$show = $field->get_property('show_in_email_customer');
			}
		}
		$show = apply_filters('thwcfe_show_field_order_data', $show, $field, $context, $args);

		return $show;
	}

	private function get_order_meta_fields_html($order, $fields, $context=false, $args=array()){
		$html = '';

		if(is_array($fields)){
			$order_id = THWCFE_Utils::get_order_id($order);
			
			$defaults = array(
				'fname_prefix'   => '',
				'is_nl2br'       => true,
				'esc_attr_label' => false,
			);
			$args = wp_parse_args( $args, $defaults );
			
			foreach($fields as $name => $field){
				if(THWCFE_Utils::is_wc_handle_custom_field($field)){
					continue;
				}
				//$show  = $field->get_property('show_in_order');

				//if($this->is_show_field($field, $context, $args)){
					$field_data = $this->prepare_single_field_data($order_id, $name, $field, $context, $args);

					if($context === self::VIEW_ADMIN_ORDER){
						$html .= $this->get_single_field_html_admin_order($field_data);

					}elseif($context === self::VIEW_CUSTOMER_ORDER){
						$html .= $this->get_single_field_html_customer_order($field_data);

					}elseif($context === self::VIEW_ORDER_EMAIL){
						$html .= $this->get_single_field_html_order_emails($field_data, $args);
					}
					//$html .= $this->display_single_field_in_admin_order($field_data);
				//}
			}
		}
		return $html;
	}

	private function get_single_field_html_admin_order($field){
		$html = '';
		$type = isset($field['type']) ? $field['type'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}
			if(!empty($label)){
				if($type === 'heading'){
					$html .= '<h3>'. $label .'</h3>';
				}else{
					$html .= '<p><strong>'. $label .'</strong></p>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;

			if(!empty($label) && !empty($value)){
				$html .= '<p><strong>'. $label .':</strong> '. $value .'</p>';
			}
		}

		return $html;
	}

	private function get_single_field_html_customer_order($field){
		$html = '';
		$type = isset($field['type']) ? $field['type'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}

			if(!empty($label)){
				if(is_account_page()){
					$html .= '<tr><th colspan="2" class="thwcfe-html-'.$type.'">'. $label .'</th></tr>';
				}else{
					$html .= '<tr><th colspan="2" class="thwcfe-html-'.$type.'">'. $label .'</th></tr>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;

			if(!empty($label) && !empty($value)){
				if(apply_filters( 'thwcfe_view_order_customer_details_table_view', true )){
					$html .= '<tr><td>'. $label .':</td><td>'. wptexturize($value) .'</td></tr>';
				}else{
					$html .= '<br/><dt>'. $label .':</dt><dd>'. wptexturize($value) .'</dd>';
				}
			}
		}

		return $html;
	}

	private function get_single_field_html_order_emails($field, $args=array()){
		$html  = '';
		$title = '';
		$value = '';
		$type  = isset($field['type']) ? $field['type'] : false;
		$plain_text = isset($args['plain_text']) ? $args['plain_text'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel && !$plain_text){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}

			if(!empty($label)){
				if($plain_text){
					$html = $label;
				}else{
					$html = '<p><strong>'.$label.'</strong></p>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;

			if(!empty($label) && !empty($value)){
				if($plain_text){
					$html = $label . ': ' . $value . "\n";
				}else{
					$html = '<p><strong>'. $label .':</strong> '. $value .'</p>';
				}
			}
		}

		$html = apply_filters('thwcfe_email_display_field_html', $html, $type, $label, $value);
		return $html;
	}

	/***********************************************
	 **** DISPLAY SECTION FIELDS - END ****
	 ***********************************************/

	/***********************************************
	 **** PREPARE SECTIONS & FIELDS - START ****
	 ***********************************************/
	private function get_order_meta_sections($order, $context, $args=array()){
		$sections = array();

		$sections = THWCFE_Utils::get_custom_sections();
		$sections = THWCFE_Utils::sort_sections($sections);
		if(is_array($sections)){
			$order_id     = THWCFE_Utils::get_order_id($order);
			$exclude      = isset($args['exclude']) ? $args['exclude'] : array();
			$dis_sections = $this->get_disabled_sections($order_id);
			$dis_sections = array_merge($dis_sections, $exclude);

			$args['exclude'] = $dis_sections;
			$sections = $this->prepare_order_meta_sections($order, $order_id, $sections, $context, $args);
		}
		return $sections;
	}

	private function prepare_order_meta_sections($order, $order_id, $sections, $context, $args){
		$final_sections = array();

		if(is_array($sections)){
			$cart_info = THWCFE_Utils::get_order_summary($order);
			$rsnames   = THWCFE_Utils_Repeat::get_repeat_section_names($order_id);

			$dis_sections = isset($args['exclude']) ? $args['exclude'] : array();
			$no_repeat    = isset($args['no_repeat']) ? $args['no_repeat'] : array();

			$args['rsnames'] = $rsnames;

			foreach($sections as $sname => $section){
				if($this->is_show_section($order, $section, $context, $cart_info, $dis_sections)){
					$section_data = $this->prepare_section_data($order, $section, $context, $args);
					$final_sections[$sname] = $section_data;

					if(!in_array($sname, $no_repeat)){
						$repeat_sections = $this->prepare_order_meta_repeat_sections($order, $order_id, $sname, $section, $context, $args);
						$final_sections  = array_merge($final_sections, $repeat_sections);
					}
				}
			}
		}

		return $final_sections;
	}

	private function prepare_order_meta_repeat_sections($order, $order_id, $name, $section, $context, $args){
		$repeat_sections = array();
		$rsnames   = isset($args['rsnames']) ? $args['rsnames'] : false;
		$rsections = THWCFE_Utils_Repeat::get_repeat_sections($order_id, $name, $section, $rsnames);

		if(is_array($rsections)){
			foreach($rsections as $rname => $rsection) {
				$rsection_data = $this->prepare_section_data($order, $rsection, $context, $args);
				$repeat_sections[$rname] = $rsection_data;
			}
		}

		return $repeat_sections;
	}

	private function prepare_section_data($order, $section, $context, $args){
		$section_data = false;
		$fields = $this->get_order_meta_fields($order, $section, $context, $args);

		if(!empty($fields)){
			$section_data = array(
				'section'  => $section,
				'fields'   => $fields,
			);
		}

		return $section_data;
	}

	private function is_show_section($order, $section, $context, $cart_info, $exclude=array()){
		$show = false;
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$name = $section->get_property('name');

			if($name === 'shipping' && THWCFE_Utils::is_ship_to_billing($order)){
				return false;
			}

			if(THWCFE_Utils_Section::is_show_section($section, $cart_info)){
				if(!in_array($name, $exclude)){
					$show = true;
				}
			}
		}
		return $show;
	}
	/***********************************************
	 **** PREPARE SECTIONS & FIELDS - END ****
	 ***********************************************/


	/***********************************************
	 **** PREPARE SECTION FIELDS - START ****
	 ***********************************************/
	private function get_order_meta_fields_by_section_name($order, $sname, $context, $args){
		//$section = WCFE_Checkout_Fields_Utils::get_checkout_section($sname);
		$section = THWCFE_Utils::get_checkout_section($sname);
		$fields  = $this->get_order_meta_fields($order, $section, $context, $args);
		return $fields;
	}

	private function get_order_meta_fields($order, $section, $context, $args){
		$fields = array();

		if($section){
			$order_id   = THWCFE_Utils::get_order_id($order);
			$cart_info  = THWCFE_Utils::get_order_summary($order);
			$fields     = THWCFE_Utils_Section::get_fields($section, $cart_info, true);
			$dis_fields = $this->get_disabled_fields($order_id);
			$fields     = $this->prepare_order_meta_fields($order_id, $fields, $dis_fields, $context, $args);
		}
		return $fields;
	}

	private function prepare_order_meta_fields($order_id, $fields, $dis_fields, $context, $args){
		$final_fields = array();

		if(is_array($fields)){
			$rfnames = THWCFE_Utils_Repeat::get_repeat_field_names($order_id);

			foreach($fields as $name => $field){
				//if( THWCFE_Utils_Field::is_custom_enabled($field) && !in_array($name, $dis_fields)){
				if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_enabled($field) && !in_array($name, $dis_fields)){
					if($this->is_show_field($field, $context, $args)){
						$final_fields[$name] = $field;
						$repeat_fields = $this->prepare_order_meta_repeat_fields($order_id, $name, $field, $rfnames);
						$final_fields  = array_merge($final_fields, $repeat_fields);
					}
				}

				//TODO check against conditional rules when original fields have display rules
				//$repeat_fields = $this->prepare_order_meta_repeat_fields($order_id, $name, $field, $rfnames);
				//$final_fields  = array_merge($final_fields, $repeat_fields);
			}
		}

		return $final_fields;
	}

	private function prepare_order_meta_repeat_fields($order_id, $name, $field, $rfnames){
		$repeat_fields = array();
		$fields = THWCFE_Utils_Repeat::get_repeat_fields($order_id, $name, $field, $rfnames);

		if(is_array($fields)){
			foreach($fields as $name => $field) {
				$repeat_fields[$name] = $field;
			}
		}

		return $repeat_fields;
	}

	private function prepare_single_field_data($order_id, $name, $field, $context, $args){
		$order = wc_get_order( $order_id );
		$fname_prefix   = isset($args['fname_prefix']) ? $args['fname_prefix'] : '';
		$is_nl2br       = isset($args['is_nl2br']) ? $args['is_nl2br'] : true;
		$esc_attr_label = isset($args['esc_attr_label']) ? $args['esc_attr_label'] : false;

		$type = $field->get_property('type');

		$field_data = array();
		$field_data['name'] = $name;
		$field_data['type'] = $type;
					
		if($type === 'label' || $type === 'heading' || $type === 'paragraph'){
			$title    = $field->get_property('title') ? $field->get_property('title') : false;
			$subtitle = $field->get_property('subtitle') ? $field->get_property('subtitle') : false;

			if($title || $subtitle){
				if($esc_attr_label){
					$title    = $title ? __($title,'woocommerce-checkout-field-editor-pro') : '';
					$subtitle = $subtitle ? __($subtitle,'woocommerce-checkout-field-editor-pro') : '';
				}else{
					$title    = $title ? __($title,'woocommerce-checkout-field-editor-pro') : '';
					$subtitle = $subtitle ? __($subtitle,'woocommerce-checkout-field-editor-pro') : '';
				}

				$field_data['label'] = $title;
				$field_data['sublabel'] = $subtitle;
			}
		}else{
			// $value = get_post_meta( $order_id, $fname_prefix.$name, true );
			$value = $order->get_meta( $fname_prefix.$name, true );

			if(!empty($value)){
				$title = $field->get_property('title') ? $field->get_property('title') : $name;
				$title = $esc_attr_label ? __($title, 'woocommerce-checkout-field-editor-pro') : __($title, 'woocommerce-checkout-field-editor-pro');

				if($type === 'file'){
					$value = $this->get_field_display_value_file($name, $value, $context);

				}else{
					$value = THWCFE_Utils::get_option_text_from_value($field, $value);
					$value = is_array($value) ? implode(",", $value) : $value;

					if($type === 'multiselect' || $type === 'checkboxgroup'){
						$value = $this->get_field_display_value_multi_option($name, $value, $context);
					}

					if($is_nl2br && $type === 'textarea'){
						$value = nl2br($value);

					}else{
						$value = esc_html($value);
					}
				}
				if($type === 'checkboxgroup' || $type === 'radio'){
					$value = html_entity_decode($value);
				}
				
				$field_data['label'] = $title;
				//$field_data['sublabel'] = $subtitle;
				$field_data['value'] = $value;					
			}
		}
		return $field_data;
	}

	private function get_field_display_value_file($name, $value, $context=false){
		$downloadable = true;

		if($context === self::VIEW_ADMIN_ORDER){
			$downloadable = apply_filters('thwcfe_clickable_filename_in_order_admin_view', true, $name);

		}elseif($context === self::VIEW_CUSTOMER_ORDER){
			$downloadable = apply_filters('thwcfe_clickable_filename_in_order_view', true, $name);

		}elseif($context === self::VIEW_ORDER_EMAIL){
			$downloadable = apply_filters('thwcfe_clickable_filename_in_order_emails', true, $name);
		}

		$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, $downloadable);
		return $value;
	}

	private function get_field_display_value_multi_option($name, $value, $context=false){
		$separate_lines = apply_filters('thwcfe_align_field_value_in_separate_lines', false);

		if($context === self::VIEW_ADMIN_ORDER){
			$separate_lines = apply_filters('thwcfe_align_field_value_in_separate_lines_admin', false);

		}elseif($context === self::VIEW_CUSTOMER_ORDER){
			$separate_lines = apply_filters('thwcfe_align_field_value_in_separate_lines_customer', false);

		}elseif($context === self::VIEW_ORDER_EMAIL){
			$separate_lines = apply_filters('thwcfe_align_field_value_in_separate_lines_emails', false);
		}

		if($separate_lines){
			$value = str_replace(",", ",<br/>", $value);
		}

		return $value;
	}

	private function get_disabled_sections($order_id){
		$order = wc_get_order( $order_id );
		// $dis_sections = get_post_meta($order_id, '_thwcfe_disabled_sections', true);
		$dis_sections = $order->get_meta('_thwcfe_disabled_sections', true );

		if(is_string($dis_sections) && $dis_sections){
			$dis_sections = explode(",", $dis_sections);
		}

		$dis_sections = is_array($dis_sections) ? $dis_sections : array();
		return $dis_sections;
	}
	
	private function get_disabled_fields($order_id){
		$order = wc_get_order( $order_id );
		// $dis_fields = get_post_meta( $order_id, '_thwcfe_disabled_fields', true );
		$dis_fields = $order->get_meta('_thwcfe_disabled_fields', true );

		if(is_string($dis_fields) && $dis_fields){
			$dis_fields = explode(",", $dis_fields);
		}

		$dis_fields = is_array($dis_fields) ? $dis_fields : array();
		return $dis_fields;
	}
	/***********************************************
	 **** PREPARE SECTION FIELDS - END ****
	 ***********************************************/
}

endif;