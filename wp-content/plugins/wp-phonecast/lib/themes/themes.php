<?php
require_once(dirname(__FILE__) .'/themes-storage.php');
require_once(dirname(__FILE__) .'/themes-bo-settings.php');

class WppcThemes{
	
	const appli_theme_directory = '../../appli/themes';
	
	public static function get_available_themes(){
		$available_themes = array();
		
		$directory = dirname(__FILE__) .'/'. self::appli_theme_directory;
		
		if( file_exists($directory) && is_dir($directory) ){
			if( $handle = opendir($directory) ){
				while( false !== ($entry = readdir($handle)) ){
					if( $entry != '.' && $entry != '..'){
						$entry_full_path = $directory .'/'. $entry;
						if( is_dir($entry_full_path) ){
							$available_themes[] = $entry;
						}
					}
				}
				closedir($handle);
			}
		}
		
		return $available_themes;
	}
	
}