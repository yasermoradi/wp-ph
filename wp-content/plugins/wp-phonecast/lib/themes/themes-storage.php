<?php
class WppcThemesStorage{
	
	const option_id = 'wppc-themes';
	
	public static function get_current_theme_options(){
		return self::get_theme_options(self::get_current_theme());
	}
	
	public static function get_current_theme(){
		$themes = self::get_themes_raw();
		return $themes['current_theme'];
	}
	
	public static function get_theme_options($theme_slug){
		$themes = self::get_themes_raw();
		return  isset($themes['themes'][$theme_slug]) ? $themes['themes'][$theme_slug] : false;
	}
	
	public static function set_current_theme($theme_slug){
		$themes = self::get_themes_raw();
		$themes['current_theme'] = $theme_slug;
		self::update_themes($themes);
	}
	
	public static function set_theme_options($theme_slug,$options){
		$themes = self::get_themes_raw();
		@$themes['themes'][$theme_slug]['options'] = $options;
		self::update_themes($themes);
	}
	
	private static function update_themes($new_themes){
		$current_themes = self::get_themes_raw();
		if ( $current_themes !== false ) {
			update_option( self::option_id, $new_themes );
		} else {
			add_option( self::option_id, $new_themes, '', 'no' );
		}
	}
	
	private static function get_themes_raw(){
		$themes = get_option( self::option_id );
		if( !isset($themes['current_theme'] )){
			$themes['current_theme'] = 'default';
		}
		return $themes;
	}
}