<?php

class WppcBuild{
	
	const export_file_memory = 10;
	
	public static function hooks(){
		if( is_admin() ){
			add_action('wp_ajax_wppc_build_app_sources', array(__CLASS__,'build_app_sources') );
			add_action('admin_action_wppc_download_app_sources', array(__CLASS__,'download_app_sources') );
			add_action('add_meta_boxes', array(__CLASS__,'add_meta_boxes'),20);
			add_action('save_post', array(__CLASS__,'save_post'));
		}
	}
	
	public static function add_meta_boxes(){
		add_meta_box(
			'wppc_simulation_box',
			__('App Simulation'),
			array(__CLASS__,'inner_simulation_box'),
			'wppc_apps',
			'side',
			'default'
		);
		
		add_meta_box(
			'wppc_export_box',
			__('Phonegap ready App export'),
			array(__CLASS__,'inner_export_box'),
			'wppc_apps',
			'side',
			'default'
		);
	}
	
	public static function inner_simulation_box($post,$current_box){
		$debug_mode = self::get_app_debug_mode_raw($post->ID);
		$wp_ws_url = WppcWebServices::get_app_web_service_url($post->ID) .'/synchronization';
		$appli_url = self::get_appli_index_url($post->ID);
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
		<a href="<?php echo WppcSimulator::get_simulator_url($post->ID) ?>" class="button button-large"><?php _e('View application in simulator') ?></a>
		<br/>
		<br/>
		<a href="<?php echo $appli_url ?>" class="button button-large"><?php _e('View application in browser') ?></a>
		<br/>
		<br/>
		<a href="<?php echo self::get_appli_dir_url() .'/config.js?wppc_app_id='. WppcApps::get_app_slug($post->ID) ?>"><?php _e('View config.js') ?></a>
		<br/>
		<br/>
		<label><?php _e('Web services') ?> :</label><br/>
		<?php _e('Synchronization') ?> : <a href="<?php echo $wp_ws_url ?>"><?php echo $wp_ws_url ?></a>
		<?php 
	}
	
