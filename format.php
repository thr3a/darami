<?php
require_once("./lib/botsource.php");
function callback($tweet){
	global $botSource;
	if( isset($tweet->user->lang) && $tweet->user->lang === "ja" ){
		$text = $tweet->text;
		$text = htmlspecialchars( $text, ENT_QUOTES );
		$text = str_replace( "\n", '', $text);//改行を削除
		//卵アイコンはスルー
		if($tweet->user->default_profile_image === true) return;
		//フォロー数フォロワー数0はスルー
		if( empty($tweet->user->friends_count) || empty($tweet->user->followers_count) ) return;
		//RT非表示かどうか
		if( empty($_GET['rtflag']) && strstr( $text, "RT " )) return;
		//botかどうか
		$source = preg_replace('/<[^>]+>/', '', $tweet->source);
		if( empty($_GET['botflag']) && in_array( $source, $botSource)) return;
		//filter
		if( !empty($_GET['filter']) ){
			$filter = htmlspecialchars( $_GET['filter'], ENT_QUOTES );
			if( !preg_match($filter, $text) ) return;
		}
		//短縮リンクを展開
		if(isset($tweet->entities->urls)){
			$shortUrl = array();
			$expanedUrl = array();
			$displayUrl = array();
			for($i = 0; $i < count( $tweet->entities->urls );$i++){
				$url = $tweet->entities->urls[$i];
				array_push($shortUrl, $url->url);
				array_push($displayUrl, '<a href="' . $url->expanded_url . '" target="_blank">' . $url->display_url . '</a>');
			}
			$text = str_replace($shortUrl, $displayUrl, $text);
		}
		//@ユーザーにリンクタグをつける
		$text = preg_replace('/(?<![0-9a-zA-Z\'"#@=:;])@([0-9a-zA-Z_]{1,15})/u','<a href="http://twitter.com/\\1">@\\1</a>', $text);
		//ハッシュタグにリンクタグをつける
		$text = preg_replace("/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)#([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u","<a href=\"http://search.twitter.com/search?q=%23\\1\">#\\1</a>", $text);
		$userImage = $tweet->user->profile_image_url;
		$tweetId = $tweet->id_str;
		$screenName = $tweet->user->screen_name;
		$timeline = '<a class="pull-left" href="https://twitter.com/' . $screenName . '/status/' . $tweetId . '" target="_blank">';
		$timeline .= '<img class="media-object" width="64" height="64" src="' . $userImage . '"></a>';
		$timeline .= '<div class="media-body"><h5 class="media-heading"><strong>@' . $screenName . '</strong></a></h5><b>' . $text . '</b>';
		echo '<script>showTweet(\' '. $timeline . ' \');</script>';
		//echo '<script>$("#timeline").prepend($(\'<li class="media">' . $timeline . '</li>\').fadeIn(\'slow\'));</script>';
		ob_flush();
		flush();
		sleep(1);
	}
}