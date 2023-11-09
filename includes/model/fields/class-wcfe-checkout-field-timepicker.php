<?php
/**
 * Checkout Field - Time Picker
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_TimePicker')):

class WCFE_Checkout_Field_TimePicker extends WCFE_Checkout_Field{
	public $min_time = '';
	public $max_time = '';
	public $start_time = '';
	public $time_step = '';
	public $time_format = '';
	public $linked_date = '';
	public $disable_time_slot = '';
	
	public function __construct() {
		$this->type = 'timepicker';
	}	
	
	public function prepare_field($name, $field){
		if(!empty($field) && is_array($field)){
			parent::prepare_field($name, $field);
			
			$this->set_min_time( isset($field['min_time']) ? $field['min_time'] : '' );
			$this->set_max_time( isset($field['max_time']) ? $field['max_time'] : '' );
			$this->set_start_time( isset($field['start_time']) ? $field['start_time'] : '' );
			$this->set_time_step( isset($field['time_step']) ? $field['time_step'] : '' );
			$this->set_time_format( isset($field['time_format']) ? $field['time_format'] : '' );
			$this->set_linked_date( isset($field['linked_date']) ? $field['linked_date'] : '' );
			$this->set_disable_time_slot( isset($field['disable_time_slot']) ? $field['disable_time_slot'] : '');
		}
	}
		
	/*public function get_html(){
		$html = '';
		if($this->is_enabled()){
			$html .= '<tr class="'. $this->get_cssclass_str() .'">';
			$html .= '<td class="label '.$this->get_title_position().'">'.$this->get_title_html().'</td">';
			$html .= '<td class="value '.$this->get_title_position().'">';
			$html .= '<input type="text" id="'.$this->get_name().'" name="'.$this->get_name().'" placeholder="'.$this->get_placeholder().'" value="'.$this->get_value().'" ';
			$html .= 'class="thwepo-time-picker input-text" data-min-time="'.$this->get_min_time().'" data-max-time="'.$this->get_max_time().'" ';
			$html .= 'data-step="'.$this->get_time_step().'" data-format="'.$this->get_time_format().'"/>';
			$html .= '</td>';
			$html .= '</tr>';
		}	
		return $html;
	}
	
	public function render_field(){
		echo $this->get_html();
	}*/	
	
   /**********************************
	**** Setters & Getters - START ****
	***********************************/
	public function set_min_time($min_time){
		$this->min_time = $min_time;
	}
	public function set_max_time($max_time){
		$this->max_time = $max_time;
	}
	public function set_start_time($start_time){
		$this->start_time = $start_time;
	}
	public function set_time_step($time_step){
		$this->time_step = $time_step;
	}
	public function set_time_format($time_format){
		$this->time_format = $time_format;
	}
	public function set_linked_date($linked_date){
		$this->linked_date = $linked_date;
	}
	public function set_disable_time_slot($disable_time_slot){
		$this->disable_time_slot = $disable_time_slot;
	}
		
	/* Getters */
	public function get_min_time(){
		return empty($this->min_time) ? '' : $this->min_time;
	}
	public function get_max_time(){
		return empty($this->max_time) ? '' : $this->max_time;
	}
	public function get_start_time(){
		return empty($this->start_time) ? '' : $this->start_time;
	}
	public function get_time_step(){
		return empty($this->time_step) ? '' : $this->time_step;
	}
	public function get_time_format(){
		return empty($this->time_format) ? '' : $this->time_format;
	}
	public function get_linked_date(){
		return empty($this->linked_date) ? '' : $this->linked_date;
	}	
}

endif;