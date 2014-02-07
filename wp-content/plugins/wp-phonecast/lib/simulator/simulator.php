<?php

require_once(dirname(__FILE__) .'/config-file.php');

class WppcSimulator{

	const menu_item = 'wppc_simulator_bo_settings';

	public static function hooks(){
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			add_filter('post_row_actions',array(__CLASS__,'add_action_link'),10,2);
		}
	}
	
	public static function add_settings_panels(){
		add_submenu_page(WppcApps::menu_item,__('Simulator'), __('Simulator'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}

	public static function add_action_link($actions){
		global $post;
		if( $post->post_type == 'wppc_apps' ) {
			if( array_key_exists('trash',$actions) ){
				$trash_mem = $actions['trash'];
				unset($actions['trash']);
				$actions['wppc-simulate-app'] = '<a href="'. self::get_simulator_url($post->ID) .'">'. __('View in simulator') .'</a>';
				$actions['trash'] = $trash_mem;
			}
		}
		return $actions;
	}

	public static function settings_panel(){

		$apps = WppcApps::get_apps();
		reset($apps);
		
		?>
		
		<div class="wrap">
				<?php screen_icon('generic') ?>
				<h2><?php _e('Simulator') ?></h2>
				
				<?php 
					$app_id = !empty($_GET['wppc_app_id']) ? esc_attr($_GET['wppc_app_id']) : key($apps);
					//TODO : default if empty
				?>
				
				<div id="app-choice">
					<form method="get" action="<?php echo remove_query_arg(array('wppc_app_id','page'),self::get_simulator_url($app_id)) ?>">
						<label><?php _e('Application') ?> : </label>
						<select name="wppc_app_id">
							<?php foreach($apps as $_app_id => $app): ?>
								<?php $selected = $app->post_name == $app_id || $_app_id == $app_id ? 'selected="selected"' : '' ?>
								<option value="<?php echo $app->post_name ?>" <?php echo $selected ?>><?php echo $app->post_title ?></option>
							<?php endforeach ?>
						</select>
						<input type="hidden" name="page" value="<?php echo self::menu_item ?>" />
						<input type="submit" value="<?php _e('OK') ?>"/>
					</form>
				</div>
				
				<?php if( WppcApps::app_exists($app_id) ): ?>
				
					<?php 
						$appli_dir_url = WppcBuild::get_appli_dir_url();
						$appli_url = $appli_dir_url .'/index.html?wppc_app_id='. $app_id;
						$wp_ws_url = WppcWebServices::get_app_web_service_url($app_id) .'/synchronization';
					?>
						
					<div id="simulator">
						<iframe src="<?php echo $appli_url ?>" width="320" height="550"></iframe>
					</div>
					
					<div id="debug-infos">
						<h3><a href="<?php echo $appli_url ?>"><?php _e('Preview in browser') ?></a></h3>
						
						<h3><a href="<?php echo $appli_dir_url .'/config.js?wppc_app_id='. $app_id ?>"><?php _e('View config file') ?></a></h3>
						
						<br/><br/>
						<h3><?php _e('Web services') ?> :</h3>
						<?php _e('Synchronization') ?> : <a href="<?php echo $wp_ws_url ?>"><?php echo $wp_ws_url ?></a>
					</div>
					
					<style>
						#simulator{ float:left; background-image:url('<?php echo plugins_url('images/iphone5.png' , dirname(dirname(__FILE__)) ) ?>');background-repeat:no-repeat;margin: 0px 0px 0px 0px;padding: 145px 0px 0px 27px;width:375px;height:690px; }
						#debug-infos{ float:left; }
					</style>
					
				<?php else: ?>
				
					<?php echo sprintf(__('App "%s" not found'),$app_id) ?>
					
				<?php endif ?>
				
			</div>
			
			<style>
				#app-choice{ margin:5px }
			</style>
		<?php
	}
	
	public static function get_simulator_url($app_id_or_slug){
		$app_post_id = WppcApps::get_app_id($app_id_or_slug);
		$app = get_post($app_post_id);
		return !empty($app) ? admin_url('admin.php?page=wppc_simulator_bo_settings&wppc_app_id='. $app->post_name) : '';
	}
	
}

WppcSimulator::hooks();