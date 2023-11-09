<?php
/**
 * The admin section forms functionalities.
 *
 * @link       https://themehigh.com
 * @since      3.1.4
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Form_Section')):

class THWCFE_Admin_Form_Section extends THWCFE_Admin_Form{
	private $section_props = array();

	public function __construct() {
		$this->section_props = $this->get_section_form_props();
	}

	public function get_available_positions(){
		$positions = array(
			//'before_checkout_form' => 'Before checkout form',
			//'after_checkout_form' => 'After checkout form',
			'before_customer_details' => 'Before customer details',
			'after_customer_details' => 'After customer details',
			'before_checkout_billing_form' => 'Before billing form',
			'after_checkout_billing_form' => 'After billing form',
			'before_checkout_shipping_form' => 'Before shipping form',
			'after_checkout_shipping_form' => 'After shipping form',
			'before_checkout_registration_form' => 'Before registration form',
			'after_checkout_registration_form' => 'After registration form',
			'before_order_notes' => 'Before order_notes',
			'after_order_notes' => 'After order notes',
			'before_terms_and_conditions' => 'Before terms and conditions',
			'after_terms_and_conditions' => 'After terms and conditions',
			'before_submit' => 'Before submit button',
			'after_submit' => 'After submit button',
			/*
			'before_cart_contents' => 'Review Order - Before cart contents',
			'after_cart_contents' => 'Review Order - After cart contents',
			'before_order_total' => 'Review Order - Before order total',
			'after_order_total' => 'Review Order - After order total',
			'before_order_review' => 'Before order review wrapper',
			'after_order_review' => 'After order review wrapper',
			'order_review_0' => 'Before order review content',
			'order_review_99' => 'After order review content',*/
		);

		if(apply_filters('thwcfe_enable_review_order_section_positions', false)){
			$positions['before_cart_contents'] = 'Review Order - Before cart contents';
			$positions['after_cart_contents'] = 'Review Order - After cart contents';
			$positions['before_order_total'] = 'Review Order - Before order total';
			$positions['after_order_total'] = 'Review Order - After order total';
			$positions['before_order_review_heading'] = 'Before order review heading';
			$positions['before_order_review'] = 'Before order review wrapper';
			$positions['after_order_review'] = 'After order review wrapper';
			$positions['order_review_0'] = 'Before order review content';
			$positions['order_review_99'] = 'After order review content';
		}
		
		$custom_positions = apply_filters('thwcfe_custom_section_positions', array());
		if(is_array($custom_positions)){
			$positions = array_merge($positions, $custom_positions);
		}
		
		return $positions;
	}
	
	public function get_section_form_props(){
		$positions = $this->get_available_positions();
		$html_text_tags = $this->get_html_text_tags();

		$suffix_types = array(
			'number' => 'Number',
			'alphabet' => 'Alphabet',
			'none' => 'None',
		);

		$suffix_types_1 = array(
			'number' => 'Number',
			'alphabet' => 'Alphabet',
		);
		
		return array(
			'name' 		 => array('name'=>'name', 'label'=>'Name/ID', 'type'=>'text', 'required'=>1),
			'position' 	 => array('name'=>'position', 'label'=>'Display Position', 'type'=>'select', 'options'=>$positions, 'required'=>1 ,'onchange'=>'thwcfeDisplayPositionChangeListner(this)'),
			//'box_type' 	 => array('name'=>'box_type', 'label'=>'Box Type', 'type'=>'select', 'options'=>$box_types),
			'cssclass' 	 => array('name'=>'cssclass', 'label'=>'CSS Class', 'type'=>'text'),
			'show_title' => array('name'=>'show_title', 'label'=>'Show section title in checkout page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			'show_title_my_account' => array('name'=>'show_title_my_account', 'label'=>'Show section title in my account page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			'order' 		 => array('name'=>'order', 'label'=>'Display Order', 'type'=>'text'),
			
			'title' 		   => array('name'=>'title', 'label'=>'Title', 'type'=>'text', 'required'=>1 ),
			//'title_position' => array('name'=>'title_position', 'label'=>'Title Position', 'type'=>'select', 'options'=>$title_positions),
			'title_type' 	   => array('name'=>'title_type', 'label'=>'Title Type', 'type'=>'select', 'value'=>'h3', 'options'=>$html_text_tags),
			'title_color' 	   => array('name'=>'title_color', 'label'=>'Title Color', 'type'=>'colorpicker'),
			'title_class' 	   => array('name'=>'title_class', 'label'=>'Title Class', 'type'=>'text'),
			
			'subtitle' 			  => array('name'=>'subtitle', 'label'=>'Subtitle', 'type'=>'text'),
			//'subtitle_position' => array('name'=>'subtitle_position', 'label'=>'Subtitle Position', 'type'=>'select', 'options'=>$title_positions),
			'subtitle_type' 	  => array('name'=>'subtitle_type', 'label'=>'Subtitle Type', 'type'=>'select', 'value'=>'h3', 'options'=>$html_text_tags),
			'subtitle_color' 	  => array('name'=>'subtitle_color', 'label'=>'Subtitle Color', 'type'=>'colorpicker'),
			'subtitle_class' 	  => array('name'=>'subtitle_class', 'label'=>'Subtitle Class', 'type'=>'text'),

			'rpt_name_suffix' => array('type'=>'select', 'name'=>'rpt_name_suffix', 'label'=>'Name Suffix', 'options'=>$suffix_types_1),
			'rpt_label_suffix' => array('type'=>'select', 'name'=>'rpt_label_suffix', 'label'=>'Label Suffix', 'options'=>$suffix_types),
			'rpt_incl_parent' => array('type'=>'checkbox', 'name'=>'rpt_incl_parent', 'label'=>'Start indexing from parent', 'value'=>'yes', 'checked'=>0),
			
			'inherit_display_rule' => array('type'=>'checkbox', 'name'=>'inherit_display_rule', 'label'=>'Inherit Cart & User based display rules', 'value'=>'yes', 'checked'=>1),
			'inherit_display_rule_ajax' => array('type'=>'checkbox', 'name'=>'inherit_display_rule_ajax', 'label'=>'Inherit Fields based display rules', 'value'=>'yes', 'checked'=>1),
			'auto_adjust_display_rule_ajax' => array('type'=>'checkbox', 'name'=>'auto_adjust_display_rule_ajax', 'label'=>'Adjust display rules based on fields in same section', 'value'=>'yes', 'checked'=>1),
		);
	}

	public function output_section_forms(){
		?>
        <div id="thwcfe_section_form_pp" class="thpladmin-modal-mask">
          <?php $this->output_popup_form_section(); ?>
        </div>
        <?php
	}

	/*****************************************/
	/********** POPUP FORM WIZARD ************/
	/*****************************************/

	private function output_popup_form_section(){
		?>
		<div class="thpladmin-modal">
			<div class="modal-container">
				<span class="modal-close" onclick="thwcfeCloseModal(this)">×</span>
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-wizard wizard">
							<aside>
								<side-title class="wizard-title">Save Section</side-title>
								<ul class="pp_nav_links">
									<li class="text-primary active first" data-index="0">
										<i class="dashicons dashicons-admin-generic text-primary"></i>Basic Info
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
									<li class="text-primary" data-index="1">
										<i class="dashicons dashicons-art text-primary"></i>Display Styles
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
									<li class="text-primary" data-index="2">
										<i class="dashicons dashicons-filter text-primary"></i>Display Rules
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
									<li class="text-primary last" data-index="3">
										<i class="dashicons dashicons-controls-repeat text-primary"></i>Repeat Rules
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
								</ul>
							</aside>
							<main class="form-container main-full">
								<form method="post" id="thwcfe_section_form" action="">
									<input type="hidden" name="s_action" value="" />
									<input type="hidden" name="s_name" value="" />
									<input type="hidden" name="s_name_copy" value="" />
									<input type="hidden" name="i_position_old" value="" />
									<input type="hidden" name="i_rules" value="" />
									<input type="hidden" name="i_rules_ajax" value="" />
									<input type="hidden" name="i_repeat_rules" value="" />

									<div class="data-panel data_panel_0">
										<?php $this->render_form_tab_general_info(); ?>
									</div>
									<div class="data-panel data_panel_1">
										<?php $this->render_form_tab_display_details(); ?>
									</div>
									<div class="data-panel data_panel_2">
										<?php $this->render_form_tab_display_rules(); ?>
									</div>
									<div class="data-panel data_panel_3">
										<?php $this->render_form_tab_repeat_rules(); ?>
									</div>
								</form>
							</main>
							<footer>
								<span class="Loader"></span>
								<div class="btn-toolbar">
									<button class="save-btn pull-right btn btn-primary" onclick="thwcfeSaveSection(this)">
										<span>Save & Close</span>
									</button>
									<button class="next-btn pull-right btn btn-primary-alt" onclick="thwcfeWizardNext(this)">
										<span>Next</span><i class="i i-plus"></i>
									</button>
									<button class="prev-btn pull-right btn btn-primary-alt" onclick="thwcfeWizardPrevious(this)">
										<span>Back</span><i class="i i-plus"></i>
									</button>
								</div>
							</footer>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/*----- TAB - General Info -----*/
	private function render_form_tab_general_info(){
		$this->render_form_tab_main_title('Basic Details');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<div class="err_msgs"></div>
			<table class="thwcfe_pp_table">
				<?php
				$this->render_form_elm_row($this->section_props['name']);
				$this->render_form_elm_row($this->section_props['title']);
				$this->render_form_elm_row($this->section_props['subtitle']);
				$this->render_form_elm_row($this->section_props['position']);
				$this->render_form_elm_row($this->section_props['order']);
				//$this->render_form_elm_row($this->section_props['title_cell_with']);
				//$this->render_form_elm_row($this->section_props['field_cell_with']);

				$this->render_form_elm_row_cb($this->section_props['show_title']);
				$this->render_form_elm_row_cb($this->section_props['show_title_my_account']);			
				?>
			</table>
		</div>
		<?php
	}

	/*----- TAB - Display Details -----*/
	private function render_form_tab_display_details(){
		$this->render_form_tab_main_title('Display Settings');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<table class="thwcfe_pp_table">
				<?php
				$this->render_form_elm_row($this->section_props['cssclass']);
				$this->render_form_elm_row($this->section_props['title_class']);
				$this->render_form_elm_row($this->section_props['subtitle_class']);

				$this->render_form_elm_row($this->section_props['title_type']);
				$this->render_form_elm_row($this->section_props['title_color']);
				$this->render_form_elm_row($this->section_props['subtitle_type']);
				$this->render_form_elm_row($this->section_props['subtitle_color']);
				?>
			</table>
		</div>
		<?php
	}

	/*----- TAB - Display Rules -----*/
	private function render_form_tab_display_rules(){
		$this->render_form_tab_main_title('Display Rules');
		$this->render_form_tab_sub_title('Display Rule Cart');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<table class="thwcfe_pp_table thwcfe-display-rules">
				<?php
				$this->render_form_fragment_rules('section'); 
				$this->render_form_fragment_rules_ajax('section');
				?>
			</table>
		</div>
		<?php
	}

	/*----- TAB - Repeat Rules -----*/
	private function render_form_tab_repeat_rules(){
		$this->render_form_tab_main_title('Repeat Rules');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<table class="thwcfe_pp_table thwcfe-repeat-rules">
				<?php
				$this->render_form_fragment_repeat_rules($this->section_props, 'section');
				?>
			</table>
		</div>
		<?php
	}
	
}

endif;