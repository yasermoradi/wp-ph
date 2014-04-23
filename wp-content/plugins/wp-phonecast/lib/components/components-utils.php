<?php
class WppcComponentsUtils{
	
	public static function get_formated_content(){
		global $post;
		
		$content = get_the_content();
	
		$replacement_image = self::get_unavailable_media_img();
	
		//Convert dailymotion video
		$content = preg_replace('/\[dailymotion\](.*?)(\[\/dailymotion\])/is','<div class="video">$1</div>',$content);
		$content = preg_replace('/<iframe (.*?)(src)="(.*?(www.dailymotion.com).*?)".*?>\s*<\/iframe>/is','<div class="video">$3</div>',$content);
	
		//Youtube and mp3 inserted via <a> :
		$content = preg_replace('/<a[^>]*href="[^"]*youtube.com.*?".*?>.*?(<\/a>)/is',$replacement_image,$content);
		$content = preg_replace('/<a[^>]*href="[^"]*(\.mp3).*?".*?>.*?(<\/a>)/is',$replacement_image,$content);
	
		//Delete [embed]
		$content = preg_replace('/\[embed .*?\](.*?)(\[\/embed\\])/is',$replacement_image,$content);
	
		//Replace iframes (slideshare etc...) by default image :
		$content = preg_replace('/<iframe([^>]*?)>.*?(<\/iframe>)/is',$replacement_image,$content);
	
		//Apply "the_content" filter : formats shortcodes etc... :
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
	
		$content = strip_tags($content,'<br/><br><p><div><h1><h2><h3><h4><h5><h6><a><span><sup><sub><img><i><em><strong><b><ul><ol><li><blockquote>');
	
		//Use this "wppc_post_content_format" filter to add your own formating to
		//apps posts and pages.
		//To overide (relace) this default formating completely, use the "wppc_posts_list_post_content"
		//and "wppc_page_content" hooks. 
		$content = apply_filters('wppc_post_content_format',$content,$post);
		
		return $content;
	}
	
	public static function cut_content($limit, $content, $no_cut_string_if_shorter_than_limit = false, $nl2br = false, $cutString = " ...") {
	
		$content = preg_replace('|(<a.*?>)(.*?)(</a>)|is','$2',$content);
		$content = preg_replace('#<p(>| .*?>)(.*?)(</p>)#is','$2',$content);
		$content = preg_replace('|(<h[0-9].*?>)(.*?)(</h[0-9]>)|is','$2',$content);
		$content = preg_replace('|<\/?.*?>|is','',$content);
		$content = preg_replace('|\[.*?\].*?\[/.*?\]|is','',$content);
		$content = preg_replace('|\[.*?\]|is',' ',$content);

		//Notes
		$content = preg_replace('|\(\(.+\)\)|is',' ',$content);
	
		if( $nl2br ){
			$content = nl2br($content);
		}else{
			$content = preg_replace('|[\r\n]|is','',$content);
		}
	
		if( strlen($content) <= $limit ) {
			
			$new_content = $content;
			if( !$no_cut_string_if_shorter_than_limit ){
				$new_content .= $cutString;
			}
			
		}else{
	
			$str = substr($content,0,$limit);
			$new_content =  substr($str,0,strrpos($str,' '));
	
			$quote = substr($new_content,-5);
			if(preg_match('|["�]|is', $quote)) {
				$new_content = substr_replace($new_content,'',-5);
			}

			$last = substr($new_content,-1,1);
	
			if(!preg_match("|[a-z0-9���������]|i", $last)) {
				$new_content = substr_replace($new_content, "", -1);
			}
	
			$new_content = $new_content.$cutString;
		}
		
		return $new_content;
	}
	
	public static function get_unavailable_media_img(){
		
		$params = array(
				'src' => get_bloginfo('wpurl') .'/wp-content/uploads/unavailable_media.png',
				'width' => 604,
				'height' => 332
		);
		
		$params = apply_filters('wppc_unavailable_media_img',$params);
		
		$img = '<img class="unavailable" alt="'. __('Unavailable content') .'" src="'. $params['src'] .'" width="'. $params['width'] .'" height="'. $params['height'] .'" />';
		
		return $img;
	}
	
}