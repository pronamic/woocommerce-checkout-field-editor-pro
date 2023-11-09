<?php
/**
 * The extra price specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils_Price')):

class THWCFE_Utils_Price {
	public static function get_price_html_option($key, $option){
		$price_html = '';
		$price_type = isset($option['price_type']) ? $option['price_type'] : false;
		$price = isset($option['price']) ? $option['price'] : false;
		
		if(is_numeric($price) && $price != 0){
			$price_html = self::get_price_html($key, $price_type, $price);
			if($price_html){
				$price_prefix = apply_filters('thwcfe_option_price_display_prefix', ' (', $key, $price, $price_type);
				$price_suffix = apply_filters('thwcfe_option_price_display_suffix', ')', $key, $price, $price_type);
				
				$price_html = $price_prefix.$price_html.$price_suffix;
			}
			
			$price_html = apply_filters('thwcfe_option_price_html', $price_html, $key, $price_type, $price);
		}
		
		return $price_html;
	}

	private static function get_price_html($key, $price_type, $price, $field = false){
		$html = '';
		if($price_type != 'custom' && is_numeric($price)){
			if($price_type === 'percentage' || $price_type === 'percentage_subtotal' || $price_type === 'percentage_subtotal_ex_tax'){
				$html = $price.apply_filters('thwcfe_field_price_percentage_symbol', '%', $key);
			}else if($price_type === 'dynamic'){
				if($field){
					$name = $field->get_property('name');
					$price_html = self::display_price($price, $field);
					$price_unit = $field->get_property('price_unit');
					$price_unit_label = apply_filters('thwcfe_field_cost_unit_label_'.$name, '/'.$price_unit.' unit', $price_unit);
					$html = $price_html.$price_unit_label;
				}
			}else{
				$html = self::display_price($price, $field);
			}
		}
		return $html;
	}

	public static function display_price($price, $field, $args = array(), $plain = true){
		extract( apply_filters( 'wc_price_args', wp_parse_args( $args, array(
			'currency'           => '',
			'decimal_separator'  => wc_get_price_decimal_separator(),
			'thousand_separator' => wc_get_price_thousand_separator(),
			'decimals'           => wc_get_price_decimals(),
			'price_format'       => get_woocommerce_price_format(),
		) ) ) );
	
		$unformatted_price = $price;
		$negative = $price < 0;
		$price = apply_filters('raw_woocommerce_price', floatval($negative ? $price * -1 : $price));
		$price = apply_filters('formatted_woocommerce_price', number_format($price, $decimals, $decimal_separator, $thousand_separator), $price, $decimals, $decimal_separator, $thousand_separator);
	
		if(apply_filters('woocommerce_price_trim_zeros', false) && $decimals > 0){
			$price = wc_trim_zeros($price);
		}
		
		$price_sign = $negative ? '-' : ($price > 0 ? '+' : '');
		$price_sign = apply_filters('thwcfe_field_display_price_sign', $price_sign, $unformatted_price, $field);
		
		$return = '';
		if($plain){
			$return = self::display_price_plain($price_sign, $price_format, $currency, $price, $unformatted_price, $field);
		}else{
			$return = self::display_price_formatted($price_sign, $price_format, $currency, $price, $unformatted_price, $field);
		}
		
		return apply_filters('thwcfe_field_display_price', $return, $price, $unformatted_price, $field);
	}
	
	private static function display_price_formatted($price_sign, $price_format, $currency, $price, $unformatted_price, $field){
		$formatted_price = $price_sign . sprintf($price_format, '<span class="thwcfe-currency-symbol">'.get_woocommerce_currency_symbol($currency).'</span>', $price);
		$return = '<span class="thwcfe-price-amount">'. $formatted_price .'</span>';
		return apply_filters('thwcfe_field_display_price_formatted', $return, $price, $unformatted_price, $field);
	}
	
	private static function display_price_plain($price_sign, $price_format, $currency, $price, $unformatted_price, $field){
		$return = $price_sign . sprintf($price_format, get_woocommerce_currency_symbol($currency), $price);
		return apply_filters('thwcfe_field_display_price_plain', $return, $price, $unformatted_price, $field);
	}
}

endif;