<?php
class wppc_simulator{

	const menu_item = 'wppc_simulator_bo_settings';

	public static function hooks(){
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			add_action('admin_enqueue_scripts', array(__CLASS__,'admin_enqueue_scripts'));
		}
		
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
			$file = $wp_query->query_vars['wppc_appli_file'];
			switch($file){
				case 'config.js':
					$wp_ws_url = get_bloginfo('wpurl') .'/phonecast-api';
					$theme = 'default';
					
					header("Content-type: text/javascript;  charset=utf-8");
					?>
					define(function (require) {

					    "use strict";
					
					    return {
							wp_ws_url : '<?php echo $wp_ws_url ?>',
							theme : '<?php echo $theme ?>'
						};
					
					});
					<?php
					exit();
			}
		}
		
	}
	
	public static function add_settings_panels(){
		add_submenu_page(WppcComponentsBoSettings::menu_item,__('Simulator'), __('Simulator'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}

	public static function admin_enqueue_scripts(){
	}

	public static function settings_panel(){
		?>
			<div class="wrap">
				<?php screen_icon('generic') ?>
				<h2><?php _e('Navigation') ?></h2>
				
				<div style="background-image:url('<?php echo plugins_url('images/iphone5.png' , dirname(dirname(__FILE__)) ) ?>');background-repeat:no-repeat;margin: 0px 0px 0px 0px;padding: 145px 0px 0px 27px;width:375px;height:690px;">
					<iframe src="<?php echo plugins_url('appli' , dirname(dirname(__FILE__)) ) ?>" width="320" height="550"></iframe>
				</div>
			</div>
		<?php
	}
	
}

wppc_simulator::hooks();