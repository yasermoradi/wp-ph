<?php

class WppcApps{
	
	const menu_item = 'wppc_main_bo_settings';
	
	public static function hooks(){
		add_action('init', array(__CLASS__,'apps_custom_post_type'));
		if( is_admin() ){
			add_action('admin_menu',array(__CLASS__,'add_settings_panels'));
			add_action('add_meta_boxes', array(__CLASS__,'add_main_meta_box'),10);
			add_action('add_meta_boxes', array(__CLASS__,'add_phonegap_meta_box'),30); //30 to pass after the "Simulation" and "Export" boxes (see WppcBuild)
			add_action('save_post', array(__CLASS__,'save_post'));
			add_filter('post_row_actions',array(__CLASS__,'remove_quick_edit'),10,2);
			add_action('admin_head',array(__CLASS__,'add_icon'));
		}
	}
	
	public static function apps_custom_post_type() {
	
		register_post_type(
			'wppc_apps', array(
			'label' => __('Applications'),
			'description' => '',
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => self::menu_item,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_in_nav_menus' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'has_archive' => false,
			'supports' => array('title'),
			'labels' => array (
					'name' => __('Applications'),
					'singular_name' => __('Application'),
					'menu_name' => __('Applications'),
					'add_new' => __('Add'),
					'add_new_item' => __('Add an application'),
					'edit' => __('Edit'),
					'edit_item' => __('Edit application'),
					'new_item' => __('New application'),
					'not_found' => __('No application found'),
				)
			)
		);
	}
	
