<?php
/**
 * Checkout Field - Select
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Select')):

class WCFE_Checkout_Field_Select extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'select';
	}	
	
	/*public function get_html(){
		$html = '';
		if($this->is_enabled()){
			$select_html  = '<select id="'.$this->get_name().'" name="'.$this->get_name().'" value="'.$this->get_value().'" >';
			$select_html .= $this->get_placeholder() ? '<option value="" selected="selected">'.$this->get_placeholder().'</option>' : '';
			
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