<?php
class WppcConfigFile{
	
	public static function hooks(){
		add_action('init',array(__CLASS__,'rewrite_rules'));
		add_action('template_redirect',array(__CLASS__,'template_redirect'));
	}
	
	public static function rewrite_rules(){
		add_rewrite_tag('%wppc_appli_file%','([^&]+)');
		add_rewrite_rule('^wp-content/plugins/wp-phonecast/appli/(config\.js)$', 'index.php?wppc_appli_file=$matches[1]', 'top');
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
							$wp_ws_url = WppcWebServices::get_app_web_service_url($app_id);
							$theme = WppcThemesStorage::get_current_theme($app_id);
							
							$app_main_infos = WppcApps::get_app_main_infos($app_id);
							$app_title = $app_main_infos['name'];
		
							header("Content-type: text/javascript;  charset=utf-8");
?>
/* Wp PhoneCast simulator config.js */
define(function (require) {

    "use strict";

    return {
		wp_ws_url : '<?php echo $wp_ws_url ?>',
		theme : '<?php echo addslashes($theme) ?>',
		app_title : "<?php echo addslashes($app_title) ?>"
	};

});
<?php
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
	
}

WppcConfigFile::hooks();