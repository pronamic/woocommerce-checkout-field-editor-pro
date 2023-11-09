<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Settings_General')):

class THWCFE_Admin_Settings_General extends THWCFE_Admin_Settings {
	protected static $_instance = null;

	private $section_form = null;
	private $field_form = null;
	private $field_form_props = array();
	
	public function __construct() {
		parent::__construct();
		$this->page_id    = 'fields';
		$this->section_id = 'billing';
		//$this->move_fields_from_one_to_another();
	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function define_admin_hooks(){
		add_filter('postmeta_form_keys', array($this, 'postmeta_form_keys'), 10, 2);
		
		add_filter('woocommerce_customer_meta_fields', array($this, 'woo_customer_meta_fields'), 11, 1);
		add_action('edit_user_profile', array($this, 'add_customer_meta_fields'), 11, 1);
		add_action('edit_user_profile_update', array( $this, 'save_customer_meta_fields'));
		
		// Shop order columns
		add_action('manage_edit-shop_order_columns', array($this, 'manage_edit_shop_order_columns'), 11, 1);
		add_action('manage_shop_order_posts_custom_column', array($this, 'manage_shop_order_posts_custom_column'), 11, 2 );
		add_filter("manage_edit-shop_order_sortable_columns", array($this, 'manage_edit_shop_order_sortable_columns'), 11, 1 );
		add_filter('posts_clauses', array($this, 'posts_clauses_sort_shop_orders'), 10, 2);
		//add_action('pre_get_posts', array($this, 'pre_get_posts'));
		//add_filter('posts_orderby', array($this, 'posts_orderby'), 10, 2);
		
		// Formatted addresses
		add_filter('woocommerce_localisation_address_formats', array($this, 'woo_localisation_address_formats'), 20, 2); 
		add_filter('woocommerce_formatted_address_replacements', array($this, 'woo_formatted_address_replacements'), 20, 2); 
		add_filter('woocommerce_order_formatted_billing_address', array($this, 'woo_order_formatted_billing_address'), 20, 2);
		add_filter('woocommerce_order_formatted_shipping_address', array($this, 'woo_order_formatted_shipping_address'), 20, 2);
		add_action('init', array($this, 'bulk_action_listner'), 999);
		add_action('init', array($this, 'handle_form_submissions'), 999);
		add_action('wp_ajax_thwcfe_reset_to_default', array($this, 'ajax_reset_to_default'));
	}

	public function init(){
		$this->section_form = new THWCFE_Admin_Form_Section();
		$this->field_form   = new THWCFE_Admin_Form_Field();
		$this->import_export = new THWCFE_Admin_Settings_Import_Export();
		$this->field_form_props = $this->field_form->get_field_form_props();

		/*$this->locale_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
			'order_comments'
		);*/

		$this->wpml_register_address_strings();
		$this->render_page();
	}

	private function wpml_register_address_strings(){
		THWCFE_i18n::wpml_register_string('Field Title - '.'Canton', 'Canton' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'County', 'County' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'District', 'District' );		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Municipality', 'Municipality' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Prefecture', 'Prefecture' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Province', 'Province' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Region', 'Region' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'State', 'State' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'State / Zone', 'State / Zone' );
		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Suburb', 'Suburb' );	
		THWCFE_i18n::wpml_register_string('Field Title - '.'Town / District', 'Town / District' );
		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Postcode', 'Postcode' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'ZIP', 'ZIP' );
	}
	
	public function render_page(){
		$memory_limit_current = ini_get('memory_limit');	
		$memory_limit = THWCFE_Utils::get_settings('wp_memory_limit');
		
		if(!empty($memory_limit)){
			ini_set('memory_limit', $memory_limit);
		}

		$this->render_tabs();
		$this->render_sections();
		$this->render_content();

		if(!empty($memory_limit)){
			ini_set('memory_limit', $memory_limit_current);
		}
	}
	
	private function reset_to_default() {
		delete_option(THWCFE_Utils::OPTION_KEY_CUSTOM_SECTIONS);
		delete_option(THWCFE_Utils::OPTION_KEY_SECTION_HOOK_MAP);
		//delete_option(THWCFE_Utils::OPTION_KEY_NAME_TITLE_MAP);'thwcfe_options_name_title_map'

		$this->prepare_sections_and_fields();

		$notice = __('All sections are successfully reset.', 'woocommerce-checkout-field-editor-pro');
		add_action('admin_notices', function() use ($notice) {
	        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
	    });
	}

	private function reset_section($section_name){
        $sections = $this->get_checkout_sections();

		$currentSection = isset($sections[$section_name]) ? $sections[$section_name] : false;

		if($currentSection && $section_name){
			$this->delete_section($section_name);
            $this->reset_section_fields($sections, $section_name, $currentSection);
		}

		$notice = __('This section is successfully reset.', 'woocommerce-checkout-field-editor-pro');
		add_action('admin_notices', function() use ($notice) {
	        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
	    });		
	}

