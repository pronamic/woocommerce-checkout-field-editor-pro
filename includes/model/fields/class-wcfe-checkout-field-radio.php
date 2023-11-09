<?php
/**
 * Checkout Field - Radio
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Radio')):

class WCFE_Checkout_Field_Radio extends WCFE_Checkout_Field{
	public $options = array();
	
	public function __construct() {
		$this->type = 'radio';
	}	
	
	/*public function get_html(){
		$html = '';
		if($this->is_enabled()){
			$html .= '<tr class="'. $this->get_cssclass_str() .'">';
			$html .= '<td class="label '.$this->get_title_position().'">'.$this->get_title_html().'</td">';
			$html .= '<td class="value '.$this->get_title_position().'">';
			foreach($this->get_options() as $option_key => $option){
				$checked = checked($this->get_value(), esc_attr($option_key), false);
				
				$html .= '<label for="'. $this->get_name() .'" class="radio '. $this->get_title_class_str() .'" style="margin-right: 10px;">';
				$html .= '<input type="radio" id="'.$this->get_name().'" name="'.$this->get_name().'" value="'.esc_attr($option_key).'" '. $checked .' "/> ';
				$html .= $this->esc_html__wepo($option['text']) .'</label>';
			}
			$html .= '</td>';
			$html .= '</tr>';
		}	
		return $html;
	}
	
	public function render_field(){
		echo $this->get_html();
	}	*/
	
	/*public function get_price_final($product_price){
		$fprice = 0;
		$options = $this->get_options();
		if(is_array($options) && isset($options[$this->value])){
			$selected_option = $options[$this->value];
			if(isset($selected_option['price'])){
				$fprice = $selected_option['price'];
				
				if(isset($selected_option['price_type']) && $selected_option['price_type'] === 'percentage'){
					if(is_numeric($fprice) && is_numeric($product_price)){
						$fprice = ($fprice/100)*$product_price;
					}	
				}
			}
		}
		return $fprice;
	}*/
}

endif;