	public static function add_icon(){
		global $pagenow, $typenow;
		
		//TODO : use an external CSS instead of writing style directly in <head>... 
		
		if( $typenow == 'wppc_apps' && in_array($pagenow,array('edit.php','post-new.php','post.php')) ){
			?>
			<style>
				#icon-wppc_main_bo_settings{
					background-image: url(<?php echo admin_url() ?>/images/icons32.png);
					background-position: -552px -5px;
				}
			</style>
			<?php 
		}
	}
	
	public static function add_settings_panels(){
		add_menu_page(__('WP PhoneCast'), __('WP PhoneCast'), 'manage_options', self::menu_item, array(__CLASS__,'settings_panel'));
	}
	
	public static function add_main_meta_box(){
		
		add_meta_box(
			'wppc_app_main_infos',
			__('Main infos'),
			array(__CLASS__,'inner_main_infos_box'),
			'wppc_apps',
			'normal',
			'default'
		);
		
	}
	
	public static function add_phonegap_meta_box(){

		add_meta_box(
			'wppc_app_phonegap_data',
			__('Phonegap config.xml infos'),
			array(__CLASS__,'inner_phonegap_infos_box'),
			'wppc_apps',
			'side',
			'default'
		);
		
		add_meta_box(
			'wppc_app_security',
			__('Security'),
			array(__CLASS__,'inner_security_box'),
			'wppc_apps',
			'side',
			'default'
		);
		
	}
	
	public static function inner_main_infos_box($post,$current_box){
		$main_infos = self::get_app_main_infos($post->ID);
		?>
		<div class="wppc_settings">
			<label><?php _e('Application title (displayed in app top bar)') ?></label> : <br/> 
			<input type="text" name="wppc_app_title" value="<?php echo $main_infos['title'] ?>" />
			<br/><br/>
			<label><?php _e('Platform') ?></label> : <br/>
			<select name="wppc_app_platform">
				<?php foreach(self::get_platforms() as $value => $label): ?>
					<?php $selected = $value == $main_infos['platform'] ? 'selected="selected"' : '' ?>
					<option value="<?php echo $value ?>" <?php echo $selected ?>><?php echo $label ?></option>
				<?php endforeach ?>
			</select>
		</div>
		<style>
			.wppc_settings input[type=text]{ width:100% }
			.wppc_settings textarea{ width:100%;height:5em }
		</style>
		<?php
	}
	
	public static function inner_phonegap_infos_box($post,$current_box){
		$main_infos = self::get_app_main_infos($post->ID);
		?>
		<div class="wppc_settings">
			<span class="description"><?php _e('PhoneGap config.xml informations that are going to be displayed on App Stores.<br/> 
			They are required when exporting the App to Phonegap, but are not used for App debug and simulation in browsers.') ?></span>
			<br/><br/>
			<label><?php _e('Application name') ?></label> : <br/> 
			<input type="text" name="wppc_app_name" value="<?php echo $main_infos['name'] ?>" />
			<br/><br/>
			<label><?php _e('Application description') ?></label> : <br/>
			<textarea name="wppc_app_desc"><?php echo $main_infos['desc'] ?></textarea>
			<br/><br/>
			<label><?php _e('Application id') ?></label> : <br/> 
			<input type="text" name="wppc_app_phonegap_id" value="<?php echo $main_infos['app_phonegap_id'] ?>" />
			<br/><br/>
			<label><?php _e('Version') ?></label> : <br/>
			<input type="text" name="wppc_app_version" value="<?php echo $main_infos['version'] ?>" />
			<br/><br/>
			<label><?php _e('Application versionCode (Android only)') ?></label> : <br/> 
			<input type="text" name="wppc_app_version_code" value="<?php echo $main_infos['version_code'] ?>" />
			<br/><br/>
			<label><?php _e('Phonegap version') ?></label> : <br/> 
			<input type="text" name="wppc_app_phonegap_version" value="<?php echo $main_infos['phonegap_version'] ?>" />
			<br/><br/>
			<label><?php _e('Application author') ?></label> : <br/> 
			<input type="text" name="wppc_app_author" value="<?php echo $main_infos['author'] ?>" />
			<br/><br/>
			<label><?php _e('Application author website') ?></label> : <br/> 
			<input type="text" name="wppc_app_author_website" value="<?php echo $main_infos['author_website'] ?>" />
			<br/><br/>
			<label><?php _e('Application author email') ?></label> : <br/> 
			<input type="text" name="wppc_app_author_email" value="<?php echo $main_infos['author_email'] ?>" />
			<br/><br/>
			<a href="<?php echo WppcBuild::get_appli_dir_url() .'/config.xml?wppc_app_id='. self::get_app_slug($post->ID) ?>"><?php _e('View config.xml') ?></a>
		</div>
		<?php
	}
	
	public static function inner_security_box($post,$current_box){
		$secured = self::get_app_is_secured($post->ID);
		?>
		<label><?php _e('Secured web services') ?></label> : <br/>
		<select name="wppc_app_secured">
			<option value="1" <?php echo $secured ? 'selected="selected"' : '' ?>><?php _e('Yes') ?></option>
			<option value="0" <?php echo !$secured ? 'selected="selected"' : '' ?>><?php _e('No') ?></option>
		</select>
		<?php
	}
		
	public static function remove_quick_edit($actions){
		global $post;
		if( $post->post_type == 'wppc_apps' ) {
			unset($actions['inline hide-if-no-js']);
		}
		return $actions;
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
	
		if ( isset( $_POST['wppc_app_title'] ) ) {
			update_post_meta( $post_id, '_wppc_app_title', sanitize_text_field( $_POST['wppc_app_title'] ) );
		}
		
		if ( isset( $_POST['wppc_app_name'] ) ) {
	        update_post_meta( $post_id, '_wppc_app_name', sanitize_text_field( $_POST['wppc_app_name'] ) );
	    }
	    
		if ( isset( $_POST['wppc_app_phonegap_id'] ) ) {
	        update_post_meta( $post_id, '_wppc_app_phonegap_id', sanitize_text_field( $_POST['wppc_app_phonegap_id'] ) );
	    }
		
	    if ( isset( $_POST['wppc_app_desc'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_desc', sanitize_text_field( $_POST['wppc_app_desc'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_version'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_version', sanitize_text_field( $_POST['wppc_app_version'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_version_code'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_version_code', sanitize_text_field( $_POST['wppc_app_version_code'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_phonegap_version'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_phonegap_version', sanitize_text_field( $_POST['wppc_app_phonegap_version'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_platform'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_platform', sanitize_text_field( $_POST['wppc_app_platform'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_author'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_author', sanitize_text_field( $_POST['wppc_app_author'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_author_website'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_author_website', sanitize_text_field( $_POST['wppc_app_author_website'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_author_email'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_author_email', sanitize_text_field( $_POST['wppc_app_author_email'] ) );
	    }
	    
	    if ( isset( $_POST['wppc_app_secured'] ) ) {
	    	update_post_meta( $post_id, '_wppc_app_secured', sanitize_text_field( $_POST['wppc_app_secured'] ) );
	    }
	     
	    
	}
	
	private static function get_platforms(){
		return array(
			'ios' => __('iOS'),
			'android' => __('Android')
		);
	}
	
	public static function get_apps(){
		$args = array(
				'post_type' => 'wppc_apps',
				'post_status' => 'publish',
				'numberposts' => -1
		);
		
		$apps_raw = get_posts($args);
		
		$apps = array();
		foreach($apps_raw as $app){
			$apps[$app->ID] = $app;
		}
		
		return $apps;
	}
	
	public static function get_app($app_id_or_slug,$no_meta=false){
		$app = null;
		
		$app_id = self::get_app_id($app_id_or_slug);
		
		if( !empty($app_id) ){
			$app = get_post($app_id);
			if( !$no_meta ){
				if( !empty($app) ){
					$app->main_infos = self::get_app_main_infos($app_id);
					$app->components = WppcComponents::get_app_components($app_id);
					$app->navigation = WppcNavigation::get_app_navigation($app_id);
				}else{
					$app = null;
				}
			}
		}
		
		return $app;
	}
	
	public static function app_exists($app_id_or_slug){
		return self::get_app_id($app_id_or_slug) != 0;
	}
	
	public static function get_app_id($app_id_or_slug){

		if( is_numeric($app_id_or_slug) ){
			return intval($app_id_or_slug);
		}

		$args = array(
				'name' => $app_id_or_slug,
				'post_type' => 'wppc_apps',
				'post_status' => 'publish',
				'numberposts' => 1
		);

		$apps = get_posts($args);

		return !empty($apps) ? $apps[0]->ID : 0;
	}	
	
	public static function get_app_slug($app_id_or_slug){
		$app = self::get_app($app_id_or_slug,true);
		return !empty($app) ? $app->post_name : '';
	}
	
	public static function get_app_main_infos($post_id){
		$title = get_post_meta($post_id,'_wppc_app_title',true);
		$app_phonegap_id = get_post_meta($post_id,'_wppc_app_phonegap_id',true);
		$name = get_post_meta($post_id,'_wppc_app_name',true);
		$desc = get_post_meta($post_id,'_wppc_app_desc',true);
		$version = get_post_meta($post_id,'_wppc_app_version',true);
		$version_code = get_post_meta($post_id,'_wppc_app_version_code',true);
		$phonegap_version = get_post_meta($post_id,'_wppc_app_phonegap_version',true);
		$platform = get_post_meta($post_id,'_wppc_app_platform',true);
		$author = get_post_meta($post_id,'_wppc_app_author',true);
		$author_website = get_post_meta($post_id,'_wppc_app_author_website',true);
		$author_email = get_post_meta($post_id,'_wppc_app_author_email',true);
		return array('title'=>$title,
					 'name'=>$name,
					 'app_phonegap_id'=>$app_phonegap_id,
					 'desc'=>$desc,
					 'version'=>$version,
					 'version_code'=>$version_code,
					 'phonegap_version'=>$phonegap_version,
					 'platform'=>$platform,
					 'author'=>$author,
					 'author_website'=>$author_website,
					 'author_email'=>$author_email
					 );
	}
	
	public static function get_app_is_secured($post_id){
		$secured_raw = get_post_meta($post_id,'_wppc_app_secured',true);
		$secured_raw = $secured_raw === '' || $secured_raw === false ? 1 : $secured_raw;
		return intval($secured_raw) == 1;
	}
	
}

WppcApps::hooks();