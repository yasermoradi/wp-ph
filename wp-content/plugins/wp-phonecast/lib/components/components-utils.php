<?php
class WppcComponentsUtils{
	
	public static function get_filtered_content(){
		$content = get_the_content();
	
		$replacement_image = '<img class="unavailable" alt="contenu indisponible" src="'. get_bloginfo('wpurl') .'/wp-content/uploads/media_indisponible.png" width="604" height="332" />';
	
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
	
		return $content;
	}
	
	public static function cut_content($limit, $content, $no_cut_string_if_shorter_than_limit = false, $nl2br = false, $cutString = " ...") {
	
		//nettoyage des balises : on garde le contenu des <a> et <h...>
		$content = preg_replace('|(<a.*?>)(.*?)(</a>)|is','$2',$content);
		$content = preg_replace('#<p(>| .*?>)(.*?)(</p>)#is','$2',$content);
		$content = preg_replace('|(<h[0-9].*?>)(.*?)(</h[0-9]>)|is','$2',$content);
		$content = preg_replace('|<\/?.*?>|is','',$content);
		$content = preg_replace('|\[.*?\].*?\[/.*?\]|is','',$content);
		$content = preg_replace('|\[.*?\]|is',' ',$content);
	
		//suppresion des notes dans les articles
		$content = preg_replace('|\(\(.+\)\)|is',' ',$content);
	
	
		//Suppression des retours chariots:
		if($nl2br){
			$content = nl2br($content);
		}else{
			$content = preg_replace('|[\r\n]|is','',$content);
		}
	
		if(strlen($content) <= $limit) {
			$new_content = $content;
			if( !$no_cut_string_if_shorter_than_limit )
				$new_content .= $cutString;
		} else {
	
			$str = substr($content,0,$limit);
			$new_content =  substr($str,0,strrpos($str,' '));
	
			//exceptions
			//retire le guillemet problématique à la fin si il y en a un
			$quote = substr($new_content,-5);
			if(preg_match('|["«]|is', $quote)) {
				$new_content = substr_replace($new_content,'',-5);
			}
			// the last character of $content
			$last = substr($new_content,-1,1);
	
			if(!preg_match("|[a-z0-9àùèéâêîûô]|i", $last)) {
				$new_content = substr_replace($new_content, "", -1);
			}
	
			$new_content = $new_content.$cutString;
		}
		return $new_content;
	}
	
}