	public static function inner_export_box($post,$current_box){
		$app_id = $post->ID;
		$available_themes = WppcThemes::get_available_themes();
		$current_theme = WppcThemesStorage::get_current_theme($app_id);
		?>
		<span class="description wppc_export_infos"><?php _e('Phonegap exports are Zip files created in the WordPress uploads directory') ?> : <br/><strong><?php echo str_replace(ABSPATH,'',self::get_export_files_path()) ?></strong></span>
		<br/><span class="description"><?php echo sprintf(__("The %s last App exports are memorized in this directory."),self::export_file_memory) ?></span>
		<br/><br/>
		<label><?php _e('Themes to include in app export')?> : </label><br/>
		<select id="wppc_export_theme" multiple>
			<?php foreach($available_themes as $theme): ?>
				<?php $selected = $theme == $current_theme ? 'selected="selected"' : '' ?>
				<option value="<?php echo $theme ?>" <?php echo $selected ?>><?php echo ucfirst($theme)?> </option>
			<?php endforeach ?>
		</select>
		<label for="wppc_download_after_build"><?php _e('Download after export') ?></label> <input type="checkbox" id="wppc_download_after_build" checked="checked" />
		<a id="wppc_export_link" href="#" class="button button-primary button-large"><?php _e('Export as PhoneGap App sources') ?>!</a>
		<div id="wppc_export_feedback"></div>
		
		<?php $previous_exports = self::get_available_app_exports($app_id) ?>
		<?php if( !empty($previous_exports) ): ?>
			<label><?php _e('Download a previous export') ?> : </label>
			<select id="wppc_available_exports">
				<?php foreach( $previous_exports as $timestamp => $entry): ?>
					<option value="<?php echo str_replace('.zip','',$entry) ?>"><?php echo get_date_from_gmt(date( 'Y-m-d H:i:s', $timestamp ),'F j, Y H:i:s' ) ?></option>
				<?php endforeach ?>
			</select>
			<a id="wppc_download_existing_link" href="#" class="button button-large"><?php _e('Download') ?>!</a>
		<?php endif ?>
		<script>
			jQuery("#wppc_export_link").click(function(e) {
				e.preventDefault();
				var themes = jQuery('#wppc_export_theme').val();
				if( themes == null ){
					jQuery('#wppc_export_feedback').addClass('error').html('<?php echo addslashes(__('Please select at least one theme')) ?>');
				}else{
				    var data = {
						action: 'wppc_build_app_sources',
				    	app_id: '<?php echo $app_id ?>',
						nonce: '<?php echo wp_create_nonce('wppc_build_app_sources') ?>',
						themes: themes
				    }
					jQuery.post(ajaxurl, data, function(response) {
						if( response.ok == 1 || response.ok == 2 ){
							$feedback = jQuery('#wppc_export_feedback');
							$feedback.addClass('updated').html('<?php echo addslashes(__("Zip export created successfully")) ?>');
							if( response.ok == 2 ){
								$feedback.append('<br/><br/><strong><?php _e("Warning!") ?></strong> : '+ response.msg);
							}
							if( jQuery('#wppc_download_after_build')[0].checked ){
								window.location.href = '<?php echo add_query_arg(array('action'=>'wppc_download_app_sources'),wp_nonce_url(admin_url(),'wppc_download_app_sources')) ?>&export='+ response['export'];
							}
						}else{
							jQuery('#wppc_export_feedback').addClass('error').html(response.msg);
						}
					});
				}
			});
			jQuery("#wppc_download_existing_link").click(function(e) {
				e.preventDefault();
				var existing_export = jQuery('#wppc_available_exports').val();
				window.location.href = '<?php echo add_query_arg(array('action'=>'wppc_download_app_sources'),wp_nonce_url(admin_url(),'wppc_download_app_sources')) ?>&export='+ existing_export;
			});
		</script>
		<style>
			a#wppc_export_link,#wppc_download_existing_link{
				margin:10px 0;
			}
			div#wppc_export_feedback{
				padding:5px;
			}
			select#wppc_export_theme,#wppc_available_exports{
				width:100%;
			}
			select#wppc_export_theme{
				margin:10px;
			}
		</style>
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
	
	public static function get_appli_index_url($app_id){
		return self::get_appli_dir_url() .'/index.html?wppc_app_id='. WppcApps::get_app_slug($app_id);
	}
	
	public static function download_app_sources(){
		
		if( !check_admin_referer('wppc_download_app_sources') ){
			return;
		}
		
		$export = addslashes($_GET['export']);
		$filename = $export .'.zip';
		$filename_full = self::get_export_files_path() ."/". $filename;
		
		if( file_exists($filename_full) ){
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".$filename."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filename_full));
			ob_end_flush();
			@readfile($filename_full);
			exit();
		}else{
			echo sprintf(__('Error: Could not find zip export file [%s]'),$filename_full);
			echo ' <a href="'. $_SERVER['HTTP_REFERER'] .'">'. __('Back to app edition') .'</a>';
			exit();
		}
	}
	
	public static function build_app_sources(){
		$answer = array('ok'=>1, 'msg'=>'');
		
		if( empty($_POST) || !check_admin_referer('wppc_build_app_sources','nonce') ){
			return;
		}
		
		if( !extension_loaded('zip') ){
			$answer['ok'] = 0;
			$answer['msg'] = __('Zip PHP extension is required to run file export. See http://www.php.net/manual/fr/book.zip.php.');
			self::exit_sending_json($answer);
		}

		if( !self::create_export_directory_if_doesnt_exist() ){
			$export_directory = self::get_export_files_path();
			$answer['ok'] = 0;
			$answer['msg'] = sprintf(__('The export directory [%s] could not be created. Please check that you have the right permissions to create this directory.'),$export_directory);
			self::exit_sending_json($answer);
		}
		
		$app_id = addslashes($_POST['app_id']); 
		
		$themes = !empty($_POST['themes']) && is_array($_POST['themes']) ? $_POST['themes'] : null;
		if( $themes == null ){
			$answer['ok'] = 0;
			$answer['msg'] = __('Please choose at least one theme for the export');
			self::exit_sending_json($answer);
		}
		
		$plugin_dir = plugin_dir_path( dirname(dirname(__FILE__)) );
		$appli_dir = $plugin_dir .'appli';
		 
		$export_filename_base = self::get_export_file_base_name($app_id);
		$export_filename = $export_filename_base .'-'. date('YmdHis');
		$export_filename_full = self::get_export_files_path() ."/". $export_filename .'.zip';
		
		$answer = self::build_zip($app_id,$appli_dir,$export_filename_full,$themes);
		
		$maintenance_answer = self::export_files_maintenance($app_id);
		if( $maintenance_answer['ok'] == 0 ){
			$answer['ok'] = $answer['ok'] == 1 ? 2 : $answer['ok'];
			$answer['msg'] .= "<br/>". $maintenance_answer['msg'];
		}
		
		$answer['export'] = $export_filename;
		
		self::exit_sending_json($answer);
	}
	
	private static function exit_sending_json($answer){
		//If something was displayed before, clean it so that our answer can
		//be valid json (and store it in an "echoed_before_json" answer key
		//so that we can warn the user about it) :
		$content_already_echoed = ob_get_contents();
		if( !empty($content_already_echoed) ){
			$answer['echoed_before_json'] = $content_already_echoed;
			ob_end_clean();
		}
		
		header('Content-type: application/json');
		echo json_encode($answer);
		exit();
	}
	
	private static function get_export_files_path(){
		return WP_CONTENT_DIR .'/uploads/wppc-export';
	}
	
	private static function get_export_file_base_name($app_id){
		return 'phonegap-export-'.  WppcApps::get_app_slug($app_id);
	}
	
	private static function create_export_directory_if_doesnt_exist(){
		$export_directory = self::get_export_files_path();
		$ok = true;
		if( !file_exists($export_directory) ){
			$ok = mkdir($export_directory,0777,true);
		}
		return $ok;
	}
	
	private static function build_zip($app_id,$source, $destination,$themes){

		$answer = array('ok'=>1, 'msg'=>'');		
		
	    if (!extension_loaded('zip') || !file_exists($source)) {
	        $answer['msg'] = sprintf(__('The Zip archive file [%s] could not be created. Please check that you have the permissions to write to this directory.'),$destination);
	        $answer['ok'] = 0;
			return $answer;
	    }
	
	    $zip = new ZipArchive();
	    if( !$zip->open($destination, ZIPARCHIVE::CREATE) ){
			$answer['msg'] = sprintf(__('The Zip archive file [%s] could not be opened. Please check that you have the permissions to write to this directory.'),$destination);
	        $answer['ok'] = 0;
			return $answer;
	    }
	   	
	    if( is_dir($source) === true ){

	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
	
	        foreach($files as $file){
				$filename = str_replace($source, '', $file);
				$filename = ltrim($filename,'/\\');
				
				//Filter themes :
				if( preg_match('|themes[/\\\].+|',$filename) ){
					$theme = preg_replace('|themes[/\\\]([^/\\\]*).*|','$1',$filename);
					if( !in_array($theme, $themes) ){
						continue;
					}
				}
				
	            if( is_dir($file) === true ){
	                if( !$zip->addEmptyDir($filename) ){
						$answer['msg'] = sprintf(__('Could not add directory [%s] to zip archive'),filename);
						$answer['ok'] = 0;
						return $answer;
					}
	            }elseif( is_file($file) === true ){
	                if( !$zip->addFile($file,$filename) ){
						$answer['msg'] = sprintf(__('Could not add file [%s] to zip archive'),filename);
						$answer['ok'] = 0;
						return $answer;
					}
	            }
	        }
	        
	        //Create config.js and config.xml files
	        $zip->addFromString('config.js', WppcConfigFile::get_config_js($app_id));
	        $zip->addFromString('config.xml', WppcConfigFile::get_config_xml($app_id));
	        
	    }else{
	        $answer['msg'] = sprintf(__('Zip archive source directory [%s] could not be found.'),$source);
	        $answer['ok'] = 0;
	        return $answer;
	    }
	
	    if( !$zip->close() ){
			$answer['msg'] = __('Error during archive creation');
			$answer['ok'] = 0;
			return $answer;
		}
		
	    return $answer;
	}
	
	private static function export_files_maintenance($app_id){
		$answer = array('ok'=>1, 'msg'=>'');
		
		$export_directory = self::get_export_files_path();
		
		$entries = self::get_available_app_exports($app_id);
		
		if( !empty($entries) ){
			$i = 1;
			foreach($entries as $entry){
				if( $i > self::export_file_memory ){
					if( !unlink($export_directory .'/'. $entry) ){
						$answer['msg'] .= sprintf(__("Couldn't delete old export [%s]"), $entry) ."<br/>\n";
						$answer['ok'] = 0;
					}
				}
				$i++;
			}
		}
		
		return $answer;
	}
	
	/**
	 * Retrieves app export zip files ordered by date desc. 
	 */
	private static function get_available_app_exports($app_id){
		$available_exports = array();
		
		$export_filename_base = self::get_export_file_base_name($app_id);
		$export_directory = self::get_export_files_path();
		
		if( $handle = opendir($export_directory) ){
			while( false !== ($entry = readdir($handle)) ){
				if( strpos($entry,$export_filename_base) !== false ){
					$entry_date = preg_replace('/'.$export_filename_base.'-(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})\.zip/','$1-$2-$3 $4:$5:$6',$entry);
					$available_exports[strtotime($entry_date)] = $entry;
				}
			}
			closedir($handle);
			if( !empty($available_exports) ){
				krsort($available_exports);
			}
		}

		return $available_exports;
	}
}

WppcBuild::hooks();