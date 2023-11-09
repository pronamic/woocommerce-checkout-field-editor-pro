<?php
/**
 * The Session Handler.
 *
 * @link       https://themehigh.com
 * @since      3.0.6
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Session_Handler')):
 
class THWCFE_Public_Session_Handler {
	const SESSION_EXPIRY = 28800;
	const SKEY_SESSION_ID = 'session_id';
	const SKEY_EXTRA_COST_INFO = 'thwcfe-extra-cost-info';
	const SKEY_LAST_REQT = 'thwcfe-last-request';
	const SKEY_ABORTED_REQTS = 'thwcfe-aborted-requests';
	const COOKIE_EXPIRY = 3600;
	const CKEY_SESSION_ID = 'thwcfe_session_id';

	public function __construct() {		
			
	}

	// Save Price Info in Sesssion
	public function save_extra_cost($price_info) {
		$session_id  = $this->get_session_id();
		$session_obj = $this->get_session($session_id);

		if($this->is_valid_session($session_obj)){
			$session_obj[self::SKEY_EXTRA_COST_INFO] = $price_info;
		}else{
			$session_obj = array(
				self::SKEY_SESSION_ID => $session_id,
				self::SKEY_EXTRA_COST_INFO => $price_info,
			);
		}

		delete_transient($session_id);
		set_transient($session_id, $session_obj, self::SESSION_EXPIRY);
	}
	
	public function get_extra_cost() {
		$extra_cost = $this->get_session_value(self::SKEY_EXTRA_COST_INFO);
		return is_array($extra_cost) ? $extra_cost : array();
	}
	
	public function clear_extra_cost_info() {
		$this->clear_session_value(self::SKEY_EXTRA_COST_INFO);
	}

	public function clear_data() {
		$this->clear_session();
	}

	// Get & Save Last Request in Sesssion
	public function save_last_request($curr_request) {
		if(is_numeric($curr_request)){
			$session_id  = $this->get_session_id();
			$session_obj = $this->get_session($session_id);

			if($this->is_valid_session($session_obj)){
				$session_obj[self::SKEY_LAST_REQT] = $curr_request;

				delete_transient($session_id);
				set_transient($session_id, $session_obj, self::SESSION_EXPIRY);
			}
		}
	}

	public function get_last_request(){
		$last_request = $this->get_session_value(self::SKEY_LAST_REQT);
		return is_numeric($last_request) ? $last_request : '';
	}

	public function clear_last_request() {
		$this->clear_session_value(self::SKEY_LAST_REQT);
	}

	
	// Save Aborted Requests in Sesssion
	public function save_aborted_requests($aborted_request) {
		$session_id  = $this->get_session_id();
		$session_obj = $this->get_session($session_id);

		if($this->is_valid_session($session_obj)){
			$aborted_requests = isset($session_obj[self::SKEY_ABORTED_REQTS]) ? $session_obj[self::SKEY_ABORTED_REQTS] : array();
			$aborted_requests[] = $aborted_request;

			$session_obj[self::SKEY_ABORTED_REQTS] = $aborted_requests;

			delete_transient($session_id);
			set_transient($session_id, $session_obj, self::SESSION_EXPIRY);
		}
	}

	public function get_aborted_requests(){
		$aborted_requests = $this->get_session_value(self::SKEY_ABORTED_REQTS);
		return is_array($aborted_requests) ? $aborted_requests : array();
	}

	public function clear_aborted_requests() {
		$this->clear_session_value(self::SKEY_ABORTED_REQTS);
	}


	// Commomn Sesssion Functions
	private function get_session_value($key){
		$value = '';
		$session_obj = $this->get_session();

		if($this->is_valid_session($session_obj) && isset($session_obj[$key])){
			$value = $session_obj[$key];
		}
		return $value;
	}
	
	private function clear_session_value($key){
		$session_id = $this->get_session_id();
		$session_obj = $this->get_session();

		if($this->is_valid_session($session_obj) && isset($session_obj[$key])){
			unset($session_obj[$key]);

			delete_transient($session_id);
			set_transient($session_id, $session_obj, self::SESSION_EXPIRY);
		}
	}

	private function get_session($session_id = ''){
		if(empty($session_id)){
			$session_id = $this->get_session_id();
		}

		$session_obj = get_transient($session_id);
		return $session_obj;
	}

	private function clear_session() {
		$session_id = $this->get_session_id();
		delete_transient($session_id);
		//$this->clear_cookie();
	}

	private function get_session_id(){
		$session_id = '';

		/*if(WC()->session){
			$session_id = WC()->session->get_customer_id();
			$session_id = 'thwcfe'.$session_id;
		}*/

		if ( isset( $_COOKIE[self::CKEY_SESSION_ID] ) ){
			$session_id = $_COOKIE[self::CKEY_SESSION_ID];
		}else{
			$session_id = $this->set_cookie();
		}
		
		return $session_id;
	}

	public function set_cookie(){
		if ( ! isset( $_COOKIE[self::CKEY_SESSION_ID] ) ){
			$uniqid = uniqid();
			$session_id =  md5( $uniqid);
			$session_id = "thwcfe_" . $session_id;
			setcookie(self::CKEY_SESSION_ID, $session_id, time() + self::COOKIE_EXPIRY, COOKIEPATH, COOKIE_DOMAIN);

			return $session_id;
		}
		return false;
	}

	private function clear_cookie(){
		if ( isset( $_COOKIE[self::CKEY_SESSION_ID] ) ){
			unset($_COOKIE[self::CKEY_SESSION_ID]);
			setcookie(self::CKEY_SESSION_ID, '', time() - self::COOKIE_EXPIRY);
		}
	}

	private function is_valid_session($session_obj){
		$valid = false;
		
		if(is_array($session_obj) && isset($session_obj[self::SKEY_SESSION_ID])){
			$valid = true;
		}
		return $valid;
	}
}

endif;