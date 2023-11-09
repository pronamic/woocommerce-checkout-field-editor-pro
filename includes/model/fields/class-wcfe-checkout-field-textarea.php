<?php
/**
 * Checkout Field - Textarea
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Textarea')):

class WCFE_Checkout_Field_Textarea extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'textarea';
	}	
		
	/*public function get_html(){
		$html = '';
		if($this->is_enabled()){
			$html .= '<tr class="'. $this->get_cssclass_str() .'">';
			$html .= '<td class="label '.$this->get_title_position().'">'.$this->get_title_html().'</td">';
			$html .= '<td class="value '.$this->get_title_position().'">';
			$html .= '<textarea id="'.$this->get_name().'" name="'.$this->get_name().'" placeholder="'.$this->get_placeholder().'">'.$this->get_value().'</textarea>';
			$html .= '</td>';
			$html .= '</tr>';
		}	
		return $html;
	}
	
	public function render_field(){
		echo $this->get_html();
	}	*/
}

endif;