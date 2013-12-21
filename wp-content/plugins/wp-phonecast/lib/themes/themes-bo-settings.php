<?php
class WppcThemesBoSettings{
	
	const menu_item = 'wppc_themes_bo_settings';
	
	public static function hooks(){
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			//add_action('admin_enqueue_scripts', array(__CLASS__,'admin_enqueue_scripts'));
		}
	}
	
	public static function add_settings_panels(){
		add_submenu_page(WppcComponentsBoSettings::menu_item,__('App Theme'), __('App Theme'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}
	
	public static function admin_enqueue_scripts(){
		global $pagenow, $typenow, $plugin_page;
		if( $pagenow == 'admin.php' && $plugin_page == self::menu_item ){
			//wp_enqueue_script('wppc_theme_bo_settings_js',plugins_url('lib/themes/themes-bo-settings.js', dirname(dirname(__FILE__))),array('jquery'),WpPhoneCast::resources_version);
		}
	}
	
	public static function settings_panel(){
		$available_themes = WppcThemes::get_available_themes();
		$current_theme = WppcThemesStorage::get_current_theme();
		self::handle_posted_data();
		?>
		<div class="wrap">
				<?php screen_icon('generic') ?>
				<h2><?php _e('App Theme') ?></h2>
				
				<?php settings_errors('wppc_themes_settings') ?>
				
				<form action="<?php echo add_query_arg(array()) ?>" method="post" id="theme-form">
					<label><?php _e('Choose theme') ?> : </label>
					<select name="theme-choice">
						<?php foreach($available_themes as $theme): ?>
							<?php $selected = $theme == $current_theme ? 'selected="selected"' : '' ?>
							<option value="<?php echo $theme ?>" <?php echo $selected ?>><?php echo ucfirst($theme)?> </option>
						<?php endforeach ?>
					</select>
					<input type="hidden" name="theme-form-submitted" value="1" />
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save theme') ?>">
				</form>
		</div>
		
		<style type="text/css">
			#theme-form{ display:block; margin:1em 0; }
		</style>
		<?php 
	}
	
	private static function handle_posted_data(){
		
		//TODO Add nonce!
		
		if( isset($_POST['theme-form-submitted']) && $_POST['theme-form-submitted'] == 1 ){
			$new_theme = $_POST['theme-choice'];
			$allowed_themes = WppcThemes::get_available_themes();
			if( in_array($new_theme,$allowed_themes) ){
				WppcThemesStorage::set_current_theme($new_theme);
				add_settings_error('wppc_themes_settings','edited',sprintf(__('Theme "%s" activated'),ucfirst($new_theme)),'updated');
			}
		}

	}	
}

WppcThemesBoSettings::hooks();