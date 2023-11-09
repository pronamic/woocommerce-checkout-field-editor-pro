<?php
/**
 * The display condition specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils_Condition')):

class THWCFE_Utils_Condition {
	const LOGIC_AND = 'and';
	const LOGIC_OR  = 'or';
	
	const PRODUCT = 'product';
	const PRODUCT_VARIATION = 'product_variation';
	const CATEGORY = 'category';
	const TAG = 'tag';
	const FIELD = 'field';
	const SHIPPING_CLASS = 'shipping_class';
	const PRODUCT_TYPE = 'product_type';
	
	const USER_ROLE_EQ = 'user_role_eq';
	const USER_ROLE_NE = 'user_role_ne';
	
	const CART_CONTAINS = 'cart_contains'; 
	const CART_NOT_CONTAINS = 'cart_not_contains'; 
	const CART_ONLY_CONTAINS = 'cart_only_contains';
	
	const CART_TOTAL_EQ = 'cart_total_eq'; 
	const CART_TOTAL_GT = 'cart_total_gt'; 
	const CART_TOTAL_LT = 'cart_total_lt';
	
	const CART_SUBTOTAL_EQ = 'cart_subtotal_eq'; 
	const CART_SUBTOTAL_GT = 'cart_subtotal_gt'; 
	const CART_SUBTOTAL_LT = 'cart_subtotal_lt';

	const SHIPPING_WEIGHT_EQ = 'shipping_weight_eq';
	const SHIPPING_WEIGHT_GT = 'shipping_weight_gt';
	const SHIPPING_WEIGHT_LT = 'shipping_weight_lt';

	
	/*const COUNT_EQ = 'count_eq'; 
	const COUNT_GT = 'count_gt'; 
	const COUNT_LT = 'count_lt';*/
		
	const VALUE_EMPTY = 'empty';
	const VALUE_NOT_EMPTY = 'not_empty';
	
	const VALUE_EQ = 'value_eq';
	const VALUE_NE = 'value_ne'; 
	const VALUE_GT = 'value_gt'; 
	const VALUE_LT = 'value_le';
	
	public static function is_valid_condition($condition){
		if($condition && $condition instanceof WCFE_Condition){
			$total_operators = array(self::CART_TOTAL_EQ, self::CART_TOTAL_GT, self::CART_TOTAL_LT, self::CART_SUBTOTAL_EQ, self::CART_SUBTOTAL_GT, self::CART_SUBTOTAL_LT, 
								self::USER_ROLE_EQ, self::USER_ROLE_NE, self::SHIPPING_WEIGHT_EQ, self::SHIPPING_WEIGHT_GT, 
								self::SHIPPING_WEIGHT_LT);
			
			if(!empty($condition->operand_type) && !empty($condition->operator)){
				return true;
			}else if(!empty($condition->operator) && in_array($condition->operator, $total_operators) && !THWCFE_Utils::is_blank($condition->operand)){
				return true;
			}
		}
		return false;
	}
	
	public static function is_satisfied($rules_set_list, $cart_info){
		$valid = true;
		if(is_array($rules_set_list) && !empty($rules_set_list)){
			foreach($rules_set_list as $rules_set){				
				if(!self::is_satisfied_rules_set($rules_set, $cart_info)){
					$valid = false;
				}
			}
		}
		return $valid;
	}
	
	public static function is_satisfied_rules_set($rules_set, $cart_info){
		$satisfied = true;
		$condition_rules = $rules_set->get_condition_rules();
		$logic = $rules_set->get_logic();
		
		if(!empty($condition_rules)){
			if($logic === self::LOGIC_AND){			
				foreach($condition_rules as $condition_rule){				
					if(!self::is_satisfied_rule($condition_rule, $cart_info)){
						$satisfied = false;
						break;
					}
				}
			}else if($logic === self::LOGIC_OR){
				$satisfied = false;
				foreach($condition_rules as $condition_rule){				
					if(self::is_satisfied_rule($condition_rule, $cart_info)){
						$satisfied = true;
						break;
					}
				}
			}
		}
		return $satisfied;
	}
	
	private static function is_satisfied_rule($rule, $cart_info){
		$satisfied = true;
		$conditions_set_list = $rule->get_condition_sets();
		$logic = $rule->get_logic();
		
		if(!empty($conditions_set_list)){
			if($logic === self::LOGIC_AND){			
				foreach($conditions_set_list as $conditions_set){				
					if(!self::is_satisfied_conditions_set($conditions_set, $cart_info)){
						$satisfied = false;
						break;
					}
				}
			}else if($logic === self::LOGIC_OR){
				$satisfied = false;
				foreach($conditions_set_list as $conditions_set){				
					if(self::is_satisfied_conditions_set($conditions_set, $cart_info)){
						$satisfied = true;
						break;
					}
				}
			}			
		}
		return $satisfied;
	}
	
	private static function is_satisfied_conditions_set($conditions_set, $cart_info){
		$satisfied = true;
		$conditions = $conditions_set->get_conditions();
		$logic = $conditions_set->get_logic();

		if(!empty($conditions)){			 
			if($logic === self::LOGIC_AND){			
				foreach($conditions as $condition){				
					if(!self::is_satisfied_condition($condition, $cart_info)){
						$satisfied = false;
						break;
					}
				}
			}else if($logic === self::LOGIC_OR){
				$satisfied = false;
				foreach($conditions as $condition){				
					if(self::is_satisfied_condition($condition, $cart_info)){
						$satisfied = true;
						break;
					}
				}
			}
		}
		return $satisfied;
	}
	
	public static function is_satisfied_condition($condition, $cart_info){
		$satisfied = true;
		if(self::is_valid_condition($condition)){
			$products = false;
			$categories = false;
			$tags = false;
			$product_variations = false;
			$shipping_class = false;
			$product_type = false;

			if(is_array($cart_info)){
				$products = isset($cart_info['products']) ? $cart_info['products'] : false;
				$categories = isset($cart_info['categories']) ? $cart_info['categories'] : false;
				$tags = isset($cart_info['tags']) ? $cart_info['tags'] : false;
				$product_variations = isset($cart_info['variations']) ? $cart_info['variations'] : false;
				$shipping_class = isset($cart_info['shipping_class']) ? $cart_info['shipping_class'] : false;
				$product_type = isset($cart_info['product_type']) ? $cart_info['product_type'] : false;
			}

			$op_type  = $condition->operand_type;
			$operator = $condition->operator;
			$operands = apply_filters('thwcfe_operand_value', $condition->operand);

			if($op_type == self::PRODUCT && is_array($products)){
				$intersection = array();
				if(is_array($operands) && in_array('-1', $operands)){
					$operands = WCFE_Checkout_Fields_Utils::load_products(true);
				}
				
				if(is_array($products) && is_array($operands)){
					$intersection = array_intersect($products, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!self::is_subset_of($products, $operands)){
					//if($intersection != $values){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					sort($products);
					sort($operands);
					if($products != $operands){
						return false;
					}
				}
			}else if($op_type == self::PRODUCT_VARIATION && is_array($product_variations)){
				$intersection = array();
				$operands = is_array($operands) ? $operands : explode(',', $operands);
								
				if(is_array($product_variations) && is_array($operands)){
					$intersection = array_intersect($product_variations, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!self::is_subset_of($product_variations, $operands)){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					sort($product_variations);
					sort($operands);					
					if($product_variations != $operands){
						return false;
					}
				}
			}else if($op_type == self::CATEGORY && is_array($categories)){
				$intersection = array();
				if(is_array($operands) && in_array('-1', $operands)){
					$operands = THWCFE_Utils::load_products_cat(true, false);
				}
				$operands = self::check_for_wpml_translations($operands);

				if(is_array($categories)){
					$categories = array_unique($categories);

					if(is_array($operands)){
						$intersection = array_intersect($categories, $operands);
					}
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!self::is_subset_of($categories, $operands)){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					sort($categories);
					sort($operands);					
					if($categories != $operands){
						return false;
					}
				}
			}else if($op_type == self::TAG && is_array($tags)){
				if(in_array('-1', $operands)){
					$intersection = array();
					if(is_array($operands)){
						$operands = THWCFE_Utils::load_product_tags(true, false);
					}
					$operands = self::check_for_wpml_translations($operands);

					if(is_array($tags)){
						$tags = array_unique($tags);

						if(is_array($operands)){
							$intersection = array_intersect($tags, $operands);
						}
					}

					if($operator == self::CART_CONTAINS){
						if (($key = array_search("undefined", $tags)) !== false) {
						    unset($tags[$key]);
						}

						if(empty($tags)){
							return false;
						}
					}else if($operator == self::CART_NOT_CONTAINS){
						if (($key = array_search("undefined", $tags)) !== false) {
						    unset($tags[$key]);
						}

						if(!empty($tags)){
							return false;
						}
					}else if($operator == self::CART_ONLY_CONTAINS){
						if ((array_search("undefined", $tags)) !== false) {
						    return false;
						}
					}
				}else{
					$intersection = array();
					$operands = self::check_for_wpml_translations($operands);

					if(is_array($tags)){
						$tags = array_unique($tags);

						if(is_array($operands)){
							$intersection = array_intersect($tags, $operands);
						}
					}
					
					if($operator == self::CART_CONTAINS) {
						if(!self::is_subset_of($tags, $operands)){
							return false;
						}
					}else if($operator == self::CART_NOT_CONTAINS){
						if(!empty($intersection)){
							return false;
						}
					}else if($operator == self::CART_ONLY_CONTAINS){
						sort($tags);
						sort($operands);
						if($tags != $operands){
							return false;
						}
					}
				}
			}else if($op_type == self::SHIPPING_CLASS && is_array($shipping_class)){
				$intersection = array();
				$operands = is_array($operands) ? $operands : explode(',', $operands);
								
				if(is_array($shipping_class) && is_array($shipping_class)){
					$intersection = array_intersect($shipping_class, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!self::is_subset_of($shipping_class, $operands)){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					sort($shipping_class);
					sort($operands);
					if($shipping_class != $operands){
						return false;
					}
				}
			}else if($op_type == self::PRODUCT_TYPE && is_array($product_type)){
				$intersection = array();
				
				if(is_array($product_type) && is_array($operands)){
					$intersection = array_intersect($product_type, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!self::is_subset_of($product_type, $operands)){
					//if($intersection != $values){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					sort($product_type);
					sort($operands);
					if($product_type != $operands){
						return false;
					}
				}
			}else if($operator == self::USER_ROLE_EQ || $operator == self::USER_ROLE_NE){
				if(is_checkout() || is_account_page()){
					$user_roles = self::get_user_roles();

					if(is_array($user_roles) && is_array($operands)){
						$intersection = array_intersect($user_roles, $operands);
						
						if($operator == self::USER_ROLE_EQ) {
							if(empty($intersection)){
								return false;
							}
						}else if($operator == self::USER_ROLE_NE){
							if(!empty($intersection)){
								return false;
							}
						}
					}
				}
			}else{
				if(is_numeric($operands)){
					if($cart_info){
						$cart_total = isset($cart_info['cart_total']) ? $cart_info['cart_total'] : false;
						$cart_subtotal = isset($cart_info['cart_subtotal']) ? $cart_info['cart_subtotal'] : false;
						$shipping_weight = isset($cart_info['shipping_weight']) ? $cart_info['shipping_weight'] : false;
										
						if($operator == self::CART_TOTAL_EQ){
							if($cart_total != $operands){
								return false;
							}
						}else if($operator == self::CART_TOTAL_GT){
							if($cart_total <= $operands){
								return false;
							}
						}else if($operator == self::CART_TOTAL_LT){
							if($cart_total >= $operands){
								return false;
							}
						}else if($operator == self::CART_SUBTOTAL_EQ){
							if($cart_subtotal != $operands){
								return false;
							}
						}else if($operator == self::CART_SUBTOTAL_GT){
							if($cart_subtotal <= $operands){
								return false;
							}
						}else if($operator == self::CART_SUBTOTAL_LT){
							if($cart_subtotal >= $operands){
								return false;
							}
						}else if($operator == self::SHIPPING_WEIGHT_EQ){
							if($shipping_weight != $operands){
								return false;
							}
						}else if($operator == self::SHIPPING_WEIGHT_GT){
							if($shipping_weight <= $operands){
								return false;
							}
						}else if($operator == self::SHIPPING_WEIGHT_LT){
							if($shipping_weight >= $operands){
								return false;
							}
						}
					}
				}
			}
			/*else if($operator == self::EMPTY){
				
			}else if($operator == self::NOT_EMPTY){
				
			}*/
		}
		return $satisfied;
	}
	
	public static function prepare_conditional_rules($posted, $ajax=false){
		$iname = $ajax ? 'i_rules_ajax' : 'i_rules';
		$conditional_rules = isset($posted[$iname]) ? trim(stripslashes($posted[$iname])) : '';
		
		$condition_rule_sets = array();	
		if(!empty($conditional_rules)){
			$conditional_rules = urldecode($conditional_rules);
			$rule_sets = json_decode($conditional_rules, true);
				
			if(is_array($rule_sets)){
				foreach($rule_sets as $rule_set){
					if(is_array($rule_set)){
						$condition_rule_set_obj = new WCFE_Condition_Rule_Set();
						$condition_rule_set_obj->set_logic('and');
												
						foreach($rule_set as $condition_sets){
							if(is_array($condition_sets)){
								$condition_rule_obj = new WCFE_Condition_Rule();
								$condition_rule_obj->set_logic('or');
														
								foreach($condition_sets as $condition_set){
									if(is_array($condition_set)){
										$condition_set_obj = new WCFE_Condition_Set();
										$condition_set_obj->set_logic('and');
													
										foreach($condition_set as $condition){
											if(is_array($condition)){
												$condition_obj = new WCFE_Condition();
												$condition_obj->set_property('operand_type', isset($condition['operand_type']) ? $condition['operand_type'] : '');
												$condition_obj->set_property('operand', isset($condition['operand']) ? $condition['operand'] : '');
												$condition_obj->set_property('operator', isset($condition['operator']) ? $condition['operator'] : '');
												$condition_obj->set_property('value', isset($condition['value']) ? $condition['value'] : '');
												
												$condition_set_obj->add_condition($condition_obj);
											}
										}										
										$condition_rule_obj->add_condition_set($condition_set_obj);	
									}								
								}
								$condition_rule_set_obj->add_condition_rule($condition_rule_obj);
							}
						}
						$condition_rule_sets[] = $condition_rule_set_obj;
					}
				}	
			}
		}
		return $condition_rule_sets;
	}

	public static function prepare_conditional_rules_json($cr_set_list, $ajaxFlag=false){
		$conditional_rules_json = '';

		if(is_array($cr_set_list)){
			$condition_rules_arr = array();

			foreach($cr_set_list as $rules_set){	
				$condition_rules = $rules_set->get_condition_rules();
					
				if(is_array($condition_rules)){
					$rule_set_arr = array();

					foreach($condition_rules as $crk => $condition_rule){				
						$conditions_set_list = $condition_rule->get_condition_sets();
						
						if(is_array($conditions_set_list)){
							$rule_arr = array();

							foreach($conditions_set_list as $csk => $conditions_set){				
								$conditions = $conditions_set->get_conditions();
								
								if(is_array($conditions)){
									$conditions_arr = array();

									foreach($conditions as $condition){				
										if(self::is_valid_condition($condition)){
											$condition_arr = array();
											$condition_arr["operand_type"] = $condition->operand_type;
											$condition_arr["value"] = $condition->value;
											$condition_arr["operator"] = $condition->operator;
											$condition_arr["operand"] = $condition->operand;

											$conditions_arr[] = $condition_arr;
										}
									}

									if(!empty($conditions_arr)){
										$rule_arr[] = $conditions_arr;
									}
								}
							}

							if(!empty($rule_arr)){
								$rule_set_arr[] = $rule_arr;
							}
						}
					}

					if(!empty($rule_set_arr)){
						$condition_rules_arr[] = $rule_set_arr;
					}
				}
			}

			if(!empty($condition_rules_arr)){
				$conditional_rules_json = json_encode($condition_rules_arr, true);
				$conditional_rules_json = urlencode($conditional_rules_json);
			}
		}

		return $conditional_rules_json;
	}
	
	public static function filter_conditional_rules($conditional_rule_sets){
		if(is_account_page() && !is_checkout()){
			$user_conditions = array(WCFE_Condition::USER_ROLE_EQ, WCFE_Condition::USER_ROLE_NE);
			
			if(!empty($conditional_rule_sets) && is_array($conditional_rule_sets)){
				foreach($conditional_rule_sets as $rskey => $conditional_rule_set){
					$conditional_rules = $conditional_rule_set->get_condition_rules();
					if(!empty($conditional_rules) && is_array($conditional_rules)){
						foreach($conditional_rules as $rkey => $conditional_rule){
							$condition_sets = $conditional_rule->get_condition_sets();
							if(!empty($condition_sets) && is_array($condition_sets)){
								foreach($condition_sets as $cskey => $condition_set){				
									$conditions = $condition_set->get_conditions();
									if(!empty($conditions) && is_array($conditions)){
										foreach($conditions as $ckey => $condition){
											if(!in_array($condition->operator, $user_conditions)){
												unset($conditions[$ckey]);
											}
										}
									}
									if(empty($conditions)){
										unset($condition_sets[$cskey]);
									}
								}
							}
							if(empty($condition_sets)){
								unset($conditional_rules[$rkey]);
							}
						}
					}
					if(empty($conditional_rules)){
						unset($conditional_rule_sets[$rskey]);
					}
				}
			}
		}
		return $conditional_rule_sets;
	}
	
	public static function is_subset_of($arr1, $arr2){
		if(is_array($arr1) && is_array($arr2)){
			foreach($arr2 as $value){
				if(!in_array($value, $arr1)){
					return false;
				}
			}
		}
		return true;
	}
	
	public static function get_user_roles($user = false) {
		$user = $user ? new WP_User( $user ) : wp_get_current_user();
		
		if(!($user instanceof WP_User))
		   return false;
		   
		$roles = $user->roles;
		return $roles;
	}
	
	public static function get_wpml_translated_taxonomy($slug){
		/*if(function_exists('icl_object_id')){
			$english_ID_lang = icl_object_id ($slug, 'category', true, ICL_LANGUAGE_CODE);
		}*/
		
		$translated_slug = $slug;
		if(defined('ICL_LANGUAGE_CODE')){
			$translated_slug = ICL_LANGUAGE_CODE != 'en' ? $slug.'-'.ICL_LANGUAGE_CODE : $slug;
			$translated_slug = apply_filters( 'thwcfe_cr_wpml_translated_taxonomy', $translated_slug, $slug, ICL_LANGUAGE_CODE );
		}
		return $translated_slug;
	}
	
	public static function check_for_wpml_translations($taxonomies){
		if(apply_filters( 'thwcfe_cr_use_wpml_translated_taxonomy', false )){
			if(is_array($taxonomies)){
				foreach($taxonomies as $key => $value){
					$taxonomies[$key] = self::get_wpml_translated_taxonomy($value);
				}
			}
		}		
		return $taxonomies;
	}
}

endif;