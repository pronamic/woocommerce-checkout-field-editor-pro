<?php
/**
 * The settings import export functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Settings_Import_Export')):

class THWCFE_Admin_Settings_Import_Export extends THWCFE_Admin_Settings{
	public function __construct() {
		parent::__construct();
	}

	public function render_settings($settings){
		?>
	    <form id="import_export_settings_form" method="post" action="" class="clear">
            <table class="form-table thwcfe-settings-table">
                <tbody>
                	<tr>
						<td colspan="3" class="section-title"><?php _e('Backup and Import Settings', 'woocommerce-checkout-field-editor-pro'); ?></td>
					</tr>
					<tr>
						<td class="label">
							<span><?php _e('Plugin Settings Data', 'woocommerce-checkout-field-editor-pro'); ?></span>
							<span class="description"><?php _e('You can transfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Save Settings".', 'woocommerce-checkout-field-editor-pro'); ?></span>
						</td>
						<td class="tip"></td>
						<td class="field">
							<textarea name="i_settings_data" rows="10"><?php echo $settings; ?></textarea>
						</td>
					</tr>				
                </tbody>
                <tfoot>
                	<tr>
						<td colspan="3" class="actions">
							<input type="submit" name="save_plugin_settings" class="button-primary" value="Save Settings">
							<!--<input type="submit" name="import_settings" class="button" value="Import Settings(.txt)" onclick="thwcfeImportSettings()">
							<input type="submit" name="export_settings" class="button" value="Export Settings(.txt)">-->
						</td>
					</tr>
                </tfoot>
            </table>
        </form>
		<?php 
	}

	public function get_posted_settings_data(){
		$settings = THWCFE_Utils::get_posted_value($_POST, 'i_settings_data');
		$settings = $settings ? unserialize(base64_decode($settings)) : '';
		return $settings;
	}

	public function get_imported_settings_data(){
		$file = THWCFE_PATH."wcfe-pro-settings-import.txt";

		$settings = file_get_contents($file, true);
		$settings = $settings ? unserialize(base64_decode($settings)) : '';
		return $settings;
	}

	public function export_settings_data($settings){
		$file = THWCFE_PATH."wcfe-pro-settings.txt";

		$handle = fopen($file, "w") or die("Unable to open file!");
		fwrite($handle, $settings);
		fclose($handle);

		if (file_exists($file)) {
			ob_clean();
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'.basename($file).'"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    readfile($file);
		    unlink($file);
		    exit;
		}
	}

	public function prepare_settings_data($settings){
		$settings = $settings ? base64_encode(serialize($settings)) : '';
		return $settings;
	}
	
	public function import_settings(){
	
	}

	public function export_section($section_name){
		$section = $this->get_checkout_section($section_name);
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$section_data = base64_encode(serialize($section));
			$url = $this->export_data_to_file($section_data);
			if($url){

				$notice = sprintf(
					/* translators: 1: Media library URL 2: Exported file URL */
				    __( 'Section exported successfully. The exported file is available in <a href="%1$s">Media Library</a> or You can download it <a download href="%2$s">here</a>.', 'woocommerce-checkout-field-editor-pro'),
				    admin_url('upload.php'), esc_url($url)
				);

			    add_action('admin_notices', function() use ($notice){
			        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
			    });


			}else{
				$notice = __('An error occurred while exporting data. Please try again!', 'woocommerce-checkout-field-editor-pro');
			    add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
			    });
			}
		}
	}

	public function export_fields($section_name){
		$fields = [];
		$section = $this->get_checkout_section($section_name);
		if(THWCFE_Utils_Section::is_valid_section($section)){

			$field_set = THWCFE_Utils_Section::get_fields($section);

			$f_names = !empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();
			$f_selected = !empty( $_POST['f_selected'] ) ? $_POST['f_selected'] : array();

			if(!in_array(1, $f_selected)){
				$notice = __('Field(s) not exported. Choose at least one field to export!', 'woocommerce-checkout-field-editor-pro');
			    add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
			    });
			    return;
			}

			$max = max( array_map( 'absint', array_keys( $f_names )));
			for($i = 0; $i <= $max; $i++){
				if(isset($f_selected[$i]) && $f_selected[$i] == 1){

					$f_name = $f_names[$i];
					if(isset($field_set[$f_name])){
						$fields[$f_name] = $field_set[$f_name];
					}
				
				}
			}

			if(!empty($fields)){
				$field_data = base64_encode(serialize($fields));
				$url = $this->export_data_to_file($field_data);
				if($url){

					$notice = sprintf(
						/* translators: 1: Media library URL 2: Exported file URL */
					    __( 'Field(s) exported successfully. The exported file is available in <a href="%1$s">Media Library</a> or You can download it <a download href="%2$s">here</a>.', 'woocommerce-checkout-field-editor-pro'),
					    admin_url('upload.php'), esc_url($url)
					);
				    add_action('admin_notices', function() use ($notice){
				        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
				    });


				}else{
					$notice = __('An error occurred while exporting data. Please try again!', 'woocommerce-checkout-field-editor-pro');
				    add_action('admin_notices', function() use ($notice) {
				        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
				    });
				}
			}

		}
	}

	public function output_export_form(){
		?>
        <div id="thwcfe_export_form_pp" class="thpladmin-modal-mask">
			<div class="thpladmin-modal">
				<div class="modal-container" style="width:40%">
					<span class="modal-close" onclick="thwcfeCloseModal(this)">×</span>
					<div class="modal-content">
						<div class="modal-body">
							<div class="form-wizard wizard">
								<form method="post" id="thwcfe_import_form" action="" enctype="multipart/form-data">
									<main class="form-container main-full">
										<main-title classname="main-title">
											<span class="device-mobile main-title-icon text-primary"><i class="i-check drishy"></i>Import Fields / Section</span>
											<span class="device-desktop">Import Fields / Section</span>
										</main-title>
										<p>Please note down below points before importing fields / sections.</p>
										<ol style="list-style-type: decimal;">
										  <li style="color:red;">This action cannot be undone. All changes are permanent. So we suggest taking a database backup before proceeding.</li>
										  <li>When you import a section, if there is a section with the same name, the existing section will be replaced with the section & fields in the imported file.</li>
										  <li>If the imported section does not exist, the section & fields in the imported file will be added with the existing sections.</li>
										  <li>When you import fields, if there are fields with the same name in the current section, existing fields will be replaced with fields in the imported file.</li>
										  <li>If the imported fields do not exist in the current section, the fields in the imported file will be added with existing fields.</li>
										  <li>Importing default checkout fields into different sections may create unexpected behaviour.</li>
										</ol>

										<p><input type="file" name="file_to_import" accept=".txt" required></p>

									</main>
									<footer>
										<span class="Loader"></span>
										<div class="btn-toolbar">
											<input type="submit" name="submit" class="button button-primary" value="Import Now" >
											<?php wp_nonce_field( 'import_section_fields', 'thwcfe_form_security' ); ?>
											<input type="hidden" name="thwcfe_form_action" value="import_section_fields">
										</div>
									</footer>
								</form>
							</div>
							hello

						</div>
					</div>
				</div>
			</div>
        </div>
        <?php
	}

	private function export_data_to_file($data){
		
		$file_name = $this->file_name_for_export();
		$upload = wp_upload_bits($file_name, null, $data);
		if(!$upload['error']){
			$wp_filetype = wp_check_filetype($file_name, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
				'post_content' => '',
				'post_status' => 'private'
			);
			$attachment_id = wp_insert_attachment($attachment, $upload['file']);
			if($attachment_id){
				return $upload['url'];
			}

			return false;
		}
		// THWCFE_Utils::write_log($upload['error']);
		return false;
	}

	private function file_name_for_export(){
		$date_time = current_datetime();
		$file_name = 'wcfe-export-'.$date_time->format('Y-m-d-H-i-s').'.txt';
		return $file_name;
	}


	public function import_section_fields($section_name){
		if(empty($_FILES['file_to_import']['tmp_name']) || !is_uploaded_file($_FILES['file_to_import']['tmp_name'])){
			$notice = __('An error occurred while importing file. Please try again!', 'woocommerce-checkout-field-editor-pro');
			add_action('admin_notices', function() use ($notice) {
		        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
		    });
		    return;
		}

		$wp_filetype = wp_check_filetype_and_ext($_FILES['file_to_import']['tmp_name'], $_FILES['file_to_import']['name']);

		if(!wp_match_mime_types('text/plain', $wp_filetype['type'])) {
			$notice = __('Invalid file type. The importer supports TXT file format.', 'woocommerce-checkout-field-editor-pro');
			add_action('admin_notices', function() use ($notice) {
		        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
		    });
		    return;
		}

		$file_content = file_get_contents($_FILES['file_to_import']['tmp_name']);

		if(empty($file_content)){
			$notice = __('An error occurred while importing file. Invalid file!', 'woocommerce-checkout-field-editor-pro');
			add_action('admin_notices', function() use ($notice) {
		        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
		    });
		    return;
		}

		$data = @unserialize(base64_decode($file_content));

		if(empty($data)){
			$notice = __('An error occurred while importing file. Invalid file!', 'woocommerce-checkout-field-editor-pro');
			add_action('admin_notices', function() use ($notice) {
		        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
		    });
		    return;
		}

		if(is_object($data)){
			$this->import_section($data);
		}else{
			$this->import_fields($data, $section_name);
		}

	}


	private function import_section($data){
		$object_name = get_class($data);

		if(($object_name == "WCFE_Checkout_Section") && (THWCFE_Utils_Section::is_valid_section($data))){

			$result = $this->update_section($data);
			if($result){
				$notice = __('Section imported successfully.', 'woocommerce-checkout-field-editor-pro');
				add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
			    });
			    return;
			}else{
				$notice = __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-checkout-field-editor-pro');
				add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
			    });
			    return;				
			}
		}
	}

	private function import_fields($data, $section_name){
		$section = $this->get_checkout_section($section_name);
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$fields = $section->fields;
			foreach ($data as $key => $field){
				if(THWCFE_Utils_Field::is_valid_field($field)){
					if(array_key_exists($key, $fields)){
						$existing_field = $fields[$key];
						if(THWCFE_Utils_Field::is_valid_field($existing_field)){
							$order = $existing_field->get_property('order');
							$field->set_property('order', $order);
						}
					}else{
						$order = count($fields) - 1;
						$field->set_property('order', $order);
					}
					$fields[$key] = $field;
				}
			}
			$section->set_property('fields', $fields);
			$section = THWCFE_Utils_Section::sort_fields($section);

			$result = $this->update_section($section);

			if($result){
				$notice = __('Field(s) imported successfully.', 'woocommerce-checkout-field-editor-pro');
				add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-success"><p>'. $notice. '</p></div>';
			    });
			    return;
			}else{
				$notice = __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-checkout-field-editor-pro');
				add_action('admin_notices', function() use ($notice) {
			        echo '<div class="notice notice-error"><p>'. $notice. '</p></div>';
			    });
			    return;				
			}
		}
	}

	
}

endif;