<?php

class WppcBuild{
	
	public static function hooks(){
		add_action('add_meta_boxes', array(__CLASS__,'add_meta_boxes'));
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
		?>
		<a href="<?php echo WppcSimulator::get_simulator_url($post->ID) ?>"><?php _e('View application in simulator') ?></a>
		<?php 
	}
}

WppcBuild::hooks();