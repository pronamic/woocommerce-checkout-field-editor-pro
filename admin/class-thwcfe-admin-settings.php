<?php
/**
 * The admin settings page specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){ die; }

if(!class_exists('THWCFE_Admin_Settings')):

abstract class THWCFE_Admin_Settings extends THWCFE_Admin_Utils{
	protected $page_id    = '';	
	protected $section_id = '';
	
	protected $tabs = '';
	protected $sections = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		//$this->tabs = array( 'fields' => 'Checkout Fields', 'advanced_settings' => 'Advanced Settings');
		$this->tabs = array( 'fields' => 'Checkout Fields', 'advanced_settings' => 'Advanced Settings', 'license_settings' => 'Plugin License');
	}
	
	public function get_tabs(){
		return $this->tabs;
	}

	public function get_current_tab(){
		return $this->page_id;
	}
	
	public function get_current_section(){
		return isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : $this->section_id;
	}
	
	public function render_tabs(){
		$current_tab = $this->get_current_tab();
		$tabs = $this->get_tabs();

		if(empty($tabs)){
			return;
		}
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $tabs as $id => $label ){
			$active = ( $current_tab == $id ) ? 'nav-tab-active' : '';
			$label  = __($label,'woocommerce-checkout-field-editor-pro');
			echo '<a class="nav-tab '.$active.'" href="'. $this->get_admin_url($id) .'">'.$label.'</a>';
		}
		echo '</h2>';		
	}
	
	public function render_sections() {
		$current_section = $this->get_current_section();
		$sections = $this->get_sections();

		if(empty($sections)){
			return;
		}
		
		$array_keys = array_keys( $sections );
		
		echo '<ul class="thpladmin-sections">';
		foreach( $sections as $id => $label ){
			$label = wp_strip_all_tags(THWCFE_i18n::t($label));
			$url = $this->get_admin_url($this->page_id, sanitize_title($id));	
			echo '<li><a href="'. $url .'" class="'. ( $current_section == $id ? 'current' : '' ) .'">'. $label .'</a> '. (end( $array_keys ) == $id ? '' : '|') .' </li>';
		}		
		echo '</ul>';
	}	
	
	public function get_admin_url($tab = false, $section = false){
		$url = 'admin.php?page=th_checkout_field_editor_pro';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		if($section && !empty($section)){
			$url .= '&section='. $section;
		}
		return admin_url($url);
	}

	public function print_notices($msg, $type='updated', $return=false){
		$notice = '<div class="thwcfe-notice '. $type .'"><p>'. __($msg, 'woocommerce-checkout-field-editor-pro') .'</p></div>';
		if(!$return){
			echo $notice;
		}
		return $notice;
	}
	
   /*******************************************
	*-------- HTML FORM FRAGMENTS - START -----
	*******************************************/
	
	public function render_form_element_tooltip($tooltip){
		$tooltip_html = '';
		
		if($tooltip){
			$icon = THWCFE_ASSETS_URL_ADMIN.'/css/help.png';
			$tooltip_html = '<a href="javascript:void(0)" title="'. $tooltip .'" class="thpladmin_tooltip"><img src="'. $icon .'" alt="" title=""/></a>';
		}
		?>
        <td style="width: 26px; padding:0px;"><?php echo $tooltip_html; ?></td>
        <?php
	}
	
	public function render_form_element_empty_cell(){
		?>
		<td width="13%">&nbsp;</td>
        <?php $this->render_form_element_tooltip(false); ?>
        <td width="34%">&nbsp;</td>
        <?php
	}
	
	public function render_form_element_h_separator($padding = 5, $colspan = 6){
		?>
        <tr><td colspan="<?php echo $colspan; ?>" style="border-bottom: 1px dashed #e6e6e6; padding-top: <?php echo $padding ?>px;"></td></tr>
        <?php
	}
	
	public function render_form_element_h_spacing($padding = 5, $colspan = 6){
		?>
        <tr><td colspan="<?php echo $colspan; ?>" style="padding-top:<?php echo $padding ?>px;"></td></tr>
        <?php
	}
	
	public function render_form_field_element($field, $atts=array(), $render_cell=true){
		if($field && is_array($field)){
			$ftype = isset($field['type']) ? $field['type'] : 'text';
			
			if($ftype == 'checkbox'){
				$this->render_form_field_element_checkbox($field, $atts, $render_cell);
				return true;
			}
		
			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_th' => false,
				'input_width' => '',
				'input_name_prefix' => 'i_',
				'input_name_suffix' => ''
			), $atts );
			
			if($ftype == 'multiselect'){
				$args['input_name_suffix'] = $args['input_name_suffix'].'[]';
			}
			
			$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
			$flabel = THWCFE_i18n::t($field['label']);
			$fvalue = isset($field['value']) ? $field['value'] : '';
			
			/*if($ftype == 'multiselect' || $ftype == 'multiselect_grouped'){
				$fvalue = !empty($fvalue) ? explode(',', $fvalue) : $fvalue;
			}*/
						
			$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
			$field_props  = 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"';
			$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
			
			$required_html = ( isset($field['required']) && $field['required'] ) ? '<abbr class="required" title="required">*</abbr>' : '';
			$field_html = '';
			
			if(isset($field['onchange']) && !empty($field['onchange'])){
				$field_props .= ' onchange="'.$field['onchange'].'"';
			}
			
			if($ftype == 'text'){
				$field_html = '<input type="text" '. $field_props .' />';
				
			}else if($ftype == 'textarea'){
				$field_html = '<textarea '. $field_props .' ></textarea>';
				
			}else if($ftype == 'select'){
				$field_html = '<select '. $field_props .' >';
				foreach($field['options'] as $value=>$label){
					$selected = $value === $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. THWCFE_i18n::t($label) .'</option>';
				}
				$field_html .= '</select>';
				
			}else if($ftype == 'multiselect'){
				$field_html = '<select multiple="multiple" '. $field_props .' class="thwcfe-enhanced-multi-select" >';
				foreach($field['options'] as $value=>$label){
					//$selected = $value === $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. trim($value) .'" >'. THWCFE_i18n::t($label) .'</option>';
				}
				$field_html .= '</select>';
				
			}else if($ftype == 'multiselect_grouped'){
				$field_props  = 'name="'. $fname .'[]" data-value="'. $fvalue .'" style="'. $input_width .'"';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
			
				$field_html = '<select multiple="multiple" '. $field_props .' class="thwcfe-enhanced-multi-select" >';
				
				foreach($field['options'] as $group_label => $fields){
					$field_html .= '<optgroup label="'. $group_label .'">';
					
					foreach($fields as $value => $label){
						$value = trim($value);
						if(isset($field['glue']) && !empty($field['glue'])){
							$value = $value.$field['glue'].trim($label);
						}
						
						$field_html .= '<option value="'. $value .'">'. THWCFE_i18n::t($label) .'</option>';
					}
					
					$field_html .= '</optgroup>';
				}
				
				$field_html .= '</select>';
				
			}else if($ftype == 'colorpicker'){
				$field_html  = '<span class="thpladmin-colorpickpreview '.$field['name'].'_preview" style=""></span>';
                $field_html .= '<input type="text" '. $field_props .' class="thpladmin-colorpick"/>';              
            
			}
			
			$label_cell_props = !empty($args['label_cell_props']) ? ' '.$args['label_cell_props'] : '';
			$input_cell_props = !empty($args['input_cell_props']) ? ' '.$args['input_cell_props'] : '';
			?>
            
			<td <?php echo $label_cell_props ?> > <?php 
				echo $flabel; echo $required_html; 
				
				if(isset($field['sub_label']) && !empty($field['sub_label'])){
					?>
                    <br /><span class="thpladmin-subtitle"><?php THWCFE_i18n::et($field['sub_label']); ?></span>
					<?php
				}
				?>
            </td>
            
            <?php 
			$tooltip = ( isset($field['hint_text']) && !empty($field['hint_text']) ) ? $field['hint_text'] : false;
			$this->render_form_element_tooltip($tooltip);
			?>
            
            <td <?php echo $input_cell_props ?> ><?php echo $field_html; ?></td>
            
            <?php
		}
	}
	
	public function render_form_field_element_advanced($field, $atts=array(), $render_cell=true){
		if($field && is_array($field)){
			$ftype = isset($field['type']) ? $field['type'] : 'text';
			
			if($ftype == 'checkbox'){
				$this->render_form_field_element_checkbox($field, $atts, $render_cell);
				return true;
			}
		
			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_th' => false,
				'input_width' => '',
				'rows' => '5',
				'cols' => '100',
				'input_name_prefix' => 'i_'
			), $atts );
			
			$fname  = $args['input_name_prefix'].$field['name'];
			$flabel = THWCFE_i18n::t($field['label']);
			$fvalue = isset($field['value']) ? $field['value'] : '';
			
			if($ftype == 'multiselect' && is_array($fvalue)){
				$fvalue = !empty($fvalue) ? implode(',', $fvalue) : $fvalue;
			}
			/*if($ftype == 'multiselect' || $ftype == 'multiselect_grouped'){
				$fvalue = !empty($fvalue) ? explode(',', $fvalue) : $fvalue;
			}*/
						
			$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
			$field_props  = 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"';
			$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
			
			$required_html = ( isset($field['required']) && $field['required'] ) ? '<abbr class="required" title="required">*</abbr>' : '';
			$field_html = '';
			
			if(isset($field['onchange']) && !empty($field['onchange'])){
				$field_props .= ' onchange="'.$field['onchange'].'"';
			}
			
			if($ftype == 'text'){
				$field_html = '<input type="text" '. $field_props .' />';
				
			}else if($ftype == 'textarea'){
				$field_props  = 'name="'. $fname .'" style=""';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
				$field_html = '<textarea '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'.$fvalue.'</textarea>';
				
			}else if($ftype == 'select'){
				$field_html = '<select '. $field_props .' >';
				foreach($field['options'] as $value=>$label){
					$selected = $value === $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. THWCFE_i18n::t($label) .'</option>';
				}
				$field_html .= '</select>';
				
			}else if($ftype == 'multiselect'){
				$field_props  = 'name="'. $fname .'[]" data-value="'. $fvalue .'" style="'. $input_width .'"';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
				
				$field_html = '<select multiple="multiple" '. $field_props .' class="thwcfe-enhanced-multi-select" >';
				foreach($field['options'] as $value=>$label){
					//$selected = $value === $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. trim($value) .'" >'. THWCFE_i18n::t($label) .'</option>';
				}
				$field_html .= '</select>';
				
			}else if($ftype == 'multiselect_grouped'){
				$field_props  = 'name="'. $fname .'[]" data-value="'. $fvalue .'" style="'. $input_width .'"';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
			
				$field_html = '<select multiple="multiple" '. $field_props .' class="thwcfe-enhanced-multi-select" >';
				
				foreach($field['options'] as $group_label => $fields){
					$field_html .= '<optgroup label="'. $group_label .'">';
					
					foreach($fields as $value => $label){
						$value = trim($value);
						if(isset($field['glue']) && !empty($field['glue'])){
							$value = $value.$field['glue'].trim($label);
						}
						
						$field_html .= '<option value="'. $value .'">'. THWCFE_i18n::t($label) .'</option>';
					}
					
					$field_html .= '</optgroup>';
				}
				
				$field_html .= '</select>';
				
			}else if($ftype == 'colorpicker'){
				$field_html  = '<span class="thpladmin-colorpickpreview '.$field['name'].'_preview" style=""></span>';
                $field_html .= '<input type="text" '. $field_props .' class="thpladmin-colorpick"/>';              
            
			}
			
			$label_cell_props = !empty($args['label_cell_props']) ? ' '.$args['label_cell_props'] : '';
			$input_cell_props = !empty($args['input_cell_props']) ? ' '.$args['input_cell_props'] : '';
			?>
            
			<td <?php echo $label_cell_props ?> > <?php 
				echo $flabel; echo $required_html; 
				
				if(isset($field['sub_label']) && !empty($field['sub_label'])){
					?>
                    <br /><span class="thpladmin-subtitle"><?php THWCFE_i18n::et($field['sub_label']); ?></span>
					<?php
				}
				?>
            </td>
            
            <?php 
			$tooltip = ( isset($field['hint_text']) && !empty($field['hint_text']) ) ? $field['hint_text'] : false;
			$this->render_form_element_tooltip($tooltip);
			?>
            
            <td <?php echo $input_cell_props ?> ><?php echo $field_html; ?></td>
            
            <?php
		}
	}
	
	public function render_form_field_element_checkbox($field, $atts=array(), $render_cell=false){
		$args = shortcode_atts( array( 
			'cell_props'  => '', 
			'input_props' => '', 
			'label_props' => '', 
			'name_prefix' => 'i_', 
			'id_prefix' => 'a_f' 
		), $atts );
		
		$fid    = $args['id_prefix'].$field['name'];
		$fname  = $args['name_prefix'].$field['name'];
		$fvalue = isset($field['value']) ? $field['value'] : '';
		$flabel = __($field['label'],'woocommerce-checkout-field-editor-pro');
		
		$field_props  = 'id="'. $fid .'" name="'. $fname .'"';
		$field_props .= !empty($fvalue) ? ' value="'. $fvalue .'"' : '';
		$field_props .= $field['checked'] ? ' checked' : '';
		$field_props .= $args['input_props'];
		$field_props .= isset($field['onchange']) && !empty($field['onchange']) ? ' onchange="'.$field['onchange'].'"' : '';
		
		$field_html  = '<input type="checkbox" '. $field_props .' />';
		$field_html .= '<label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label>';
		
		if($render_cell){
		?>
			<td <?php echo $args['cell_props']; ?> ><?php echo $field_html; ?></td>
		<?php 
		}else{
		?>
			<?php echo $field_html; ?>
		<?php 
		}
	}
	
	public function render_form_section_separator($props, $atts=array()){
		?>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:10px;"></td></tr>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" class="thpladmin-form-section-title" ><?php echo $props['title']; ?></td></tr>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:0px;"></td></tr>
		<?php
	}
	
   /*******************************************
	*-------- HTML FORM FRAGMENTS - END   -----
	*******************************************/
}

endif;