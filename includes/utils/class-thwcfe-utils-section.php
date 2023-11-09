<?php
/**
 * The checkout sections specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils_Section')):

class THWCFE_Utils_Section {
	static $DEFAULT_SECTIONS = array('billing', 'shipping', 'additional');
	static $SECTION_PROPS = array(
		'name' 	   => array('name'=>'name', 'value'=>''),		
		'position' => array('name'=>'position', 'value'=>''),
		'order'    => array('name'=>'order', 'value'=>''),
		'cssclass' => array('name'=>'cssclass', 'value'=>array(), 'value_type'=>'array'),
		
		'show_title'     => array('name'=>'show_title', 'value'=>1, 'value_type'=>'boolean'),
		'show_title_my_account' => array('name'=>'show_title_my_account', 'value'=>1, 'value_type'=>'boolean'),
		'custom_section' => array('name'=>'custom_section', 'value'=>1, 'value_type'=>'boolean'),
		
		'title' 	  => array('name'=>'title', 'value'=>''),
		'title_type'  => array('name'=>'title_type', 'value'=>''),
		'title_color' => array('name'=>'title_color', 'value'=>''),
		'title_class' => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
		
		'subtitle' 		 => array('name'=>'subtitle', 'value'=>''),
		'subtitle_type'  => array('name'=>'subtitle_type', 'value'=>''),
		'subtitle_color' => array('name'=>'subtitle_color', 'value'=>''),
		'subtitle_class' => array('name'=>'subtitle_class', 'value'=>array(), 'value_type'=>'array'),

		'rpt_name_suffix' => array('name'=>'rpt_name_suffix', 'value'=>''),
		'rpt_label_suffix' => array('name'=>'rpt_label_suffix', 'value'=>''),
		'rpt_incl_parent' => array('name'=>'rpt_incl_parent', 'value'=>0, 'value_type'=>'boolean'),

		'inherit_display_rule'      => array('name'=>'inherit_display_rule', 'value'=>0, 'value_type'=>'boolean'),
		'inherit_display_rule_ajax' => array('name'=>'inherit_display_rule_ajax', 'value'=>0, 'value_type'=>'boolean'),
		'auto_adjust_display_rule_ajax' => array('name'=>'auto_adjust_display_rule_ajax', 'value'=>0, 'value_type'=>'boolean'),
	);
	
	public static function is_valid_section($section){
		if(isset($section) && $section instanceof WCFE_Checkout_Section && !empty($section->name)){
			return true;
		} 
		return false;
	}
	
	public static function is_enabled($section){
		if(self::is_valid_section($section) && $section->get_property('enabled')){
			return true;
		}
		return false;
	}

	public static function is_valid_enabled_section($section){
		if(self::is_valid_section($section) && self::is_enabled($section)){
			return true;
		}
		return false;
	}
	
	public static function is_custom_section($section){
		return $section->custom_section;
	}

	public static function has_fields($section){
		if($section->get_property('fields')){
			return true;
		}
		return false;
	}
	
	public static function is_show_section($section, $cart_info=false){
		$show = true;
		if(self::is_enabled($section)){
			$rules_set_list = $section->get_property('conditional_rules');
			$valid = THWCFE_Utils_Condition::is_satisfied($rules_set_list, $cart_info);
			if($section->get_property('rules_action') === 'hide'){
				$show = $valid ? false : true;
			}else{
				$show = $valid ? true : false;
			}
		}else{
			$show = false;
		}
		$show = apply_filters('thwcfe_show_section', $show, $section->name);
		return $show;
	}

	public static function is_show_section_title($section, $context='emails'){
		$show = true;
		if(self::is_enabled($section)){
			if($context === 'admin_order'){

			}else if($context === 'customer_order'){

			}else if($context === 'emails'){

			}
		}else{
			$show = false;
		}
		$show = apply_filters('thwcfe_show_section_title_in_'.$context, $show, $section->name);
		return $show;
	}

	public static function has_user_fields($section, $fieldset=false){
		$has_user_fields = false;
		if(is_array($fieldset)){
			foreach($fieldset as $key => $field) {
				if(isset($field['custom']) && $field['custom']){
					$ftype = isset($field['type']) ? $field['type'] : 'text';
						
					if(isset($field['user_meta']) && $field['user_meta']){
						$has_user_fields = true;
					}else if(($ftype === 'label' || $ftype === 'heading') && (isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'])){
						$has_user_fields = true;
					}
				}
			}
		}
		return $has_user_fields;
	}

	public static function has_ajax_rules($section){
		$has_ajax_rules = false;
		if(self::is_enabled($section)){
			$has_ajax_rules = empty($section->get_property('conditional_rules_ajax_json')) ? false : true;
		}
		return $has_ajax_rules;
	}

	public static function get_property_set($section, $esc_attr=false){
		if(self::is_valid_section($section)){
			$props_set = array();
			
			foreach(self::$SECTION_PROPS as $pname => $props){
				$pvalue = $section->get_property($props['name']);
				
				if(isset($props['value_type']) && $props['value_type'] === 'array' && !empty($pvalue)){
					$pvalue = is_array($pvalue) ? $pvalue : explode(',', $pvalue);
				}
				
				if(isset($props['value_type']) && $props['value_type'] != 'boolean'){
					$pvalue = empty($pvalue) ? $props['value'] : $pvalue;
				}
				
				$pvalue = $esc_attr && is_string($pvalue) ? esc_attr($pvalue) : $pvalue;
				$props_set[$pname] = $pvalue;
			}
			
			$props_set['custom'] = self::is_custom_section($section);
			$props_set['rules_action'] = $section->get_property('rules_action');
			$props_set['rules_action_ajax'] = $section->get_property('rules_action_ajax');
			$props_set['repeat_rules'] = $section->get_property('repeat_rules');
			
			return $props_set;
		}else{
			return false;
		}
	}
	
	public static function get_property_json($section){
		$props_json = '';
		$props_set = self::get_property_set($section, true);
		
		if($props_set){
			//$props_json = htmlspecialchars(json_encode($props_set));
			$props_json = json_encode($props_set);
		}
		return $props_json;
	}

	public static function get_fields($section, $cart_info = false, $exclude_disabled = false){
		$fields = false;
		if(self::is_valid_section($section)){
			$fields = $section->get_property('fields');
			$fields = self::filter_fields($fields, $cart_info, $exclude_disabled);
		}
		return is_array($fields) && !empty($fields) ? $fields : array();
	}

	public static function filter_fields($fields, $cart_info = false, $exclude_disabled = false){
		if(is_array($fields) && ($cart_info || $exclude_disabled)){
			foreach($fields as $name => $field){
				if(THWCFE_Utils_Field::is_valid_field($field)){
					$is_enabled = THWCFE_Utils_Field::is_enabled($field);

					if(($exclude_disabled && !$is_enabled) || !THWCFE_Utils_Field::show_field($field, $cart_info)){
						unset($fields[$name]);
					}
				} 
			}
		}
		return $fields;
	}

	public static function get_fieldset($section, $cart_info = false, $exclude_disabled = true){
		$fieldset = array();
		$fields = self::get_fields($section);

		foreach($fields as $name => $field){
			if(THWCFE_Utils_Field::is_valid_field($field)){
				$is_enabled = THWCFE_Utils_Field::is_enabled($field);

				if($exclude_disabled && !$is_enabled){
					continue;
				}

				if(!THWCFE_Utils_Field::show_field($field, $cart_info)){
					continue;
				}

				$field_props = THWCFE_Utils_Field::get_property_set($field);
				$fieldset[$name] = $field_props;
			} 
		}
		return $fieldset;
	}

	public static function get_user_fieldset($section, $exclude_disabled = true){
		$fieldset = array();
		$fields = self::get_fields($section);

		foreach($fields as $name => $field){
			if(THWCFE_Utils_Field::is_valid_field($field)){
				if(THWCFE_Utils_Field::is_custom_field($field) && THWCFE_Utils_Field::is_user_field($field)){
					$is_enabled = THWCFE_Utils_Field::is_enabled($field);

					if($exclude_disabled && !$is_enabled){
						continue;
					}

					$fieldset[$name] = $field;
				}
			} 
		}
		return $fieldset;
	}

	public static function get_user_fieldset_full(){
		$fieldset = array();
		$sections = THWCFE_Utils::get_custom_sections();
		
		foreach($sections as $sname => $section){	
			$fields = self::get_user_fieldset($section);

			if(is_array($fields)){
				$fieldset = array_merge($fieldset,$fields);
			}
		}
		
		return $fieldset;
	}

	/*public static function get_fieldset_all($section, $exclude_disabled = true){
		$fieldset = array();
		$fields = self::get_fields($section);

		foreach($fields as $name => $field){
			if(THWCFE_Utils_Field::is_valid_field($field)){
				if($exclude_disabled && !THWCFE_Utils_Field::is_enabled($field)){
					continue;
				}

				$field_props = THWCFE_Utils_Field::get_property_set($field);
				$fieldset[$name] = $field_props;
			} 
		}
		return $fieldset;
	}
	
	public static function get_fieldset($section, $products, $categories, $product_variations=false){
		$fieldset = array();
		$fields = self::get_fields($section);

		foreach($fields as $name => $field){
			if(THWCFE_Utils_Field::is_valid_field($field)){
				if(THWCFE_Utils_Field::is_enabled($field)){
					if(THWCFE_Utils_Field::show_field($field, $products, $categories, $product_variations)){
						$field_props = THWCFE_Utils_Field::get_property_set($field);
						$fieldset[$name] = $field_props; 
					}
				}
			} 
		}
		return $fieldset;
	}*/

	public static function add_field($section, $field, $custom_field=1){
		if(self::is_valid_section($section) && THWCFE_Utils_Field::is_valid_field($field)){
			$new_order = 0;
			$order_array = wp_list_pluck($section->fields, 'order');
			if(!empty($order_array)){
				$new_order = max($order_array);
				if(!$new_order){
					$new_order = sizeof($section->fields);
				}
			}

			$field->set_property('order', $new_order + 1);
			$field->set_property('custom_field', $custom_field);
			$section->fields[$field->get_property('name')] = $field;
			return $section;
		}else{
			throw new Exception('Invalid Section or Field Object.');
		}
	}
	
	public static function update_field($section, $field){
		if(self::is_valid_section($section) && THWCFE_Utils_Field::is_valid_field($field)){
			$name = $field->get_property('name');
			$name_old = $field->get_property('name_old');
			$field_set = $section->fields;
			
			if(!empty($name) && is_array($field_set) && isset($field_set[$name_old])){
				$o_field = $field_set[$name_old];				
				$index = array_search($name_old, array_keys($field_set));
				$is_custom = ($name != $name_old) ? 1 : $o_field->get_property('custom_field');
				
				$field->set_property('order', $index);
				$field->set_property('custom_field', $is_custom);
				//$field_set[$name] = $field;
				//$section->fields[$name] = $field;
				
				if($name != $name_old){
					//unset($field_set[$name_old]);
					//$field_set = self::sort_field_set($field_set);

					$temp_field_set = array();
					foreach($field_set as $key => $ofield){
						if($key === $name_old){
							$temp_field_set[$name] = $field;
						}else{
							$temp_field_set[$key] = $ofield;
						}
					}
					$field_set = $temp_field_set;
				}else{
					$field_set[$name] = $field;
				}
				//$field_set = self::sort_field_set($field_set);
				$section->set_property('fields', $field_set);
			}
			return $section;
		}else{
			throw new Exception('Invalid Section or Field Object.');
		}
	}
	
	public static function clear_fields($section){
		if(self::is_valid_section($section)){
			$section->fields = array();
		}
		return $section;
	}

	public static function prepare_section_from_posted_data($posted, $form = 'new'){
		$name     = isset($posted['i_name']) ? $posted['i_name'] : '';
		$position = isset($posted['i_position']) ? $posted['i_position'] : '';
		$title    = isset($posted['i_title']) ? $posted['i_title'] : '';

		if(!$name || !$title || !$position){
			return;
		}
		
		if($form === 'edit'){
			$section = WCFE_Checkout_Fields_Utils::get_checkout_section($name);
		}else{
			$name = strtolower($name);
			$name = is_numeric($name) ? "s_".$name : $name;
				
			$section = new WCFE_Checkout_Section();
			$section->set_property('id', $name);
		}
		
		foreach( self::$SECTION_PROPS as $pname => $property ){
			$iname  = 'i_'.$pname;
			$pvalue = isset($posted[$iname]) ? $posted[$iname] : $property['value'];
			$pvalue = is_string($pvalue) ? trim(stripslashes($pvalue)) : $pvalue;
			
			if($pname === 'show_title' || $pname === 'show_title_my_account'){
				$pvalue = !empty($pvalue) && $pvalue === 'yes' ? 1 : 0;
			}
			
			$section->set_property($pname, $pvalue);
		}
		
		if($form != 'edit'){
			$name = urldecode( sanitize_title(wc_clean($name)) );
			$section->set_property('name', $name);
			$section->set_property('id', $name);
		}
		
		$section->set_property('custom_section', 1);
		
		$section->set_property('rules_action', isset($posted['i_rules_action']) ? trim(stripslashes($posted['i_rules_action'])) : '');
		$section->set_property('conditional_rules_json', isset($posted['i_rules']) ? trim(stripslashes($posted['i_rules'])) : '');
		$section->set_property('conditional_rules', THWCFE_Utils_Condition::prepare_conditional_rules($posted, false));
		
		$section->set_property('rules_action_ajax', isset($posted['i_rules_action_ajax']) ? trim(stripslashes($posted['i_rules_action_ajax'])) : '');
		$section->set_property('conditional_rules_ajax_json', isset($posted['i_rules_ajax']) ? trim(stripslashes($posted['i_rules_ajax'])) : '');
		$section->set_property('conditional_rules_ajax', THWCFE_Utils_Condition::prepare_conditional_rules($posted, true));

		$section->set_property('repeat_rules', isset($posted['i_repeat_rules']) ? trim(stripslashes($posted['i_repeat_rules'])) : '');
		
		//WPML Support
		self::add_wpml_support($section);
		return $section;
	}
	
	public static function get_title_html($section, $index){
		$title_html = '';
		if($section->get_property('title')){
			$title_type  = $section->get_property('title_type') ? $section->get_property('title_type') : 'label';
			$title_style = $section->get_property('title_color') ? 'style="color:'.$section->get_property('title_color').';"' : '';
			$title = __($section->get_property('title'), 'woocommerce-checkout-field-editor-pro');
			$repeat_rule_set = $section->get_property('repeat_rules');

			if((isset($repeat_rule_set) && !empty($repeat_rule_set)) && apply_filters('thwcfe_section_name_edit_repeat_rule',false)){
				if($index == 1){
					if(preg_match('/\d/', $title)){
						$title = preg_replace('/\d/', '', $title);
					}
				}
			}
			
			$title_args = apply_filters('thwcfe_section_title_args', '', $section->get_property('title_class'));
			$title_html .= '<'.$title_type.' class="'.$section->get_property('title_class').'" '.$title_style.' '.$title_args.'>'.$title.'</'.$title_type.'>';
		}
		
		$subtitle_html = '';
		if($section->get_property('subtitle')){
			$subtitle_type  = $section->get_property('subtitle_type') ? $section->get_property('subtitle_type') : 'span';
			$subtitle_style = $section->get_property('subtitle_color') ? 'style="color:'.$section->get_property('subtitle_color').';"' : '';
			$subtitle = __($section->get_property('subtitle'), 'woocommerce-checkout-field-editor-pro');
			
			$subtitle_html .= '<'.$subtitle_type.' class="'.$section->get_property('subtitle_class').'" '.$subtitle_style.'>'.$subtitle.'</'.$subtitle_type.'>';
		}
		
		$html = $title_html;
		if(!empty($subtitle_html)){
			$html .= $subtitle_html;
		}
		return $html;
	}

	public static function sort_fields($section){
		if(is_array($section->fields)){
			uasort($section->fields, array('self', 'sort_by_order'));
		}
		return $section;
	}
	
	/*public static function sort_field_set($field_set){
		uasort($field_set, array('self', 'sort_by_order'));
		return $field_set;
	}*/
	
	public static function sort_by_order($a, $b){
	    if($a->get_property('order') == $b->get_property('order')){
	        return 0;
	    }
	    return ($a->get_property('order') < $b->get_property('order')) ? -1 : 1;
	}
	
	public static function add_wpml_support($section){
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Section Title - '.$section->name, $section->title );
		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Section Subtitle - '.$section->name, $section->subtitle );
	}
}

endif;