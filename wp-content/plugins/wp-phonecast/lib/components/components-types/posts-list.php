<?php
class WppcComponentTypePostsList extends WppcComponentType{
	
	protected function compute_data($options){
		global $wpdb;
		
		$post_type = !empty($options['post-type']) ? $options['post-type'] : 'post';
		
		$query = array('post_type' => $post_type);
		
		$query_args = array('post_type' => $post_type);
			
		$query_args['numberposts'] = 20; //TODO : dynamise this!!
		
		if( !empty($options['taxonomy']) && !empty($options['term']) ){
			
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $options['taxonomy'],
					'field' => 'slug',
					'terms' => $options['term']
				)
			);
			
			$query['type'] = 'taxonomy';
			$query['taxonomy'] = $options['taxonomy'];
			$query['terms'] = is_array($options['term']) ? $options['term'] : array($options['term']);
		}
					
		$posts = get_posts($query_args);
		$total = $wpdb->get_var('SELECT FOUND_ROWS()');
		
		$posts_ids = array();
		
		foreach($posts as $post){
			$posts_ids[$post->ID] = self::get_post_data($post);
		}
		
		$this->set_specific('ids',array_keys($posts_ids));
		$this->set_specific('total',$total);
		$this->set_specific('query',$query);
		$this->set_globals('posts',$posts_ids);
		
	} 
	
	private function get_post_data($_post){
		global $post;
		$post = $_post;
		setup_postdata($post);
		
		$post_data = array(
			'id' => $post->ID,
			'post_type' => $post->post_type,
			'date' => strtotime($post->post_date),
			'title' => $post->post_title,
			'content' => '',
			'excerpt' => '',
			'featured_img' => '',
			'author' => '',
			'url' => get_permalink(),
			'nb_comments' => (int)get_comments_number()
		);
		
		$content = WppcComponentsUtils::get_filtered_content();
		$post_data['content'] = $content;
		
		$post_data['excerpt'] = !empty($post->post_excerpt) ? $post->post_excerpt : WppcComponentsUtils::cut_content(200,$content);
		
		$post_featured_img_id = get_post_thumbnail_id($post->ID);
		if( !empty($post_featured_img_id) ){
			$featured_img_src = wp_get_attachment_image_src($post_featured_img_id, 'mobile-featured-thumb');
			@$post_data['featured_img']['src'] = $featured_img_src[0];
			$post_data['featured_img']['width'] = $featured_img_src[1];
			$post_data['featured_img']['height'] = $featured_img_src[2];
		}
		
		$post_data = apply_filters('wppc_posts_list_post_data',$post_data,$post);
		
		return (object)$post_data;
	}
	
	public function get_options_to_display($component){
		$post_type = get_post_type_object($component->options['post-type']);
		$taxonomy = get_taxonomy($component->options['taxonomy']);
		$term = get_term_by('slug',$component->options['term'],$component->options['taxonomy']);
		$options = array();
		if( !is_wp_error($term) ){
			$options = array(
				'post-type' => array('label'=>__('Post type'),'value'=>$post_type->labels->name),
				'taxonomy' => array('label'=>__('Taxonomy'),'value'=>$taxonomy->labels->name),
				'term' => array('label'=>__('Term'),'value'=>$term->name)
			);
		}
		return $options;
	}
	
	public function echo_form_fields($component){
		$post_types = get_post_types(array('public'=>true),'objects'); //TODO : hook on arg array
		unset($post_types['attachment']);
		
		$has_options = !empty($component) && !empty($component->options);
		
		reset($post_types);
		$first_post_type = key($post_types);
		
		$current_post_type = $first_post_type;
		$current_taxonomy = '';
		$current_term = '';
		if( $has_options ){
			$options = $component->options;
			$current_post_type = $options['post-type'];
			$current_taxonomy = $options['taxonomy'];
			$current_term = $options['term'];
		}
		
		?>
		<div class="component-params">
			<label><?php _e('Post type') ?> : </label>
			<select name="post-type" class="posts-list-post-type">
				<?php foreach($post_types as $post_type => $post_type_object): ?>
					<?php $selected = $post_type == $current_post_type ? 'selected="selected"' : '' ?>
					<option value="<?php echo $post_type ?>" <?php echo $selected ?>><?php echo $post_type_object->labels->name ?></option>
				<?php endforeach ?>
			</select>
		</div>
		
		<div class="ajax-target">
			<?php self::echo_sub_options_html($current_post_type,$current_taxonomy,$current_term) ?>
		</div>
			
		<?php
	}
	
	public function echo_form_javascript(){
		?>
		<script type="text/javascript">
			(function(){
				var $ = jQuery;
				$('.wrap').delegate('.posts-list-post-type','change',function(){
					var post_type = $(this).find(":selected").val();
					WppcComponents.ajax_update_component_options(this,'posts-list','change-post-list-option',{taxonomy:'',post_type:post_type});
				});
				$('.wrap').delegate('.posts-list-taxonomies','change',function(){
					var post_type = $(this).closest('.ajax-target').prev('div.component-params').find('select.posts-list-post-type').eq(0).find(":selected").val();
					var taxonomy = $(this).find(":selected").val();
					WppcComponents.ajax_update_component_options(this,'posts-list','change-post-list-option',{taxonomy:taxonomy,post_type:post_type});
				});
			})();
		</script>
		<?php
	}

	public function get_ajax_action_html_answer($action,$params){
		switch($action){
			case 'change-post-list-option':
				$post_type = $params['post_type'];
				$taxonomy = $params['taxonomy'];
				self::echo_sub_options_html($post_type,$taxonomy);
				break;
		} 
	}
	
	private function echo_sub_options_html($current_post_type,$current_taxonomy='',$current_term = ''){

		$taxonomies = get_object_taxonomies($current_post_type);
		$taxonomies = array_diff($taxonomies,array('nav_menu','link_category'));
		$taxonomies = apply_filters('wppc_component_type_posts_list_form_taxonomies',$taxonomies);
		
		$first_taxonomy = reset($taxonomies);
		$current_taxonomy = empty($current_taxonomy) ? $first_taxonomy : $current_taxonomy;

		?>
		<label><?php _e('Taxonomy') ?> : </label>
		<?php if( !empty($taxonomies) ): ?>
			<select name="taxonomy" class="posts-list-taxonomies">
				<?php foreach($taxonomies as $taxonomy_slug): ?>
					<?php $taxonomy = get_taxonomy($taxonomy_slug) ?>
					<?php $selected = $taxonomy_slug == $current_taxonomy ? 'selected="selected"' : '' ?>
					<option value="<?php echo $taxonomy_slug ?>" <?php echo $selected ?>><?php echo $taxonomy->labels->name ?></option>
				<?php endforeach ?>
			</select>
			<br/>
			<?php 
				$taxonomy_obj = get_taxonomy($current_taxonomy);
				$terms = get_terms($current_taxonomy);
			?>
			<label><?php echo $taxonomy_obj->labels->name ?> : </label>
			<?php if( !empty($terms) ): ?>
				<select name="term">
					<?php foreach($terms as $term): ?>
						<?php $selected = $term->slug == $current_term ? 'selected="selected"' : '' ?>
						<option value="<?php echo $term->slug ?>" <?php echo $selected ?>><?php echo $term->name ?></option>
					<?php endforeach ?>
				</select>
			<?php else: ?>
				<?php echo sprintf(__('No %s found'),$taxonomy_obj->labels->name); ?>
			<?php endif ?>
		<?php else: ?>
			<?php echo sprintf(__('No taxonomy found for post type %s'),$current_post_type); ?>
		<?php endif ?>
		<?php
	}
	
	public function get_options_from_posted_form($data){
		$post_type = $data['post-type'];
		$taxonomy = $data['taxonomy'];
		$term = $data['term'];
		$options = array('taxonomy' => $taxonomy, 'term' => $term, 'post-type' => $post_type);
		return $options;
	}
	
}