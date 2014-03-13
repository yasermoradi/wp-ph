<?php
class WppcConfigFile{
	
	public static function hooks(){
		add_action('init',array(__CLASS__,'rewrite_rules'));
		add_action('template_redirect',array(__CLASS__,'template_redirect'));
	}
	
	public static function rewrite_rules(){
		add_rewrite_tag('%wppc_appli_file%','([^&]+)');
		add_rewrite_rule('^wp-content/plugins/wp-phonecast/appli/(config\.js)$', 'index.php?wppc_appli_file=$matches[1]', 'top');
		add_rewrite_rule('^wp-content/plugins/wp-phonecast/appli/(config\.xml)$', 'index.php?wppc_appli_file=$matches[1]', 'top');
	}
	
	public static function template_redirect(){
		global $wp_query;
	
		if( isset($wp_query->query_vars['wppc_appli_file']) && !empty($wp_query->query_vars['wppc_appli_file']) ){
				
			if( !empty($_GET['wppc_app_id']) ){
	
				$app_id = esc_attr($_GET['wppc_app_id']); //can be ID or slug
	
				$app = WppcApps::get_app($app_id);
				
				if( !empty($app) ){
					$app_id = $app->ID;
					
					$file = $wp_query->query_vars['wppc_appli_file'];
					switch($file){
						case 'config.js':
							header("Content-type: text/javascript;  charset=utf-8");
							echo "/* Wp PhoneCast simulator config.js */\n";
							self::get_config_js($app_id,true);
							exit();
						case 'config.xml':
							header("Content-type: text/xml;  charset=utf-8");
							self::get_config_xml($app_id,true);
							exit();
						default:
							exit();
					}
				}else{
					echo __('App not found') .' : ['. $app_id .']';
					exit();
				}
				
			}else{
				_e('App id not found in _GET parmeters');
				exit();
			}
		}
		
	}
	
	public static function get_config_js($app_id,$echo=false){
		$wp_ws_url = WppcWebServices::get_app_web_service_base_url($app_id);
		$theme = WppcThemesStorage::get_current_theme($app_id);
			
		$app_slug = WppcApps::get_app_slug($app_id);
		
		$app_main_infos = WppcApps::get_app_main_infos($app_id);
		$app_title = $app_main_infos['title'];
			
		$debug_mode = WppcBuild::get_app_debug_mode($app_id);

		$auth_key = WppcApps::get_app_is_secured($app_id) ? WppcToken::get_hash_key() : '';
		//TODO : options to choose if the auth key is displayed in config.js.
		
		if( !$echo ){
			ob_start();
		}
?>
define(function (require) {

	"use strict";

	return {
		app_slug : '<?php echo $app_slug ?>',
		wp_ws_url : '<?php echo $wp_ws_url ?>',
		theme : '<?php echo addslashes($theme) ?>',
		app_title : '<?php echo addslashes($app_title) ?>',
		debug_mode : '<?php echo $debug_mode ?>'<?php 
			if( !empty($auth_key) ):
		?>,
		auth_key : '<?php echo $auth_key ?>'<?php
			endif 
		?>
		
	};

});
<?php
		$content = '';
		if( !$echo ){
			$content = ob_get_contents();
			ob_end_clean();
		}
		
		return !$echo ? $content : '';
	}
	
	public static function get_config_xml($app_id,$echo=false){
		$app_main_infos = WppcApps::get_app_main_infos($app_id);
		
		$app_name = $app_main_infos['name'];
		$app_description = $app_main_infos['desc'];
		$app_phonegap_id = $app_main_infos['app_phonegap_id'];
		$app_version = $app_main_infos['version'];
		$app_version_code = $app_main_infos['version_code'];
		$app_author = $app_main_infos['author'];
		$app_author_email = $app_main_infos['author_email'];
		$app_author_website = $app_main_infos['author_website'];
		$app_platform = $app_main_infos['platform'];
		
		$xmlns = 'http://www.w3.org/ns/widgets';
		$xmlns_gap = 'http://phonegap.com/ns/1.0';
		
		if( !$echo ){
			ob_start();
		}
		
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>

<widget xmlns       = "<?php echo $xmlns ?>"
        xmlns:gap   = "<?php echo $xmlns_gap ?>"
        id          = "<?php echo $app_phonegap_id ?>"
        versionCode = "<?php echo $app_version_code ?>"
        version     = "<?php echo $app_version ?>" >

	<name><?php echo $app_name ?></name>

	<description><?php echo $app_description ?></description>
	
	<author href="<?php echo $app_author_website ?>" email="<?php echo $app_author_email ?>"><?php echo $app_author ?></author>

	<gap:platform name="<?php echo $app_platform ?>" />
		
	<!-- Add Icon and/or Splash screen here -->
	
</widget>
<?php
		$content = '';
		if( !$echo ){
			$content = ob_get_contents();
			ob_end_clean();
		}
		
		return !$echo ? $content : '';
	}
	
}

WppcConfigFile::hooks();