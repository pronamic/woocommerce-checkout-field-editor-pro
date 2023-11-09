<?php
/**
 * 
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Condition_Set')):

class WCFE_Condition_Set {
	const LOGIC_AND = 'and';
	const LOGIC_OR  = 'or';
	
	public $logic = self::LOGIC_AND;
	public $conditions = array();
	
	public function __construct() {
		
	}	
	
	/*public function is_satisfied($cart_info){
		$satisfied = true;
		$conditions = $this->get_conditions();
		if(!empty($conditions)){			 
			if($this->get_logic() === self::LOGIC_AND){			
				foreach($conditions as $condition){				
					if(!$condition->is_satisfied($cart_info)){
						$satisfied = false;
						break;
					}
				}
			}else if($this->get_logic() === self::LOGIC_OR){
				$satisfied = false;
				foreach($conditions as $condition){				
					if($condition->is_satisfied($cart_info)){
						$satisfied = true;
						break;
					}
				}
			}
		}
		return $satisfied;
	}*/
	
	public function add_condition($condition){
		if(THWCFE_Utils_Condition::is_valid_condition($condition)){
			$this->conditions[] = $condition;
		} 
	}
	
	public function set_logic($logic){
		$this->logic = $logic;
	}	
	public function get_logic(){
		return $this->logic;
	}
		
	public function set_conditions($conditions){
		$this->conditions = $conditions;
	}	
	public function get_conditions(){
		return $this->conditions; 
	}	
}

endif;