	public function ajax_reset_to_default(){
		if (!check_ajax_referer( 'reset-to-default-nonce')){
		   wp_die("Error happens");
		}

		$capability = THWCFE_Utils::wcfe_capability();
		if(!current_user_can($capability)){
			return;
		}

		$this->reset_to_default();

		

		wp_send_json(True);
	}

	/*------------------------------------*
	 *----- SECTION FUNCTIONS - START ----*
	 *------------------------------------*/
	/* Override */
	public function render_sections() {
		$result = false;

		// if(isset($_POST['reset_fields']))
		// 	$result = $this->reset_to_default();

		$s_action = isset($_POST['s_action']) ? $_POST['s_action'] : false;

		if($s_action == 'new' || $s_action == 'copy'){
			$result = $this->create_section();
		}else if($s_action == 'edit'){
			$result = $this->edit_section();
		}else if($s_action == 'remove'){
			$result = $this->remove_section();
		}
			
		$current_section = $this->get_current_section();
		$sections = THWCFE_Utils::get_custom_sections();
					
		if(empty($sections)){
			return;
		}
		
		$this->sort_sections($sections);
		$array_keys = array_keys( $sections );
				
		echo '<ul class="thpladmin-sections">';
		$i=0;
		foreach( $sections as $name => $section ){
			if(!THWCFE_Utils_Section::is_valid_section($section)){
				continue;
			}
			$url = $this->get_admin_url($this->page_id, sanitize_title($name));
			$props_json = htmlspecialchars(THWCFE_Utils_Section::get_property_json($section));
			$rules_json = htmlspecialchars($section->get_property('conditional_rules_json'));
			$rules_json_ajax = htmlspecialchars($section->get_property('conditional_rules_ajax_json'));
			$title = wp_strip_all_tags(THWCFE_i18n::t($section->get_property('title')));
			
			echo '<li><a href="'. $url .'" class="'. ($current_section == $name ? 'current' : '') .'">'. $title .'</a></li>';
			if(THWCFE_Utils_Section::is_custom_section($section)){
				?>
                <li>
                	<form id="section_prop_form_<?php echo $name; ?>" method="post" action="">
                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $rules_json; ?>" />
                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $rules_json_ajax; ?>" />
                    </form>
                    <span class='s_edit_btn dashicons dashicons-edit tips' data-tip='<?php _e('Edit Section', 'woocommerce-checkout-field-editor-pro'); ?>'  
					onclick='thwcfeOpenEditSectionForm(<?php echo $props_json; ?>)'></span>
                </li>
                <li>
					<span class="s_copy_btn dashicons dashicons-admin-page tips" data-tip="<?php _e('Duplicate Section', 'woocommerce-checkout-field-editor-pro'); ?>" onclick='thwcfeOpenCopySectionForm(<?php echo $props_json; ?>)'></span>
				</li>
				<li>
                    <form method="post" action="">
                        <input type="hidden" name="s_action" value="remove" />
                        <input type="hidden" name="i_name" value="<?php echo $name; ?>" />
                        <span class='s_delete_btn dashicons dashicons-no tips color-red' data-tip='<?php _e('Delete Section', 'woocommerce-checkout-field-editor-pro'); ?>' onclick='thwcfeRemoveSection(this)'></span>
					</form>
                </li>
                <?php
			}
			echo '<li>';
			echo(end( $array_keys ) == $name ? '' : '<li style="margin-right: 5px;">|</li>');
			echo '</li>';
		}
		echo '<li><a href="javascript:thwcfeOpenNewSectionForm()" class="btn btn-tiny btn-primary ml-30">+ '. __('Add new section', 'woocommerce-checkout-field-editor-pro') .'</a></li>';
		echo '</ul>';		
		
		if($result){
			echo $result;
		}
	}

	private function prepare_copy_section($section, $posted){
		$s_name_copy = isset($posted['s_name_copy']) ? $posted['s_name_copy'] : '';
		if($s_name_copy){
			$section_copy = WCFE_Checkout_Fields_Utils::get_checkout_section($s_name_copy);
			if(THWCFE_Utils_Section::is_valid_section($section_copy)){
				$field_set = $section_copy->get_property('fields');
				if(is_array($field_set) && !empty($field_set)){
					$section->set_property('fields', $field_set);
				}
			}
		}
		return $section;
	}
					
	private function create_section(){
		$section = THWCFE_Utils_Section::prepare_section_from_posted_data($_POST);
		$section = $this->prepare_copy_section($section, $_POST);
		$result = $this->update_section($section);

		if($result == true){
			return $this->print_notices('New section added successfully.', 'updated', true);
		}else{
			return $this->print_notices('New section not added due to an error.', 'error', true);
		}
	}
	
