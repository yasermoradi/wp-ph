<?php
class WppcThemesBoSettings{
	
	public static function hooks(){
		add_action('add_meta_boxes', array(__CLASS__,'add_meta_boxes'));
		add_action('save_post', array(__CLASS__,'save_post'));
	}
	
	public static function add_meta_boxes(){
		add_meta_box(
			'wppc_app_theme',
			__('Theme'),
			array(__CLASS__,'inner_main_infos_box'),
			'wppc_apps',
			'normal',
			'default'
		);
	}
	
	public static function inner_main_infos_box($post,$current_box){
		$available_themes = WppcThemes::get_available_themes();
		$current_theme = WppcThemesStorage::get_current_theme($post->ID);
		?>
		
		<label><?php _e('Choose theme') ?> : </label>
		<select name="wppc_app_theme_choice">
			<?php foreach($available_themes as $theme): ?>
				<?php $selected = $theme == $current_theme ? 'selected="selected"' : '' ?>
				<option value="<?php echo $theme ?>" <?php echo $selected ?>><?php echo ucfirst($theme)?> </option>
			<?php endforeach ?>
		</select>
		
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
	
		if ( isset( $_POST['wppc_app_theme_choice'] ) ) {
			WppcThemesStorage::set_current_theme($post_id,$_POST['wppc_app_theme_choice']);
		}
		
	}
}

WppcThemesBoSettings::hooks();