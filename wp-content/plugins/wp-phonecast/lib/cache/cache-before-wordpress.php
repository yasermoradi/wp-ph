<?php
/**
 * This file must be included in wp-content/advanced-cache.php or any pre wordpress 
 * cache file used by cache plugins.
 * The WP API is not loaded at this point, so we don't have access to plugins functions or options values.
 * So, the option "Cache deactivated" of this plugin won't be taken into account here : you'll have
 * to un-include this file by hand if you want to deactivate this "before wordpress" cache.
 */

require_once(dirname(__FILE__) .'/../../../../GM/gm-constants.php'); //For GM_PLATFORM

/****************************
 * Services that will have "before wordpress" cache.
 * See rewrite rules in /lib/web-services/web_services.php::add_rewrite_tags_and_rules() 
 * and /lib/web-services/wweb_services_types to know how to fill in "identifiers".
 */
$cachable_services = array(
	'/^\/rest\/(.*?)\/synchronization\/?$/' => array(
													'slug' => 'synchronization',
													'identifiers' => array('ews_data'=>'synchronization','ews_action'=>'list'),
													'token' => '$matches[1]',
													'reserved_get_keys' => array() //use this for a specific token get param for example
											   ),
	'/^\/rest\/(.*?)\/post-single\/(\d+)\/?$/' => array(
													'slug' => 'post-single',
													'identifiers' => array('ews_data'=>'post-single','ews_action'=>'one','ews_id'=>'$matches[2]'),
													'token' => '$matches[1]'
											   ),
	'/^\/rest\/(.*?)\/comments\/post\/(\d+)\/?$/' => array(
													'slug' => 'comments',
													'identifiers' => array('ews_data'=>'comments','ews_action'=>'list','ews_subaction'=>'default','ews_subaction_data'=>'$matches[2]'),
													'token' => '$matches[1]'
											   ),
	'/^\/rest\/(.*?)\/posts-list\/(\d+)\/?$/' => array(
													'slug' => 'posts-list',
													'identifiers' => array('ews_data'=>'posts-list','ews_action'=>'one','ews_id'=>'$matches[2]'),
													'token' => '$matches[1]'
											   )
);

/*****************************
* Define a custom "No changes" answer (when using the "last_update" $_GET param).
* Do the same thing here as in the "mlrws_not_changed_answer" hook.
* Can be commented if not needed.
*/
function  mlrws_before_cache_get_not_changed_answer($cached_last_update){
	//Here we want a status=1 instead of native 2 :
	return json_encode((object)array('result' => (object)array('status'=>1,'message'=>''), 'last-update' => $cached_last_update ));
}


/*****************************
* Token functions, equivalent to the "mrlws_generate_token" and "mrlws_check_token" hooks.
* Those functions must be implemented if token is activated, and can be commented if not needed.
* NOTE : tokens generated via native wp nonce functions will not work here, as we don't have the WP API loaded at this point.
*/

//Generate token, in the same way we generate the token via the "mrlws_generate_token" hook : 
function mlrws_before_cache_generate_token($service){
	//Copied from \wp-content\themes\gazette_v2.8\lib\web_services\token.php :
	function gm_ws_generate_token($site, $env) {
		$token_keys = array(
				'preprod' => array(	'gaz' => "(_NJ`U&3}c$[ky.Io`@9 M%Q{'" ),
				'prod' => array( 'gaz' => "GJPf.X!lR +hz#!k3)DrTv[+ra" )
		);
		@$msg = $token_keys[$env][$site];
		return base64_encode(hash('sha256', $msg . date('Y-m-d')));
	}
	return gm_ws_generate_token('gaz',GM_PLATFORM);
}

/*
//Check token, in the same way we check the token via the "mrlws_check_token" hook : 
function mlrws_before_cache_check_token($token,$hooked_token,$service){
	$token_ok = true;
	return $token_ok;
}
*/

function mlrws_before_cache_wrong_token_answer($token,$service){
	return '{"result":{"status":0,"message":"Wrong security token"}}';
}


/************************************************************************************************
*************************************************************************************************
* Nothing should be changed after this
*/

require_once(dirname(__FILE__) .'/web_services_cache.php');

$requested_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$allowed_identifiers = array(
		'ews_data' => '',
		'ews_id' => '',
		'ews_action' => '',
		'ews_subaction' => '',
		'ews_subaction_data' => ''
);

$found_identifiers = array();
$slug = '';
$token = '';
$reserved_keys = array();
$service = array();

foreach($cachable_services as $regexp => $ws_data){
	if( preg_match($regexp,$requested_url,$matches) ){
		
		foreach($ws_data['identifiers'] as $identifier => $value){
			
			if( array_key_exists($identifier,$allowed_identifiers) ){
				if( preg_match('/^\$matches\[(\d+)\]$/',$value,$matches_identifier) ){
					$found_identifiers[$identifier] = $matches[$matches_identifier[1]];
				}else{
					$found_identifiers[$identifier] = $value;
				}
			}
		}
		
		if( !empty($found_identifiers) ){
			
			$service = $ws_data;
			
			$slug = $ws_data['slug'];
			
			if( !empty($ws_data['token']) ){
				if( preg_match('/^\$matches\[(\d+)\]$/',$ws_data['token'],$matches_token) ){
					$token = $matches[$matches_token[1]];
				}else{
					$token = $ws_data['token'];
				}
			}
			
			$reserved_keys = isset($ws_data['reserved_get_keys']) ? $ws_data['reserved_get_keys'] : array();
		
			break;
		}
	}
}

if( !empty($found_identifiers) ){

	//Check token (see MlrwsWebServices::check_token() ):
	$token_ok = true;
	if( !empty($token) ){
		$token_ok = false;
		$hooked_token = function_exists('mlrws_before_cache_generate_token') ? mlrws_before_cache_generate_token($service) : '';
		if( !empty($hooked_token) ){
			if( function_exists('mlrws_before_cache_check_token') ){
				$token_ok = mlrws_before_cache_check_token($token,$hooked_token,$service);
			}else{
				$token_ok = ($token == $hooked_token);
			}
		}else{
			//We don't have the wp_verify_nonce() wordpress API function here...
			$token_ok = false;
		}
	}
	
	if( $token_ok ){
		$cache_id = MlrwsCache::build_web_service_cache_id($slug,$found_identifiers,$_GET,$reserved_keys);
		
		$cached_webservice = MlrwsCache::get_cached_web_service(
				$cache_id,
				isset($_GET['force_reload']) && is_numeric($_GET['force_reload']) && $_GET['force_reload'] == 1,
				isset($_GET['last_update']) && is_numeric($_GET['last_update']) ? $_GET['last_update'] : 0,
				function_exists('mlrws_before_cache_get_not_changed_answer') ? 'mlrws_before_cache_get_not_changed_answer' : null
		);
		
		if( !empty($cached_webservice) ){
			header('Content-type: application/json');
			header('Access-Control-Allow-Origin: *');
			$callback = !empty($_GET['callback']) ? $_GET['callback'] : '';
			if( $callback ){
				echo $callback .'('. $cached_webservice .')';
			}else{
				echo $cached_webservice;
			}
			exit();
		}
	}else{
		header('Content-type: application/json');
		header('Access-Control-Allow-Origin: *');
		if( function_exists('mlrws_before_cache_wrong_token_answer') ){
			echo mlrws_before_cache_wrong_token_answer($token,$service);
		}else{
			echo '{"result":{"status":0,"message":"Wrong security token"}}';
		}
		exit();
	}
}
