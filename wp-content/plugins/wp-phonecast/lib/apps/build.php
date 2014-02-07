<?php

class WppcBuild{
	
	public static function hooks(){
		if( is_admin() ){
			add_action('add_meta_boxes', array(__CLASS__,'add_meta_boxes'));
			add_action('save_post', array(__CLASS__,'save_post'));
		}
	}
	
	public static function add_meta_boxes(){
		add_meta_box(
			'wppc_build_box',
			__('Simulation and export'),
			array(__CLASS__,'inner_build_box'),
			'wppc_apps',
			'side',
			'default'
		);
	}
	
	public static function inner_build_box($post,$current_box){
		$debug_mode = self::get_app_debug_mode_raw($post->ID);
		$wp_ws_url = WppcWebServices::get_app_web_service_url($post->ID) .'/synchronization';
		?>
		<label><?php _e('Debug Mode') ?> : </label>
		<select name="wppc_app_debug_mode">
			<option value="on" <?php echo $debug_mode == 'on' ? 'selected="selected"' : '' ?>><?php _e('On') ?></option>
			<option value="off" <?php echo $debug_mode == 'off' ? 'selected="selected"' : '' ?>><?php _e('Off') ?></option>
			<option value="wp" <?php echo $debug_mode == 'wp' ? 'selected="selected"' : '' ?>><?php _e('Same as Wordpress WP_DEBUG') ?></option>
		</select>
		<br/><span class="description"><?php _e('If activated, echoes debug infos in the browser javascript console while simulating the app.') ?></span>
		<br/>
		<br/>
		<a href="<?php echo WppcSimulator::get_simulator_url($post->ID) ?>"><?php _e('View application in simulator') ?></a>
		<br/>
		<br/>
		<a href="<?php echo self::get_appli_dir_url() .'/config.js?wppc_app_id='. $post->ID ?>"><?php _e('View config file') ?></a>
		<br/>
		<br/>
		<label><?php _e('Web services') ?> :</label><br/>
		<?php _e('Synchronization') ?> : <a href="<?php echo $wp_ws_url ?>"><?php echo $wp_ws_url ?></a>
		<?php 
	}
	
	public static function save_post($post_id){
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}
	
		if( empty($_POST['post_type']) || $_POST['post_type'] != 'wppc_apps' ){
			return;
		}
	
		if( !current_user_can('edit_post', $post_id) ){
			return;
		}
	
		if ( isset( $_POST['wppc_app_debug_mode'] ) ) {
			update_post_meta( $post_id, '_wppc_app_debug_mode', $_POST['wppc_app_debug_mode']);
		}

	}
	
	private static function get_app_debug_mode_raw($app_id){
		$debug_mode = get_post_meta($app_id,'_wppc_app_debug_mode',true);
		return empty($debug_mode) ? 'off' : $debug_mode;
	}
	
	public static function get_app_debug_mode($app_id){
		$debug_mode = self::get_app_debug_mode_raw($app_id);
		return $debug_mode == 'wp' ? (WP_DEBUG ? 'on' : 'off') : $debug_mode;
	}
	
	public static function get_appli_dir_url(){
		return plugins_url('appli' , dirname(dirname(__FILE__)) );
	}
}

WppcBuild::hooks();