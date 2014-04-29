<?php
class WpakToken{
	
	public static function get_token($app_id,$service_slug){
		$hooked_token = apply_filters('wpak_generate_token','',$service_slug,$app_id);
		$token = !empty($hooked_token) ? $hooked_token : self::generate_token($app_id);
		return $token;
	}
	
	public static function check_token($token,$app_id,$service_slug){
		$right_token = self::get_token($app_id,$service_slug);
		
		$token_ok = apply_filters('wpak_check_token',null,$token,$right_token,$app_id,$service_slug);
		if( $token_ok === null ){
			$token_ok = ($token == $right_token);
		}
		
		return $token_ok;
	}
	
	private static function generate_token($salt) {
		$hash_key = self::get_hash_key() . $salt . date('Y-m-d');
		return base64_encode(hash('sha256', $hash_key));
	}
	
	public static function get_hash_key(){
		$hash_key = '';
		//WPPH_AUTH_KEY can be defined in wp-config.php
		if( defined('WPPH_AUTH_KEY') ){
			$hash_key = WPPH_AUTH_KEY;
		}else{
			$hash_key = AUTH_KEY;
		}
		return $hash_key;
	}
	
}