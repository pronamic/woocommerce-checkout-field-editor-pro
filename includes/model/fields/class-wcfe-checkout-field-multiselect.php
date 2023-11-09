<?php
/**
 * Checkout Field - Multiselect
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Multiselect')):

class WCFE_Checkout_Field_Multiselect extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'multiselect';
	}	
	
	/*public function get_html(){
		$html = '';
		if($this->is_enabled()){
			$select_html  = '<select multiple="multiple" id="'.$this->get_name().'" name="'.$this->get_name().'[]" data-placeholder="'.$this->get_placeholder().'" ';
			$select_html .= 'value="'.$this->get_value().'" class="thwepo-enhanced-multi-select">'; 
			
			foreach($this->get_options() as $option_key => $option){		
				$selected 	  = ($option['text'] === $this->get_value()) ? 'selected' : '';
				$price_suffix = $option['price_type'] === 'percentage' ? '%' : '';
				$option_text  = $option['text'];
				$option_text .= is_numeric($option['price']) ? ' (+'.$option['price'].$price_suffix.')' : '';
						
				$select_html .= '<option value="'.$option_key.'" '.$selected.'>'.$option_text.'</option>';
			}
			
			$select_html .= '</select>';
			
			$html .= '<tr class="'. $this->get_cssclass_str() .'">';
			$html .= '<td class="label '.$this->get_title_position().'">'.$this->get_title_html().'</td">';
			$html .= '<td class="value '.$this->get_title_position().'">'.$select_html.'</td>';
			$html .= '</tr>';
		}	
		return $html;
	}
	
	public function render_field(){
		echo $this->get_html();
	}*/
	
	/*public function get_price_final($product_price){
		$fprice = 0;
		$options = $this->get_options();
		
		if(is_array($options) && is_array($this->value)){
			foreach($this->value as $option_value){
				if(isset($options[$option_value])){
					$selected_option = $options[$option_value];
					
					if(isset($selected_option['price'])){
						$price = $selected_option['price'];
						
						if(isset($selected_option['price_type']) && $selected_option['price_type'] === 'percentage'){
							if(is_numeric($price) && is_numeric($product_price)){
								$fprice = $fprice + ($price/100)*$product_price;
							}	
						}else{
							if(is_numeric($price)){
								$fprice = $fprice + $price;
							}
						}
					}
				}
			}
		}
		return $fprice;
	}*/
}

endif;