	private function edit_section(){
		$result = false;
		$section  = THWCFE_Utils_Section::prepare_section_from_posted_data($_POST, 'edit');
		if($section){
			$name 	  = $section->get_property('name');
			$position = $section->get_property('position');
			$old_position = !empty($_POST['i_position_old']) ? $_POST['i_position_old'] : '';
			
			if($old_position && $position && ($old_position != $position)){			
				$this->remove_section_from_hook($position_old, $name);
			}
			
			$result = $this->update_section($section);
		}

		if($result == true){
			return $this->print_notices('Section details updated successfully.', 'updated', true);
		}else{
			return $this->print_notices('Section details not updated due to an error.', 'error', true);
		}	
	}
	
	private function remove_section(){
		$section_name = !empty($_POST['i_name']) ? $_POST['i_name'] : false;		
		if($section_name){	
			$result = $this->delete_section($section_name);

			if ($result == true) {
				return $this->print_notices('Section removed successfully.', 'updated', true);
			} else {
				return $this->print_notices('Section not removed due to an error.', 'error', true);
			}
		}
	}
	 
	private function delete_section($section_name){
		if(isset($section_name) && !empty($section_name)){	
			$sections = $this->get_checkout_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section   = $sections[$section_name];
				$hook_name = $section->get_property('position');
				
				$this->remove_section_from_hook($hook_name, $section_name);
				unset($sections[$section_name]);
							
				$result = $this->save_sections($sections);		
				return $result;
			}
		}
		return false;
	}

	//handle bulk actions dropdown
	function bulk_action_listner(){
		$capability = THWCFE_Utils::wcfe_capability();
		if(!current_user_can($capability)){
			return;
		}

		$action = isset($_POST['action_type']) ? $_POST['action_type'] : false;
		if(!isset($_POST['bulk_action']) || !$action){
			return;
		}

		$current_section = $this->get_current_section();

		if($action == 'reset_all_sections'){
			$this->reset_to_default();		
		}elseif($action == 'reset_section'){
			$this->reset_section($current_section);
		}elseif($action == 'export_section'){
			$import_export = new THWCFE_Admin_Settings_Import_Export();
			$import_export->export_section($current_section);
		}elseif($action == 'export_fields'){
			$import_export = new THWCFE_Admin_Settings_Import_Export();
			$import_export->export_fields($current_section);
		}

	}

	// New form submission handler
	function handle_form_submissions(){
		$capability = THWCFE_Utils::wcfe_capability();
		if(!current_user_can($capability)){
			return;
		}

		$action = isset($_POST['thwcfe_form_action']) ? $_POST['thwcfe_form_action'] : false;
		if(!isset($_POST['submit']) || !$action){
			return;
		}

		if($action == 'import_section_fields'){
			if(!isset( $_POST['thwcfe_form_security'] ) || !wp_verify_nonce( $_POST['thwcfe_form_security'], 'import_section_fields' )){
			   die('Thank You');
			}

			$current_section = $this->get_current_section();

			$import_export = new THWCFE_Admin_Settings_Import_Export();
			$import_export->import_section_fields($current_section);

		}
	}

	/*-----------------------------------*
	 *------ SECTION FORMS - END --------*
	 *-----------------------------------*/

	/*-----------------------------------*
	 *------ FIELD FORMS - END ----------*
	 *-----------------------------------*/	
	private function truncate_str($string, $offset){
		if($string && strlen($string) > $offset){
			$string = trim(substr($string, 0, $offset)).'...';
		}
		
		return $string;
	}

	private function render_fields_table_heading(){
		?>
		<th class="sort"></th>
		<th class="check-column"><input type="checkbox" style="margin:0px 4px -1px -1px;" onclick="thwcfeSelectAllCheckoutFields(this)"/></th>
		<th class="name"><?php THWCFE_i18n::et('Name'); ?></th>
		<th class="type"><?php THWCFE_i18n::et('Type'); ?></th>
		<th class="label"><?php THWCFE_i18n::et('Label'); ?></th>
		<th class="placeholder"><?php THWCFE_i18n::et('Placeholder'); ?></th>
		<th class="validate"><?php THWCFE_i18n::et('Validations'); ?></th>
        <th class="status"><?php THWCFE_i18n::et('Required'); ?></th>
		<th class="status"><?php THWCFE_i18n::et('Enabled'); ?></th>	
        <th class="actions align-center"><?php THWCFE_i18n::et('Actions'); ?></th>	
        <?php
	}
	
	private function render_actions_row($section){
		if(THWCFE_Utils_Section::is_valid_section($section)){
		?>
            <th colspan="5">
                <button type="button" class="btn btn-small btn-primary" onclick="thwcfeOpenNewFieldForm('<?php echo $section->get_property('name'); ?>')">
                    <?php _e('+ Add field', 'woocommerce-checkout-field-editor-pro'); ?>
                </button>
                <button type="button" class="btn btn-small" onclick="thwcfeRemoveSelectedFields()"><?php  _e('Remove', 'woocommerce'); ?></button>
                <button type="button" class="btn btn-small" onclick="thwcfeEnableSelectedFields()"><?php  _e('Enable', 'woocommerce'); ?></button>
                <button type="button" class="btn btn-small" onclick="thwcfeDisableSelectedFields()"><?php _e('Disable', 'woocommerce'); ?></button>
            </th>
            <th colspan="5">
                <input type="submit" name="save_fields" class="btn btn-small btn-primary" value="<?php _e('Save changes', 'woocommerce') ?>" style="float:right" />
<!--                 <input type="submit" name="reset_fields" class="btn btn-small" value="<?php THWCFE_i18n::et('Reset to default fields') ?>" style="float:right; margin-right: 5px;" 
                onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');"/> -->
				<input type="submit" name="bulk_action" class="btn btn-small" value="<?php _e('Apply', 'woocommerce') ?>" style="float:right; margin-right: 5px;" 
				                onclick="thwcfeApplyBulkAction(this, event)"/>                
				<select name="bulk_action_options" style="float:right; margin-right: 5px; height:32px" onchange="thwcfeBulkActionListner(this)">
					<option value="">Actions</option>
					<option value="reset_section">Reset this section</option>
					<option value="reset_all_sections">Reset all sections</option>
					<option value="export_section">Export section</option>
					<option value="export_fields">Export fields</option>
					<option value="import_fields_section">Import fields / section</option>
				</select>
				<input type="hidden" name="action_type" value="">
            </th>  
    	<?php 
		}else{
		?>
			<th colspan="5">
                <button type="button" class="btn btn-small" disabled ><?php _e('+ Add field', 'woocommerce-checkout-field-editor-pro'); ?></button>
                <button type="button" class="btn btn-small" disabled ><?php _e('Remove', 'woocommerce-checkout-field-editor-pro'); ?></button>
                <button type="button" class="btn btn-small" disabled ><?php _e('Enable', 'woocommerce-checkout-field-editor-pro'); ?></button>
                <button type="button" class="btn btn-small" disabled ><?php _e('Disable', 'woocommerce-checkout-field-editor-pro'); ?></button>
            </th>
            <th colspan="5">
                <input type="submit" name="save_fields" class="btn btn-small" disabled value="<?php _e('Save changes', 'woocommerce') ?>" style="float:right" />
<!--                 <input type="submit" name="reset_fields" class="btn btn-small" value="<?php THWCFE_i18n::et('Reset to default fields') ?>" style="float:right; margin-right: 5px;" 
                onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');"/> -->
				<input type="submit" name="bulk_action" class="btn btn-small" value="<?php _e('Apply', 'woocommerce') ?>" style="float:right; margin-right: 5px;" 
				                onclick="thwcfeApplyBulkAction(this, event)"/>                
				<select name="bulk_action_options" style="float:right; margin-right: 5px; height:32px" onchange="thwcfeBulkActionListner(this)">
					<option value="">Actions</option>
					<option value="reset_section">Reset this section</option>
					<option value="reset_all_sections">Reset all sections</option>
					<option value="export_section">Export section</option>
					<option value="export_fields">Export fields</option>
					<option value="import_fields_section">Import fields / section</option>
				</select>
				<input type="hidden" name="action_type" value="">

            </th> 
		<?php
		}
	}

	private function render_content(){
		$thwcfe_all_section_data = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		$reset_to_default = '';
		if(empty($thwcfe_all_section_data)){
			$reset_to_default = '<a href="#" data-nonce="'.wp_create_nonce('reset-to-default-nonce').'" onclick="thwcfeRestoreDefaultFields(event, this);"> ' . __('Else click here to get the default fields.', 'woocommerce-checkout-field-editor-pro') . '</a>';
		}

		$section_name = $this->get_current_section();
		$section = $this->get_checkout_section($section_name);
		$action = isset($_POST['f_action']) ? $_POST['f_action'] : false;
		
		if($action === 'new' || $action === 'copy')
			echo $this->save_or_update_field($section, $action);	
			
		if($action === 'edit')
			echo $this->save_or_update_field($section, $action);
		
		if(isset($_POST['save_fields']))
			echo $this->save_fields($section);
			
		$section = $this->get_checkout_section($section_name);
		$ignore_fields = apply_filters('thwcfe_ignore_fields', array());
		
		?>            
        <div class="wrap woocommerce"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>                
		    <form method="post" id="thwcfe_checkout_fields_form" action="">
            <table id="thwcfe_checkout_fields" class="wc_gateways widefat thpladmin_fields_table" cellspacing="0">
            	<thead>
                    <tr><?php $this->render_actions_row($section); ?></tr>
                    <tr><?php $this->render_fields_table_heading(); ?></tr>						
                </thead>
                <tfoot>
                    <tr><?php $this->render_fields_table_heading(); ?></tr>
                    <tr><?php $this->render_actions_row($section); ?></tr>
                </tfoot>
                <tbody class="ui-sortable">
                <?php 
				if(THWCFE_Utils_Section::is_valid_section($section) && THWCFE_Utils_Section::has_fields($section)){
					$i=0;										
					foreach( $section->fields as $field ) :	
						$name = $field->get_property('name');
						$type = $field->get_property('type');
						$is_enabled = $field->get_property('enabled') ? 1 : 0;
						$props_json = htmlspecialchars($this->get_property_set_json($field));
						
						$options_json = htmlspecialchars($field->get_property('options_json'));
						$rules_json = htmlspecialchars($field->get_property('conditional_rules_json'));
						$rules_json_ajax = htmlspecialchars($field->get_property('conditional_rules_ajax_json'));
						$repeat_rule_json = htmlspecialchars($field->get_property('repeat_rules'));
						
						//$disabled_actions = $is_enabled ? in_array($type, THWCFE_Utils_Field::$SPECIAL_FIELD_TYPES) : 1;
						$disable_actions = in_array($name, $ignore_fields) ? true : false;
						$disable_edit = $disable_actions || !$is_enabled ? true : false;
						$disable_copy = $disable_actions || in_array($type, THWCFE_Utils_Field::$SPECIAL_FIELD_TYPES) ? true : false;
						$disabled_cb = $disable_actions ? 'disabled' : '';
					?>
						<tr class="row_<?php echo $i; echo($is_enabled === 1 ? '' : ' thpladmin-disabled') ?>">
							<td width="1%" class="sort ui-sortable-handle">
								<input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo $name; ?>" />
								<input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
								<input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
								<input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo $is_enabled; ?>" />
								<input type="hidden" name="f_selected[<?php echo $i; ?>]" class="f_selected" value="0" />
								
								<input type="hidden" name="f_props[<?php echo $i; ?>]" class="f_props" value='<?php echo $props_json; ?>' />
								<input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo $options_json; ?>" />
								<input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $rules_json; ?>" />
								<input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $rules_json_ajax; ?>" />

								<input type="hidden" name="f_repeat_rules[<?php echo $i; ?>]" class="f_repeat_rules" value="<?php echo $repeat_rule_json; ?>" />
							</td>
							<td class="td_select"><input type="checkbox" name="select_field" <?php echo $disabled_cb; ?> onchange="thwcfeCheckoutFieldSelected(this)"/></td>
							
							<?php
							$field_props_display = $this->field_form->get_field_form_props_display();

							foreach( $field_props_display as $pname => $property ){							
								$pvalue = $field->get_property($pname);
								$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
								
								if($property['type'] == 'checkbox'){
									$pvalue = $pvalue ? 1 : 0;
								}
								
								if(isset($property['status']) && $property['status'] == 1){
									$statusHtml = $pvalue == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="'.__('Yes', 'woocommerce').'"></span>' : '-';
									?>
									<td class="td_<?php echo $pname; ?> status"><?php echo $statusHtml; ?></td>
									<?php
								}else{
									$pvalue = esc_attr($pvalue);
									$pvalue = stripslashes($pvalue);
									$tooltip = '';

									$len = isset($property['len']) ? $property['len'] : false;

									if(is_numeric($len) && $len > 0){
										$tooltip = $pvalue;
										$pvalue = $this->truncate_str($pvalue, $len);
									}

									?>
									<td class="td_<?php echo $pname; ?>">
										<label title="<?php echo $tooltip; ?>"><?php echo $pvalue; ?></label>
									</td>
									<?php
								}
							}
							?>
							
							<td class="td_actions" align="center">
								<?php if($disable_edit){ ?>
									<span class="f_edit_btn dashicons dashicons-edit disabled"></span>
								<?php }else{ ?>
									<span class="f_edit_btn dashicons dashicons-edit tips" data-tip="<?php _e('Edit Field', 'woocommerce-checkout-field-editor-pro'); ?>"  
									onclick="thwcfeOpenEditFieldForm(this, <?php echo $i; ?>)"></span>
								<?php } ?>
								
								<?php if($disable_copy){ ?>
									<span class="f_copy_btn dashicons dashicons-admin-page disabled"></span>
								<?php }else{ ?>
									<span class="f_copy_btn dashicons dashicons-admin-page tips" data-tip="<?php _e('Duplicate Field', 'woocommerce-checkout-field-editor-pro'); ?>"  
									onclick="thwcfeOpenCopyFieldForm(this, <?php echo $i; ?>)"></span>
								<?php } ?>
							</td>
						</tr>						
	                <?php $i++; endforeach;
                }else{
                	?>
					<tr>
						<td colspan="10" class="empty-msg-row">
							<?php _e('No checkout fields found. Click on Add Field button to create new fields.', 'woocommerce-checkout-field-editor-pro'); ?>
							<?php echo $reset_to_default; ?>
						</td>
					</tr>
					<?php
				}
                ?>
                </tbody>
            </table> 
            </form>
            <?php
            $this->section_form->output_section_forms();
            $this->field_form->output_field_forms();
            $this->import_export->output_export_form();
			?>
    	</div>
    <?php
    }

    private function get_property_set_json($field){
		if(THWCFE_Utils_Field::is_valid_field($field)){
			$props_set = array();
			
			foreach( $this->field_form_props as $pname => $property ){
				$pvalue = $field->get_property($pname);
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				$pvalue = esc_attr($pvalue);
				
				if($property['type'] == 'checkbox'){
					$pvalue = $pvalue ? 1 : 0;
				}
				$props_set[$pname] = $pvalue;
			}
						
			$props_set['custom'] = THWCFE_Utils_Field::is_custom_field($field) ? 1 : 0;
			$props_set['order'] = $field->get_property('order');
			$props_set['priority'] = $field->get_property('priority');
			$props_set['price_field'] = $field->get_property('price_field') ? 1 : 0;
			$props_set['rules_action'] = $field->get_property('rules_action');
			$props_set['rules_action_ajax'] = $field->get_property('rules_action_ajax');
						
			return json_encode($props_set);
		}else{
			return '';
		}
	}
	
	private function save_or_update_field($section, $action) {
		try {
			$field = THWCFE_Utils_Field::prepare_field_from_posted_data($_POST, $this->field_form_props);
			if(is_object($field) && isset($field->name) && !empty($field->name) && isset($field->type) && !empty($field->type)){
				if($action === 'edit'){
					$section = THWCFE_Utils_Section::update_field($section, $field);
				}else{
					$section = THWCFE_Utils_Section::add_field($section, $field);
				}
				
				$result = $this->update_section($section);
				
				if($result == true){
					$this->print_notices('Your changes were saved.', 'updated');
					do_action('thwcfe-checkout-fields-updated');
				}else{
					$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error');
				}
			}else{
				$this->print_notices('Your changes were not saved due to an error.', 'error');
			}
		} catch (Exception $e) {
			$this->print_notices('Your changes were not saved due to an error.', 'error');
		}
	}
	
	private function save_fields($section) {
		try {
			if(THWCFE_Utils_Section::is_valid_section($section)){
				$f_names = !empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();	
				if(empty($f_names)){
					echo '<div class="error"><p> '. __('Your changes were not saved due to no fields found.', 'woocommerce-checkout-field-editor-pro') .'</p></div>';
					return;
				}
				
				$f_order   = !empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();	
				$f_deleted = !empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
				$f_enabled = !empty( $_POST['f_enabled'] ) ? $_POST['f_enabled'] : array();
							
				$sname = $section->get_property('name');
				$field_set = THWCFE_Utils_Section::get_fields($section);
				
				$max = max( array_map( 'absint', array_keys( $f_names ) ) );
				for($i = 0; $i <= $max; $i++) {
					$name = $f_names[$i];
					
					if(isset($field_set[$name])){
						if(isset($f_deleted[$i]) && $f_deleted[$i] == 1){
							unset($field_set[$name]);
							continue;
						}
						
						$field = $field_set[$name];
						$field->set_property('order', isset($f_order[$i]) ? trim(stripslashes($f_order[$i])) : 0);
						$field->set_property('enabled', isset($f_enabled[$i]) ? trim(stripslashes($f_enabled[$i])) : 0);
						
						$field_set[$name] = $field;
					}
				}
				$section->set_property('fields', $field_set);
				$section = THWCFE_Utils_Section::sort_fields($section);
				
				$result1 = $this->update_section($section);
				//$result2 = $this->update_options_name_title_map();
				
				if ($result1 == true) {
					$this->print_notices('Your changes were saved.', 'updated');
					do_action('thwcfe-checkout-fields-updated');
				} else {
					$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error');
				}
			}
		} catch (Exception $e) {
			$this->print_notices('Your changes were not saved due to an error.', 'error');
		}
	}
	/*-----------------------------------*
	 *------ FIELD FORMS - END ----------*
	 *-----------------------------------*/	

	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER DETAILS PAGE - START ***
	*******************************************************************************/	
	public function woo_customer_meta_fields($fields){
		$sections = $this->get_checkout_sections();
		if($sections && is_array($sections)){
			foreach($sections as $sname => $section) {
				$fieldset = THWCFE_Utils_Section::get_fields($section);
					
				if($fieldset && is_array($fieldset) && !empty($fieldset)){
					if($sname === 'billing' || $sname === 'shipping'){
						foreach($fieldset as $key => $field) {
							if(THWCFE_Utils_Field::is_custom_field($field) && $field->get_property('user_meta')){
								if($field->get_property('type') != 'file'){
									$fields[$sname]['fields'][$key] = array(
										'label'       => THWCFE_i18n::t($field->get_property('title')),
										'description' => THWCFE_i18n::t($field->get_property('description')),
										'type'        => $field->get_property('type'),
										'class'       => '',
										'options'     => THWCFE_Utils_Field::get_option_array($field)
									);
								}
							}
						}
					}else{
						$cfields = array();
						
						foreach($fieldset as $key => $field) {
							if(THWCFE_Utils_Field::is_custom_field($field) && $field->get_property('user_meta')){
								if($field->get_property('type') != 'file'){
									$cfields[$key] = array(
										'label'       => THWCFE_i18n::t($field->get_property('title')),
										'description' => THWCFE_i18n::t($field->get_property('description')),
										'type'        => $field->get_property('type'),
										'class'       => '',
										'options'     => THWCFE_Utils_Field::get_option_array($field)
									);
								}
							}
						}
						
						if(!empty($cfields)){
							$fields[$sname]['title'] = THWCFE_i18n::t($section->get_property('title'));
							$fields[$sname]['fields'] = $cfields;
						}
					}
				}
			}
		}
		
		return $fields;
	}

	public function add_customer_meta_fields( $user ) {
		if ( ! apply_filters( 'woocommerce_current_user_can_edit_customer_meta_fields', current_user_can( 'manage_woocommerce' ), $user->ID ) ) {
			return;
		}

		$sections = THWCFE_Utils::get_custom_sections();
		if(is_array($sections)){
			$cfields = array();

			foreach($sections as $sname => $section) {
				$fieldset = THWCFE_Utils_Section::get_fields($section);
					
				if($fieldset && is_array($fieldset) && !empty($fieldset)){
					foreach ( $fieldset as $key => $field ) {
						if(THWCFE_Utils_Field::is_custom_user_field($field)){
							if($field->get_property('type') === 'file'){
								$cfields[$key] = THWCFE_Utils_Field::get_property_set($field);
							}
						}
					}
				}
			}

			if(!empty($cfields)){
				?>
				<h2>Other custom fields</h2>
				<table class="form-table thwcfe-user-profile" id="<?php echo esc_attr( 'thwcfe_fieldset-' . $sname ); ?>">
					<?php
					foreach($cfields as $key => $field){
						$title = isset($field['label']) ? $field['label'] : $key;
						$desc  = isset($field['description']) ? $field['description'] : $key;
						$value = get_user_meta($user->ID, $key, true);
						//$class = !empty($field->get_property('class')) ? esc_attr($field->get_property('class')) : 'regular-text';
						
						//woocommerce_form_field($key, $field, $value);
						$field_html  = THWCFE_Utils::prepare_file_preview_html($value);
						$field_html .= THWCFE_Utils::form_field_file_html($key, $field, $value);
						
						?>
						<tr class="thwcfe-input-field-wrapper">
							<th>
								<label for="<?php echo esc_attr($key); ?>"><?php echo esc_html(__($title, 'woocommerce-checkout-field-editor-pro')); ?></label>
							</th>
							<td>
								<?php echo $field_html; ?>
								<p class="description"><?php echo wp_kses_post(__($desc, 'woocommerce-checkout-field-editor-pro')); ?></p>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			}
		}
	}

	public function save_customer_meta_fields( $user_id ) {
		if(!apply_filters('woocommerce_current_user_can_edit_customer_meta_fields', current_user_can('manage_woocommerce'), $user_id)) {
			return;
		}
		$sections = THWCFE_Utils::get_custom_sections();
		if(is_array($sections)){
			foreach($sections as $sname => $section) {
				$fieldset = THWCFE_Utils_Section::get_fields($section, false, true);

				if($fieldset){
					foreach($fieldset as $key => $field) {
						if(THWCFE_Utils_Field::is_custom_user_field($field)){
							$type = $field->get_property('type');

							if($type === 'file'){
								$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
								update_user_meta( $user_id, $key, wc_clean($value));
							}
						}
					}
				}
			}
		}
	}
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER DETAILS PAGE - END *****
	*******************************************************************************/
	
	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER LIST TABLE - START *****
	*******************************************************************************/
	private function get_custom_shop_order_columns(){
		$custom_columns_str = $this->get_settings('custom_shop_order_columns');
		$custom_columns = array();
		
		if(!empty($custom_columns_str)){
			$col_arr = explode(",", $custom_columns_str);
			
			if($col_arr){
				foreach($col_arr as $col_str){
					$col = explode(":", $col_str);
					
					if(is_array($col) && !empty($col)){
						$name = isset($col[0]) ? $col[0] : false;
						if($name){
							$title = isset($col[1]) ? $col[1] : $name;
							$custom_columns[$name] = $title;
						}
					}
				}
			}
		}
		
		return is_array($custom_columns) ? $custom_columns : array();
	}
	
	public function manage_edit_shop_order_columns($columns){
		$custom_columns = $this->get_custom_shop_order_columns();

		if(is_array($custom_columns) && !empty($custom_columns)){
			$new_columns = (is_array($columns)) ? $columns : array();
			if(isset($new_columns['order_actions'])){
				unset($new_columns['order_actions']);
			}
			
			foreach($custom_columns as $name => $title){
				$new_columns[$name] = $title;
			}
			
			if(isset($columns['order_actions'])){
				$new_columns['order_actions'] = $columns['order_actions'];
			}		
			return $new_columns;
		}
		return $columns;
	}
	
	public function manage_shop_order_posts_custom_column($column){
		$custom_columns = $this->get_custom_shop_order_columns();
		
		if(is_array($custom_columns) && !empty($custom_columns)){
			global $post;
			$data = get_post_meta( $post->ID );
			
			if(array_key_exists($column, $custom_columns)){
			    $column_data = isset($data[$column]) ? $data[$column][0] : '';
			    echo apply_filters('thwcfe_modify_order_posts_custom_column',$column_data,$column);
			}
		}
	}
	
	public function manage_edit_shop_order_sortable_columns( $columns ) {
		$custom_columns = $this->get_custom_shop_order_columns();
		$custom = array();
		
		if(!empty($custom_columns)){
			foreach($custom_columns as $name => $title){
				//$custom[$name] = $name.'_POST_META_ID';
				$custom[$name] = $name;
			}
		}

		return wp_parse_args( $custom, $columns );
	}

	public function posts_clauses_sort_shop_orders($pieces, $query) {
		global $wpdb;

		if(isset($query->query['post_type']) && $query->query['post_type'] == 'shop_order' && $query->is_main_query() && isset($query->query['orderby'])) {	
			$custom_columns = $this->get_custom_shop_order_columns();
			$orderby = $query->query['orderby'];
	
			if(!empty($custom_columns) && array_key_exists($orderby, $custom_columns)){
				$fieldset = self::get_all_checkout_fields();
				$cfield = is_array($fieldset) && isset($fieldset[$orderby]) ? $fieldset[$orderby] : false;

				if($cfield){
					$orderby_str = 'wp_rd.meta_value';

					if($cfield->get_property('type') === 'datepicker'){
						$date_format = $cfield->get_property('date_format');
						if($date_format){
							$date_format = str_replace("dd", "%d", $date_format);
							$date_format = str_replace("mm", "%m", $date_format);
							$date_format = str_replace("yy", "%Y", $date_format);
						}else{
							$date_format = '%d/%m/%Y';
						}

						$orderby_str = "STR_TO_DATE( wp_rd.meta_value,'".$date_format."' )";
					}

					$order = strtoupper($query->get('order'));
			    	$order = in_array($order, array('ASC', 'DESC')) ? $order : 'ASC';

					$pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = '".$orderby."'";
					
		            $pieces['orderby'] = $orderby_str." $order, ". $pieces['orderby'];
				}
			}
		}
		return $pieces;
	}

	/*
	public function pre_get_posts($query) { 
		//if ($query->is_post_type_archive('shop_order') && $query->is_main_query()) {
		if($query->query['post_type']  == 'shop_order' && $query->is_main_query() && isset($query->query['orderby'])) {	
			$custom_columns = $this->get_custom_shop_order_columns();
			$orderby = $query->query['orderby'];
	
			if(!empty($custom_columns) && array_key_exists($orderby, $custom_columns)){
				//$query->set('meta_key', $orderby);
				//$query->set('orderby', 'meta_value');
			}
		}
		//return $query;
	}
	*/
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER LIST TABLE - START *****
	*******************************************************************************/

	//Example function to move fields from one section to another.
	private function move_fields_from_one_to_another(){
		$fields = array('f1', 'f2', 'f3');
		$section_from = $this->get_checkout_section('billing');
		$section_to = $this->get_checkout_section('new_sec');

		if(is_array($fields) && THWCFE_Utils_Section::is_valid_section($section_from) && THWCFE_Utils_Section::is_valid_section($section_to)){
			$field_set = THWCFE_Utils_Section::get_fields($section_from);
			$updated = false;

			foreach($fields as $fname){
				$field = isset($field_set[$fname]) ? $field_set[$fname] : false;
				if(THWCFE_Utils_Field::is_valid_field($field)){
					$section_to = THWCFE_Utils_Section::add_field($section_to, $field);
					$updated = true;
				}
			}

			if($updated){
				$result = $this->update_section($section_to);
			}
		}
	}


}

endif;