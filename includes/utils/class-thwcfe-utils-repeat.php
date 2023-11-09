<?php
/**
 * The repeat fields specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      3.0.4
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes/utils
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils_Repeat')):

class THWCFE_Utils_Repeat {

	/*----- REPEAT SECTION - START -----*/
	public static function get_repeat_section_names($order_id){
		$order = wc_get_order( $order_id );
		// $data = get_post_meta( $order_id, '_thwcfe_repeat_sections', true );
		$data = $order->get_meta( '_thwcfe_repeat_sections', true );
		$sections = self::prepare_rsection_names_array($data);
		return $sections;
	}

	public static function get_repeat_section_names_from_posted($posted){
		$data = isset( $_POST['thwcfe_repeat_sections'] ) ? wc_clean( $_POST['thwcfe_repeat_sections'] ) : '';
		$sections = self::prepare_rsection_names_array($data);
		return $sections;
	}

	private static function prepare_rsection_names_array($data){
		$sections = array();
		if($data){
			$rsections = $data ? explode(",", $data) : array();

			foreach ($rsections as $rsnames_str) {
				$snames = $rsnames_str ? explode(":", $rsnames_str) : array();

				if(count($snames) > 1){
					$osname = $snames[0];
					unset($snames[0]);
					$sections[$osname] = $snames;
				}
			}
		}
		return $sections;
	}

	public static function prepare_repeat_sections_json() {
		$rsections = array();
		$sections = THWCFE_Utils::get_custom_sections();
		$cart_info = THWCFE_Utils::get_cart_summary();

		foreach($sections as $key => $section) {
			$show_section = true;
			if($key !== 'billing' && $key !== 'shipping'){
				$show_section = THWCFE_Utils_Section::is_show_section($section, $cart_info);
			}

			if($show_section){
				$rsnames = self::prepare_repeat_sections($section, false, true);
				$rsnames = is_array($rsnames) ? implode(':', $rsnames) : false;

				if($rsnames){
					$rsnames = $key.':'.$rsnames;
					$rsections[] = $rsnames;
				}
			}
		}
		return $rsections ? implode(',', $rsections) : '';
	}

	public static function get_repeat_sections($order_id, $key, $section, $rsnames){
		$rsections = false;
		if(is_array($rsnames) && array_key_exists($key, $rsnames)){
			$rn = is_array($rsnames[$key]) ? count($rsnames[$key]) : false;

			if(is_numeric($rn)){
				$rsections = self::prepare_repeat_sections($section, $rn+1);
			}
		}
		return $rsections;
	}

	public static function prepare_repeat_sections($section, $rn=false, $name_only=false){
		$rsections = array();

		if(THWCFE_Utils_Section::is_valid_section($section)){
			$r_exp = $section->get_property('repeat_rules');

			if($r_exp){
				$sname = $section->get_property('name');

				$rn = is_numeric($rn) ? $rn : self::get_repeat_times($r_exp, $sname, 'section');

				if($sname === 'billing' && $sname === 'shipping'){
					$rn = 0;
				}

				if($rn > 1){
					$rprops = self::prepare_repeat_props($section, true);

					for($i = 1 ; $i < $rn; $i++){
						$new_section = self::prepare_new_repeat_section($section, $i, $rprops);

						if($new_section){
							$new_name = $new_section->get_property('name');
							if($new_name){
								if($name_only){
									$rsections[] = $new_name;
								}else{
									$rsections[$new_name] = $new_section;
								}
							}
						}
					}
				}
			}
		}
		return $rsections;
	}

	private static function prepare_new_repeat_section($section, $index, $props){
		$new_section = false;
		$fields = $section->get_property('fields');

		if(is_array($fields)){
			$rfields = array();
			foreach($fields as $fname => $field) {
				$rfield = self::prepare_new_repeat_field_obj($field, $index, $props);
				$rfield = self::prepare_display_rules_field($rfield, $props, $index, $section);

				$rfname = $rfield->get_property('name');
				$rfields[$rfname] = $rfield;
			}

			$name = $section->get_property('name');
			$label = $section->get_property('title');

			$new_name  = self::prepare_new_name_section($name, $index, $props);
			$new_label = self::prepare_new_label_section($name, $label, $index, $props);

			$new_section = self::deepClone($section); //clone $section;
			$new_section->set_property('id', $new_name);
			$new_section->set_property('name', $new_name);
			$new_section->set_property('title', $new_label);
			$new_section->set_property('fields', $rfields);

			$new_section = self::prepare_display_rules_section($new_section, $props, $index);
		}
		return $new_section;
	}

	private static function prepare_new_name_section($name, $index, $props){
		$new_name = self::prepare_new_name($name, $index, $props);
		$new_name = apply_filters('thwcfe_repeat_section_name', $new_name, $name, $index);
		return $new_name;
	}

	private static function prepare_new_label_section($name, $label, $index, $props){
		$new_label = self::prepare_new_label($label, $index, $props);
		$new_label = apply_filters('thwcfe_repeat_section_label', $new_label, $label, $name, $index);
		return $new_label;
	}
	/*----- REPEAT SECTION - END -----*/


	/*----- REPEAT FIELD - START -----*/
	public static function get_repeat_field_names($order_id){
		$fields = array();
		$order = wc_get_order( $order_id );

		// $value = get_post_meta( $order_id, '_thwcfe_repeat_fields', true );
		$value = $order->get_meta( '_thwcfe_repeat_fields', true );
		if($value){
			$rfields = $value ? explode(",", $value) : array();

			foreach ($rfields as $rfnames_str) {
				$fnames = $rfnames_str ? explode(":", $rfnames_str) : array();

				if(count($fnames) > 1){
					$ofname = $fnames[0];
					unset($fnames[0]);
					$fields[$ofname] = $fnames;
				}
			}
		}
		return $fields;
	}

	public static function get_repeat_fields($order_id, $key, $field, $rfnames){
		$rfields = false;

		if(is_array($rfnames) && isset($rfnames[$key])){
			$rn = is_array($rfnames[$key]) ? count($rfnames[$key]) : false;
			if(is_numeric($rn)){
				$rfields = self::prepare_repeat_fields_obj($field, $rn+1);
			}
		}
		return $rfields;
	}

	public static function prepare_repeat_fields_single($field, $rn=false, $name_only=false){
		$fields = false;

		if(THWCFE_Utils_Field::is_valid_field($field)){
			$fields = self::prepare_repeat_fields_obj($field, $rn, $name_only);

		}else if(is_array($field)){
			$fields = self::prepare_repeat_fields_arr($field, $rn, $name_only);
		}
		return empty($fields) ? false : $fields;
	}

	public static function prepare_repeat_fields_set($fieldset, $exclude=array()){
		if(is_array($fieldset)){
			$has_repeat_field = false;
			$new_fieldset = array();
			$exclude = is_array($exclude) ? $exclude : array();

			foreach($fieldset as $name => $field) {
				$new_fieldset[$name] = $field;

				//if(!in_array($name, $exclude)){
					$rfields = self::prepare_repeat_fields_single($field);
					if(is_array($rfields)){
						$has_repeat_field = true;
						$new_fieldset = array_merge($new_fieldset, $rfields);
					}
				//}
			}
			if($has_repeat_field){
				$fieldset = $new_fieldset;
			}
		}
		return $fieldset;
	}

	private static function prepare_repeat_fields_arr($field, $rn=false, $name_only=false){
		$fields = array();
		$name   = $field['name'];
		$r_exp  = isset($field['repeat_rules']) ? $field['repeat_rules'] : false;

		if($r_exp){
			$rn = is_numeric($rn) ? $rn : self::get_repeat_times($r_exp, $name);
			if($rn > 1){
				$rprops = self::prepare_repeat_props($field, false);
				for($i = 1 ; $i < $rn; $i++){
					$new_field = self::prepare_new_repeat_field_arr($field, $i, $rprops);
					$new_name = isset($new_field['name']) ? $new_field['name'] : '';

					if($new_name){
						if($name_only){
							$fields[] = $new_name;
						}else{
							$fields[$new_name] = $new_field;
						}
					}
				}
			}
		}
		return $fields;
	}

	private static function prepare_repeat_fields_obj($field, $rn=false, $name_only=false){
		$fields = array();
		$key    = $field->get_property('name');
		$r_exp  = $field->get_property('repeat_rules');

		if($r_exp){
			$rn = is_numeric($rn) ? $rn : self::get_repeat_times($r_exp, $key);
			if($rn > 1){
				$rprops = self::prepare_repeat_props($field, true);

				for($i = 1 ; $i < $rn; $i++){
					$new_field = self::prepare_new_repeat_field_obj($field, $i, $rprops);
					$new_name = $new_field->get_property('name');

					if($new_name){
						if($name_only){
							$fields[] = $new_name;
						}else{
							$fields[$new_name] = $new_field;
						}
					}
				}
			}
		}

		return $fields;
	}

	private static function prepare_new_repeat_field_arr($field, $index, $props){
		$new_field = $field;

		$name  = isset($field['name']) ? $field['name'] : '';
		$label = isset($field['label']) ? $field['label'] : '';
		$priority = isset($field['priority']) ? $field['priority'] : '';

		$new_name  = self::prepare_new_name_field($name, $index, $props);
		$new_label = self::prepare_new_label_field($name, $label, $index, $props);
		$new_priority = self::prepare_new_priority_field($name, $priority, $index);

		$new_field['name']  = $new_name;
		$new_field['label'] = $new_label;
		$new_field['title'] = $new_label;
		$new_field['priority'] = $new_priority;
		$new_field['custom'] = true;

		return $new_field;
	}

	private static function prepare_new_repeat_field_obj($field, $index, $props){
		$new_field = self::deepClone($field); //clone $field;

		$name = $field->get_property('name');
		$label = $field->get_property('title');
		$priority = $field->get_property('priority');


		$new_name  = self::prepare_new_name_field($name, $index, $props);
		$new_label = self::prepare_new_label_field($name, $label, $index, $props);
		$new_priority = self::prepare_new_priority_field($name, $priority, $index);

		$new_field->set_property('id', $new_name);
		$new_field->set_property('name', $new_name);
		$new_field->set_property('name_old', $new_name);
		$new_field->set_property('title', $new_label);
		$new_field->set_property('priority', $new_priority);

		return $new_field;
	}

	private static function prepare_new_name_field($name, $index, $props){
		$new_name = self::prepare_new_name($name, $index, $props);
		$new_name = apply_filters('thwcfe_repeat_field_name', $new_name, $name, $index);
		return $new_name;
	}

	private static function prepare_new_label_field($name, $label, $index, $props){
		$new_label = self::prepare_new_label($label, $index, $props);
		$new_label = apply_filters('thwcfe_repeat_field_label', $new_label, $label, $name, $index);
		return $new_label;
	}

	private static function prepare_new_priority_field($name, $priority, $index){
		if($priority && $index){
			$float_index = $index / 100;
			$new_priority = $priority + $float_index;
			return apply_filters('thwcfe_repeat_field_priority', $new_priority, $priority, $name, $index);
		}
		return $priority;
	}

	//Deprecating
	public static function prepare_repeat_fields_json() {
		$rfields = array();
		$sections = THWCFE_Utils::get_custom_sections();
		$cart_info = THWCFE_Utils::get_cart_summary();

		foreach($sections as $sname => $section) {
			$show_section = true;
			if($sname !== 'billing' && $sname !== 'shipping'){
				$show_section = THWCFE_Utils_Section::is_show_section($section, $cart_info);
			}

			if($show_section){
				$fieldset = THWCFE_Utils::get_fieldset_to_show($section);
				$fieldset = $fieldset ? $fieldset : array();

				if(is_array($fieldset)){
					foreach ($fieldset as $key => $field) {
						$rnames = self::prepare_repeat_fields_single($field, false, true);
						$rnames = is_array($rnames) ? implode(':', $rnames) : false;

						if($rnames){
							$rnames = $key.':'.$rnames;
							$rfields[] = $rnames;
						}
					}
				}
			}
		}
		return $rfields ? implode(',', $rfields) : '';
	}
	/*----- REPEAT FIELD - END -----*/


	/*----- COMMON FUNCTIONS - START -----*/
	private static function prepare_repeat_props($field, $isobj){
		$props = array();

		if($isobj){
			$incl_parent  = $field->get_property('rpt_incl_parent');
			$props['incl_parent']  = $incl_parent === 'yes' ? true : false;
			$props['name_suffix']  = $field->get_property('rpt_name_suffix');
			$props['label_suffix'] = $field->get_property('rpt_label_suffix');

			$props['inherit_display_rule']      = $field->get_property('inherit_display_rule');
			$props['inherit_display_rule_ajax'] = $field->get_property('inherit_display_rule_ajax');
			$props['auto_adjust_display_rule_ajax'] = $field->get_property('auto_adjust_display_rule_ajax');
		}else{
			$incl_parent = isset($field['rpt_incl_parent']) ? $field['rpt_incl_parent'] : false;
			$props['incl_parent']  = $incl_parent === 'yes' ? true : false;
			$props['name_suffix']  = isset($field['rpt_name_suffix']) ? $field['rpt_name_suffix'] : 'number';
			$props['label_suffix'] = isset($field['rpt_label_suffix']) ? $field['rpt_label_suffix'] : 'number';

			$props['inherit_display_rule']      = $incl_parent === 'yes' ? true : false;
			$props['inherit_display_rule_ajax'] = $incl_parent === 'yes' ? true : false;
			$props['auto_adjust_display_rule_ajax'] = $incl_parent === 'yes' ? true : false;
		}

		return $props;
	}

	private static function prepare_new_name($name, $index, $props){
		$name_suffix = isset($props['name_suffix']) ? $props['name_suffix'] : 'number';
		$incl_parent = isset($props['incl_parent']) ? $props['incl_parent'] : false;

		$new_name = self::prepare_suffix($name, $incl_parent, $name_suffix, $index, 'name');
		return $new_name;
	}

	private static function prepare_new_label($label, $index, $props){
		$label_suffix = isset($props['label_suffix']) ? $props['label_suffix'] : 'number';
		$incl_parent  = isset($props['incl_parent']) ? $props['incl_parent'] : false;

		$new_label = self::prepare_suffix($label, $incl_parent, $label_suffix, $index, 'label');
		return $new_label;
	}

	private static function prepare_suffix($text, $incl_parent, $suffix_type, $index, $type){
		if($text && $suffix_type != 'none' && is_numeric($index)){
			$alphabet = range('A', 'Z');
			$index = $incl_parent ? $index+1 : $index;
			$text = self::clean_suffix($text, $incl_parent, $suffix_type, $type);

			$suffix = '';
			if($suffix_type === 'alphabet' && $index < 27){
				$suffix = $alphabet[$index-1];
				$suffix = $type === 'name' ? strtolower($suffix) : $suffix;
			}else{
				$suffix = $index;
			}

			$glue = $type === 'name' ? '_' : ' ';
			$text = $text.$glue.$suffix;
		}
		return $text;
	}

	private static function clean_suffix($text, $incl_parent, $suffix_type, $type){
		if($text && $incl_parent){
			if($suffix_type === 'alphabet'){
				$ch = $type === 'name' ? "_a" : " A";
				$text = rtrim($text, $ch);
			}else if($suffix_type === 'number'){
				$ch = $type === 'name' ? "_1" : " 1";
				$text = rtrim($text, "1");
			}
		}
		return $text;
	}

	/* Get Repeat number */
	public static function get_repeat_times($r_exp, $name='', $type='field'){
		$rt = 0;
		if($r_exp){
			$exp_arr = explode(":", $r_exp);
			if(count($exp_arr) == 2){
				$operator = $exp_arr[0];
				//$operand_type = $exp_arr[1];
				$operand = $exp_arr[1];

				if($operator === 'qty_product'){
					$rt = self::get_cart_item_qty($operand);
				}else if($operator === 'qty_cart'){
					$rt = self::get_cart_qty($operand);
				}else if($operator === 'qty_category'){
					$rt = self::get_cart_category_qty($operand);
				}
			}
		}
		$rt = apply_filters('thwcfe_repeat_times', $rt, $name, $type);
		return $rt;
	}

	public static function get_cart_qty( $product_id ){
		
		if(empty(WC()->cart->cart_contents)){
			return 0;
		}

		$count = WC()->cart->get_cart_contents_count();
	    return $count;
	}

	public static function get_cart_item_qty( $product_id ){
		
		if(empty(WC()->cart->cart_contents)){
			return 0;
		}

        $qty = 0;

	    foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item){
	        if ( $product_id == $cart_item['product_id'] ){
	            $qty += $cart_item['quantity'];
	        } elseif ($product_id == $cart_item['variation_id']){
				$qty += $cart_item['quantity'];
			}
	    }
	    return $qty;
	}

	public static function get_cart_category_qty( $cat_name ){		
		
		if(empty(WC()->cart->cart_contents)){
			return 0;
		}

		$cat_name = 'hoodies';
	    $count = 0;        

	    foreach(WC()->cart->get_cart() as $cart_item){
	    	if( has_term( $cat_name, 'product_cat', $cart_item['product_id'])){
	            $count += $cart_item['quantity'];
	    	}
	    }
	    return  $count;
	}

	/*----- PREPARE DISPLAY RULES - START -----*/
	private static function prepare_display_rules_section($section, $props, $index=false){
		$section = self::prepare_display_rules($section, $props, $index, false);
		return $section;
	}

	private static function prepare_display_rules_field($field, $props, $index=false, $section=false){
		$field = self::prepare_display_rules($field, $props, $index, $section);
		return $field;
	}

	private static function prepare_display_rules($new_obj, $props, $index=false, $section=false){
		$inherit_dr = isset($props['inherit_display_rule']) ? $props['inherit_display_rule'] : true;
		$inherit_dr_ajax = isset($props['inherit_display_rule_ajax']) ? $props['inherit_display_rule_ajax'] : true;

		if(!$inherit_dr){
			$new_obj->set_property('conditional_rules', array());
			$new_obj->set_property('conditional_rules_json', '');
		}

		if($inherit_dr_ajax){
			if($section && is_numeric($index)){
				$adjust_dr_ajax = isset($props['auto_adjust_display_rule_ajax']) ? $props['auto_adjust_display_rule_ajax'] : true;

				if($adjust_dr_ajax){
					$dr = self::prepare_display_rules_ajax($new_obj, $props, $index, $section);
					$dr_ajax = THWCFE_Utils_Condition::prepare_conditional_rules_json($dr, true);

					$new_obj->set_property('conditional_rules_ajax', $dr);
					$new_obj->set_property('conditional_rules_ajax_json', $dr_ajax);
				}
			}
		}else{
			$new_obj->set_property('conditional_rules_ajax', array());
			$new_obj->set_property('conditional_rules_ajax_json', '');
		}

		return $new_obj;
	}

	private static function prepare_display_rules_ajax($new_obj, $props, $index, $section){
		$rules_set_list = array();

		if($section && is_numeric($index)){
			$rules_set_list = $new_obj->get_property('conditional_rules_ajax');
			$sfields = $section->get_property('fields');

			if(is_array($rules_set_list) && !empty($rules_set_list) && is_array($sfields)){
				foreach($rules_set_list as $rsk => $rules_set){
					$condition_rules = $rules_set->get_condition_rules();

					if(!empty($condition_rules)){
						foreach($condition_rules as $crk => $condition_rule){
							$conditions_set_list = $condition_rule->get_condition_sets();

							if(!empty($conditions_set_list)){
								foreach($conditions_set_list as $csk => $conditions_set){
									$conditions = $conditions_set->get_conditions();

									if(!empty($conditions)){
										foreach($conditions as $ck => $condition){
											$operand_type = $condition->operand_type;
											$operands = $condition->operand;

											if($operand_type === 'field' && is_array($operands)){
												//$new_operands = array();

												foreach($operands as $opk => $operand){
													$field = isset($sfields[$operand]) ? $sfields[$operand] : false;

													if(THWCFE_Utils_Field::is_valid_field($field)){
														$operand = self::prepare_new_name_field($operand, $index, $props);
														$operands[$opk] = $operand;
													}
												}

												$condition->set_property('operand', $operands);
												$conditions[$ck] = $condition;
											}
										}

										$conditions_set->set_conditions($conditions);
										$conditions_set_list[$csk] = $conditions_set;
									}
								}

								$condition_rule->set_condition_sets($conditions_set_list);
								$condition_rules[$crk] = $condition_rule;
							}
						}

						$rules_set->set_condition_rules($condition_rules);
						$rules_set_list[$rsk] = $rules_set;
					}
				}
			}
		}

		return $rules_set_list;
	}
	/*----- PREPARE DISPLAY RULES - END -----*/

	private static function deepClone($object){
	    return unserialize(serialize($object));
	}

}

endif;
