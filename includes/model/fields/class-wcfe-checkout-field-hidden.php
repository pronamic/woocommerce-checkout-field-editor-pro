<?php
/**
 * Checkout Field - Hidden
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Hidden')):

class WCFE_Checkout_Field_Hidden extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'hidden';
	}	
		
	/*public function get_html(){
		$html = '';
		/*if($this->is_enabled()){
			$html .= '<tr class="'. $this->get_cssclass_str() .'">';
			$html .= '<td class="label '.$this->get_title_position().'">'.$this->get_title_html().'</td">';
			$html .= '<td class="value '.$this->get_title_position().'">';
			$html .= '<input type="text" id="'.$this->get_name().'" name="'.$this->get_name().'" placeholder="'.$this->get_placeholder().'" value="'.$this->get_value().'" />';
			$html .= '</td>';
			$html .= '</tr>';
		}*/
		/*return $html;
	}
	
	public function render_field(){
		echo $this->get_html();
	}	*/
}

endif;