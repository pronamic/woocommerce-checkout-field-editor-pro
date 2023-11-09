<?php
/**
 * The admin advanced settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Settings_Advanced')):

class THWCFE_Admin_Settings_Advanced extends THWCFE_Admin_Settings{
	protected static $_instance = null;

	private $imp_exp_settings = null;
	
	private $settings_options = NULL;
	private $left_cell_props = array();
	private $right_cell_props = array();
	private $checkbox_cell_props = array();

	public function __construct() {
		parent::__construct();

		$this->imp_exp_settings = new THWCFE_Admin_Settings_Import_Export();
		
		$this->page_id = 'advanced_settings';
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	} 	
	
	public function init_constants(){
		$this->left_cell_props = array( 
			'label_cell_props' => 'class="titledesc" scope="row" style="width: 20%;"', 
			'input_cell_props' => 'class="forminp"', 
			'input_width' => '250px', 
			'label_cell_th' => true 
		);
		
		$this->right_cell_props = array( 'label_cell_width' => '13%', 'input_cell_width' => '34%', 'input_width' => '250px' );
		$this->checkbox_cell_props = array( 'cell_props' => 'colspan="3"' );
		$this->settings_fields  = $this->get_advanced_settings_fields();
	}
	
	public function get_advanced_settings_fields(){
		$fields_position_email = array(
			'woocommerce_email_order_meta_fields' => 'Above customer details',
			'woocommerce_email_customer_details_fields' => 'Below customer details',
		);
		
		$default_address_fields = WCFE_Checkout_Fields_Utils::get_default_full_address_fields();
		
		$custom_billing_fields = array();
		$custom_shipping_fields = array();
		
		$sections = $this->get_checkout_sections();	
		if($sections){
			foreach($sections as $sname => $section){
				$custom_fields = array();
				if($sname === 'billing' || $sname === 'shipping'){
					$fieldset = THWCFE_Utils_Section::get_fields($section);
					if($fieldset && is_array($fieldset)){
						foreach($fieldset as $name => $field) {
							$fname = str_replace($sname.'_', '', $name);
							
							if($field && THWCFE_Utils_Field::is_valid_field($field) && !in_array($fname, $default_address_fields)){
								$label = $field->get_property('title');
								$label = empty($label) ? $name : $label;
								$custom_fields[$name] = $label;
							}
						}
					}
				}
				
				if($sname === 'billing'){
					$custom_billing_fields = $custom_fields;
				}
				if($sname === 'shipping'){
					$custom_shipping_fields = $custom_fields;
				}
			}
		}
		
		$custom_fields = $this->get_all_custom_fields();
		
		return array(
			'custom_fields_position_email' => array(
				'name'=>'custom_fields_position_email', 'label'=>'Fields display position in email', 'type'=>'select', 
				'value'=>'woocommerce_email_order_meta_fields', 'options'=>$fields_position_email
			),	
			'custom_shop_order_columns' => array(
				'name'=>'custom_shop_order_columns', 'label'=>'Custom shop order columns', 'type'=>'multiselect_grouped', 'options'=>$custom_fields, 'glue'=>':'
			),	
			'section_address_fields' => array('title'=>'Custom Address Fields', 'type'=>'separator', 'colspan'=>'3'),
			'custom_billing_address_keys' => array(
				'name'=>'custom_billing_address_keys', 'label'=>'Custom billing address keys', 'type'=>'multiselect', 'options'=>$custom_billing_fields
			),
			'custom_shipping_address_keys' => array(
				'name'=>'custom_shipping_address_keys', 'label'=>'Custom shipping address keys', 'type'=>'multiselect', 'options'=>$custom_shipping_fields
			),
			'address_formats' => array('name'=>'address_formats', 'label'=>'Address format overrides', 'type'=>'textarea'),
			'section_custom_validators' => array('title'=>'Custom validators', 'type'=>'separator', 'colspan'=>'3'),
			'custom_validators' => array(
				'name'=>'custom_validators', 'label'=>'Custom validators', 'type'=>'dynamic_options'
			),
			'confirm_validators' => array(
				'name'=>'confirm_validators', 'label'=>'Confirm field validators', 'type'=>'dynamic_options', 'prefix'=>'cnf'
			),
			'section_csv_export' => array('title'=>'CSV Export Fields', 'type'=>'separator', 'colspan'=>'3'),
			'enable_csv_export_support' => array(
				'name'=>'enable_csv_export_support', 'label'=>'Enable CSV Export support.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'csv_export_columns' => array(
				'name'=>'csv_export_columns', 'label'=>'CSV export columns', 'type'=>'multiselect_grouped', 'options'=>$custom_fields
			),
			'section_pdf_invoice' => array('title'=>'PDF Invoice & Packing Slip Fields', 'type'=>'separator', 'colspan'=>'3'),
			'enable_wcpdf_invoice_packing_slip_support' => array(
				'name'=>'enable_wcpdf_invoice_packing_slip_support', 'label'=>'Enable PDF Invoice & Packing Slip support.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'pdf_invoice_fields' => array(
				'name'=>'pdf_invoice_fields', 'label'=>'Invoice Fields', 'type'=>'multiselect_grouped', 'options'=>$custom_fields
			),
			'pdf_packing_slip_fields' => array(
				'name'=>'pdf_packing_slip_fields', 'label'=>'Packing Slip Fields', 'type'=>'multiselect_grouped', 'options'=>$custom_fields
			),

			'section_address_autofill' => array('title'=>'Address Autofill', 'type'=>'separator', 'colspan'=>'3'),
			'enable_autofill' => array('name'=>'enable_autofill', 'label' => 'Enable Address Autofill', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0),
			'autofill_apikey' => array('type'=>'text', 'name'=>'autofill_apikey', 'label'=>'Google Maps API Key','value'=> ''),


			'locale_override_settings' => array('title'=>'Locale override settings', 'type'=>'separator', 'colspan'=>'3'),
			'enable_label_override' => array(
				'name'=>'enable_label_override', 'label'=>'Enable label override for address fields.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_placeholder_override' => array(
				'name'=>'enable_placeholder_override', 'label'=>'Enable placeholder override for address fields.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_class_override' => array(
				'name'=>'enable_class_override', 'label'=>'Enable class override for address fields.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
			'enable_priority_override' => array(
				'name'=>'enable_priority_override', 'label'=>'Enable priority override for address fields.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_required_override' => array(
				'name'=>'enable_required_override', 'label'=>'Enable required validation override for address fields.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
			'section_other_settings' => array('title'=>'Other Settings', 'type'=>'separator', 'colspan'=>'3'),
			/*'wp_memory_limit' => array('name'=>'wp_memory_limit', 'label'=>'WP Memory Limit', 'type'=>'text'),
			'lazy_load_products' => array(
				'name'=>'lazy_load_products', 'label'=>'Lazy load products used in conditional rules', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'lazy_load_categories' => array(
				'name'=>'lazy_load_categories', 'label'=>'Lazy load categories used in conditional rules', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),*/
			'enable_conditions_country' => array(
				'name'=>'enable_conditions_country', 'label'=>'Enable display of Country field based on Conditional rules.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_conditions_state' => array(
				'name'=>'enable_conditions_state', 'label'=>'Enable display of State/ Province field based on Conditional rules.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_country_based_conditions' => array(
				'name'=>'enable_country_based_conditions', 'label'=>'Enable conditional rules based on Country selected.', 
				'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_conditions_payment_shipping' => array(
				'name'=>'enable_conditions_payment_shipping', 'label'=>'Enable conditional rules based on Payment & Shipping methods.', 
				'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_conditions_review_panel' => array(
				'name'=>'enable_conditions_review_panel', 'label'=>'Enable conditional rules for review panel.', 
				'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'disable_select2_for_select_fields' => array(
				'name'=>'disable_select2_for_select_fields', 'label'=>'Disable "Enhanced Select(Select2)" for select fields.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_wc_zapier_support' => array(
				'name'=>'enable_wc_zapier_support', 'label'=>'Enable Zapier support.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'wrap_custom_sections_with_div' => array(
				'name'=>'wrap_custom_sections_with_div', 'label'=>'Wrap custom sections with div.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_html_in_emails' => array(
				'name'=>'enable_html_in_emails', 'label'=>'Enable Heading/Label field display in emails.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'enable_inline_validations' => array(
				'name'=>'enable_inline_validations', 'label'=>'Enable Inline validation in checkout page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
		);
	}
	
	public function save_advanced_settings($settings){
		$autoload = apply_filters('thwcfe_option_autoload', 'no');
		$result = update_option(self::OPTION_KEY_ADVANCED_SETTINGS, $settings, $autoload);
		return $result;
	}
	
	private function reset_settings(){
		delete_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		echo '<div class="updated"><p>'. __('Settings successfully reset', 'woocommerce-checkout-field-editor-pro') .'</p></div>';		
	}
	
	public function get_all_custom_fields(){
		$sections = $this->get_checkout_sections();	
		$field_set = array();
		
		if($sections && is_array($sections)){	
			foreach($sections as $sname => $section){	
				if($section && THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					
					if($fields && is_array($fields)){	
						$custom_fields = array();

						foreach($fields as $name => $field){
							if($field && THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field)){
								$label = $field->get_property('title');
								$label = empty($label) ? $name : $label;
								$custom_fields[$name] = $label;
							}
						}
						
						if(!empty($custom_fields)){
							$field_set[$section->get_property('title')] = $custom_fields;
						}
					}
				}
			}
		}
		
		return $field_set;
   	}
	
	private function save_settings(){
		$settings = array();
		
		foreach( $this->settings_fields as $name => $field ) {
			if($field['type'] === 'dynamic_options'){
				$prefix = isset($field['prefix']) ? 'i_'.$field['prefix'].'_' : 'i_';
				
				$vnames = !empty( $_POST[$prefix.'validator_name'] ) ? $_POST[$prefix.'validator_name'] : array();
				$vlabels = !empty( $_POST[$prefix.'validator_label'] ) ? $_POST[$prefix.'validator_label'] : array();
				$vpatterns = !empty( $_POST[$prefix.'validator_pattern'] ) ? $_POST[$prefix.'validator_pattern'] : array();
				$vmessages = !empty( $_POST[$prefix.'validator_message'] ) ? $_POST[$prefix.'validator_message'] : array();
				
				$validators = array();
				$max = max( array_map( 'absint', array_keys( $vnames ) ) );
				for($i = 0; $i <= $max; $i++) {
					$vname = isset($vnames[$i]) ? stripslashes(trim($vnames[$i])) : '';
					$vlabel = isset($vlabels[$i]) ? stripslashes(trim($vlabels[$i])) : '';
					$vpattern = isset($vpatterns[$i]) ? stripslashes(trim($vpatterns[$i])) : '';
					$vmessage = isset($vmessages[$i]) ? stripslashes(trim($vmessages[$i])) : '';
					
					if(!empty($vname) && !empty($vpattern)){
						$vlabel = empty($vlabel) ? $vname : $vlabel;
						
						$validator = array();
						$validator['name'] = $vname;
						$validator['label'] = $vlabel;
						$validator['pattern'] = $vpattern;
						$validator['message'] = $vmessage;
						
						$validators[$vname] = $validator;
					}
				}
				$settings[$name] = $validators;
			}else{
				$value = '';
				
				if($field['type'] === 'checkbox'){
					$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				}else if($field['type'] === 'multiselect_grouped'){
					$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
					$value = is_array($value) ? implode(',', $value) : $value;
				}else if($field['type'] === 'text' || $field['type'] === 'textarea'){
					$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
					$value = !empty($value) ? stripslashes(trim($value)) : '';
				}else{
					$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				}
				
				$settings[$name] = $value;
			}
		}
				
		$result = $this->save_advanced_settings($settings);
		if ($result == true) {
			echo '<div class="updated"><p>'. __('Your changes were saved.', 'woocommerce-checkout-field-editor-pro') .'</p></div>';
		} else {
			echo '<div class="error"><p>'. __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-checkout-field-editor-pro') .'</p></div>';
		}	
	}
	
	public function render_page(){
		$this->render_tabs();
		$this->output_content();
		$this->output_import_export_settings();
	}
	
	private function output_content(){
		if(isset($_POST['reset_settings']))
			echo $this->reset_settings();	
			
		if(isset($_POST['save_settings']))
			echo $this->save_settings();
			
		$settings = $this->get_advanced_settings();
		?>            
        <div style="padding-left: 30px;">               
		    <form id="advanced_settings_form" method="post" action="">
                <!--<h2>Custom Fields Display Settings</h2>
                <p>The following options affect how prices are displayed on the frontend.</p>-->
                <table class="form-table thpladmin-form-table">
                    <tbody>
                    <?php 
					foreach( $this->settings_fields as $name => $field ) { 
						if($field['type'] === 'separator'){
							$this->render_form_section_separator($field);
						}else {
					?>
                        <tr valign="top">
                            <?php 
								if($field['type'] === 'dynamic_options'){
									$this->render_validator_settings($settings, $field);
									
								}else{
									$cell_props = $this->left_cell_props;
									
									if(is_array($settings) && isset($settings[$name])){
										if($field['type'] === 'checkbox'){
											if(is_array($settings) && isset($settings[$name])){
                                    			if($field['value'] === $settings[$name]){
                                    				$field['checked'] = 1;
                                    			}else{
                                    				$field['checked'] = 0;
                                    			}
                                    		}
										}else{
											$field['value'] = $settings[$name];
										}
									}
									
									if($field['type'] === 'checkbox'){
										$cell_props = $this->checkbox_cell_props;
									}
									
									if($field['type'] === 'multiselect' || $field['type'] === 'textarea'){
										$this->render_form_field_element_advanced($field, $cell_props);
									}else{
										$this->render_form_field_element($field, $cell_props);
										if($field['name'] === 'enable_autofill'){
                            				$this->render_google_api_link();
                  						}
									} 
								}
							?>
                        </tr>
                    <?php 
						}
					} 
					?>
                    </tbody>
                </table> 
                <p class="submit">
					<input type="submit" name="save_settings" class="button-primary" value="Save changes">
                    <input type="submit" name="reset_settings" class="button" value="Reset to default" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
            	</p>
            </form>
    	</div>       
    	<?php
	}

	public function render_google_api_link(){
		?>
		<tr valign="top">
			<td>
				<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php echo esc_html__('Click here to get your API Key', 'woocommerce-checkout-field-editor-pro');?></a>
			</td>
		</tr>

		<?php
	}
			
	public function render_validator_settings($settings, $field){
		$name = is_array($field) && isset($field['name']) ? $field['name'] : false;
		if($name){
			$custom_validators = is_array($settings) && isset($settings[$name]) ? $settings[$name] : array();
		
			?>
			<td><?php echo $field['label']; ?></td>
			<?php $this->render_form_element_tooltip(''); ?>
			<td>
				<table border="0" cellpadding="0" cellspacing="0" class="thwcfe-validations-list thpladmin-dynamic-row-table"><tbody>
					<?php
					if(is_array($custom_validators) && !empty($custom_validators)){
						foreach( $custom_validators as $vname => $validator ) {
							$this->render_validator_row($settings, $field, $validator);
						}
					}else{
						$this->render_validator_row($settings, $field, false);
					}
					?>
				</tbody></table>            	
			</td>
			<?php
		}
	}
	
	public function render_validator_row($settings, $field, $validator = false){
		$vname = ''; $vlabel = ''; $vpattern = ''; $vmessage = '';
		$prefix = isset($field['prefix']) ? 'i_'.$field['prefix'].'_' : 'i_';
		$prefix_index = 0;
		
		$pattern_ph = 'Validator Pattern';
		
		if(isset($field['prefix']) && $field['prefix'] === 'cnf'){
			$prefix_index = 1;
			$pattern_ph = 'Field Name';
		}
		
		if($validator && is_array($validator)){
			$vname = isset($validator['name']) ? $validator['name'] : '';
			$vlabel = isset($validator['label']) ? $validator['label'] : '';
			$vpattern = isset($validator['pattern']) ? $validator['pattern'] : '';
			$vmessage = isset($validator['message']) ? $validator['message'] : '';
		}
		
		?>
		<tr>
			<td style="width:190px;">
				<input type="text" name="<?php echo $prefix ?>validator_name[]" value="<?php echo $vname; ?>" placeholder="Validator Name" style="width:180px;"/>
			</td>
			<td style="width:190px;">
				<input type="text" name="<?php echo $prefix ?>validator_label[]" value="<?php echo $vlabel; ?>" placeholder="Validator Label" style="width:180px;"/>
			</td>
			<td style="width:190px;">
				<input type="text" name="<?php echo $prefix ?>validator_pattern[]" value="<?php echo $vpattern; ?>" placeholder="<?php echo $pattern_ph; ?>" style="width:180px;"/>
			</td>
			<td style="width:190px;">
				<input type="text" name="<?php echo $prefix ?>validator_message[]" value="<?php echo $vmessage; ?>" placeholder="Validator Message" style="width:180px;"/>
			</td>
			<td class="action-cell">
				<a href="javascript:void(0)" onclick="thwcfeAddNewValidatorRow(this, <?php echo $prefix_index; ?>)" class="dashicons dashicons-plus" title="Add new validator"></a>
			</td>
			<td class="action-cell">
				<a href="javascript:void(0)" onclick="thwcfeRemoveValidatorRow(this, <?php echo $prefix_index; ?>)" class="dashicons dashicons-no-alt" title="Remove validator"></a>
			</td>
		</tr>
		<?php
	}
	
   /************************************************
	*-------- IMPORT & EXPORT SETTINGS - START -----
	************************************************/
	public function output_import_export_settings(){
		$settings = $this->get_plugin_settings_data();
		
		if(isset($_POST['save_plugin_settings'])){ 
			$result = $this->save_plugin_settings(); 
		}	
		
		if(isset($_POST['import_settings'])){
			$result = $this->import_plugin_settings();
			//echo $this->imp_exp_settings->import_settings();
		} 
		
		if(isset($_POST['export_settings'])){
			$result = $this->export_plugin_settings($settings);  
		}

		echo '<div style="padding-left: 30px;">';
		$this->imp_exp_settings->render_settings($settings);
		echo '</div>';
	}

	private function save_plugin_settings(){
		$settings = $this->imp_exp_settings->get_posted_settings_data();
		$this->save_plugin_settings_data($settings);
	}

	private function import_plugin_settings(){
		$settings = $this->imp_exp_settings->get_imported_settings_data();
		$this->save_plugin_settings_data($settings);
	}

	private function export_plugin_settings($settings){
		$this->imp_exp_settings->export_settings_data($settings); 
	}

	private function save_plugin_settings_data($settings){
		$result1 = $result2 = $result3 = false;

		if(is_array($settings)){
			foreach($settings as $key => $value){	
				if($key === 'OPTION_KEY_CUSTOM_SECTIONS'){
					$autoload = apply_filters('thwcfe_option_autoload', 'no');
					$result1 = update_option(self::OPTION_KEY_CUSTOM_SECTIONS, $value, $autoload);

				}else if($key === 'OPTION_KEY_ADVANCED_SETTINGS'){ 
					$result2 = $this->save_advanced_settings($value);

				}else if($key === 'OPTION_KEY_SECTION_HOOK_MAP'){
					$autoload = apply_filters('thwcfe_option_autoload', 'no');
					$result3 = update_option(self::OPTION_KEY_SECTION_HOOK_MAP, $value, $autoload);  
				}						  
			}					
		}

		if($result1 || $result2 || $result3){
			$msg = 'Your Settings Updated.';
			echo '<div class="updated"><p>'.__($msg, 'woocommerce-checkout-field-editor-pro').'</p></div>';
			return true; 
		}else{
			$msg = 'Your changes were not saved due to an error (or you made none!).';
			echo '<div class="error"><p>'. __($msg, 'woocommerce-checkout-field-editor-pro') .'</p></div>';
			return false;
		}
	}

	private function get_plugin_settings_data(){
		$settings_sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		$settings_hook_map = get_option(self::OPTION_KEY_SECTION_HOOK_MAP);
		$settings_advanced = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);

		$settings = array(
			'OPTION_KEY_CUSTOM_SECTIONS' => $settings_sections,
			'OPTION_KEY_SECTION_HOOK_MAP' => $settings_hook_map,
			'OPTION_KEY_ADVANCED_SETTINGS' => $settings_advanced,
		);

		$settings = $this->imp_exp_settings->prepare_settings_data($settings);
		return $settings;
	}

	
	/*
	public function output_import_export_settings(){
		$plugin_settings = $this->get_plugin_settings();
		
		if(isset($_POST['save_plugin_settings'])){ 
			$result = $this->save_plugin_settings(); 
		}	
		
		if(isset($_POST['import_settings'])){
			   
		} 
		
		if(isset($_POST['export_settings'])){
			echo $this->export_settings($plugin_settings);   
		}  
		
		$imp_exp_fields = array(
			'section_import_export' => array('title'=>'Backup and Import Settings', 'type'=>'separator', 'colspan'=>'3'),
			'settings_data' => array(
				'name'=>'settings_data', 'label'=>'Plugin Settings Data', 'type'=>'textarea',
				'sub_label'=>'You can tranfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Save Settings".',
				//'sub_label'=>'You can insert the settings data to the textarea field to import the settings from one site to another website.'
			),
		);
		
		$cell_props_textarea = $this->left_cell_props;
		$cell_props_textarea['label_cell_props'] = 'class="titledesc" scope="row" style="width: 20%; vertical-align:top"';
		$cell_props_textarea['rows'] = 10;
		
		?>
		<div style="padding-left: 30px;">               
		    <form id="import_export_settings_form" method="post" action="" class="clear">
                <table class="form-table thpladmin-form-table">
                    <tbody>
                    <?php 
					foreach( $imp_exp_fields as $name => $field ) { 
						if($field['type'] === 'separator'){
							$this->render_form_section_separator($field);
						}else {
					?>
                        <tr valign="top">
                            <?php 
								$field['value'] = $plugin_settings;
								$this->render_form_field_element_advanced($field, $cell_props_textarea);
							?>
                        </tr>
                    <?php 
						}
					} 
					?>
                    </tbody>
                </table> 
                <p class="submit">
					<input type="submit" name="save_plugin_settings" class="button-primary" value="Save Settings">
					<!--<input type="submit" name="import_settings" class="button" value="Import Settings(CSV)">-->
					<!--<input type="submit" name="export_settings" class="button" value="Export Settings(CSV)">-->
            	</p>
            </form>
    	</div> 
		<?php 
	}

	public function get_plugin_settings(){
		$settings_custom_section = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		$settings_hook_map = get_option(self::OPTION_KEY_SECTION_HOOK_MAP);
		$settings_advaced = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);

		$plugin_settings = array(
				'OPTION_KEY_CUSTOM_SECTIONS' => $settings_custom_section,
				'OPTION_KEY_ADVANCED_SETTINGS' => $settings_advaced,						
				'OPTION_KEY_SECTION_HOOK_MAP' => $settings_hook_map, 
			);

		$plugin_settings_encoded = base64_encode(serialize($plugin_settings));
		return $plugin_settings_encoded;
	}
	
	public function save_plugin_settings(){		
		if(isset($_POST['i_settings_data']) && !empty($_POST['i_settings_data'])) {
			$settings_data_encoded = $_POST['i_settings_data'];   
			$settings = unserialize(base64_decode($settings_data_encoded)); 
			$result = $result1 = $result2 = false;

			if($settings){	
				foreach($settings as $key => $value){	
					if($key === 'OPTION_KEY_CUSTOM_SECTIONS'){
						$result = update_option(self::OPTION_KEY_CUSTOM_SECTIONS, $value);	
					}
					
					if($key === 'OPTION_KEY_ADVANCED_SETTINGS'){ 
						$result1 = $this->save_advanced_settings($value);  
					}
					
					if($key === 'OPTION_KEY_SECTION_HOOK_MAP'){ 
						$result2 = update_option(self::OPTION_KEY_SECTION_HOOK_MAP, $value);  
					}						  
				}					
			}		
									
			if($result || $result1 || $result2){
				echo '<div class="updated"><p>'. __('Your Settings Updated.', 'woocommerce-checkout-field-editor-pro') .'</p></div>';
				return true; 
			}else{
				echo '<div class="error"><p>'. __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-checkout-field-editor-pro') .'</p></div>';
				return false;
			}	 			
		}
	}

	public function export_settings($settings){
		ob_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"wcfe-checkout-field-editor-settings.csv\";" );
		echo $settings;	
        ob_flush();     
     	exit; 		
	}
	
	public function import_settings(){
	
	}
	*/
   /**********************************************
	*-------- IMPORT & EXPORT SETTINGS - END -----
	**********************************************/
	
}

endif;