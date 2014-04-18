<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>ダラ見たん</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="http://nyaaz.dip.jp/lib/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="http://www.berriart.com/sidr/javascripts/vendor/sidr/stylesheets/jquery.sidr.dark.css">
	<script src="http://nyaaz.dip.jp/lib/jquery-2.1.0.min.js"></script>
	<script src="./jquery.pause.min.js"></script>
<script src="jquery.sidr.min.js"></script>
	<script>
	//phpから受け取ったツイートを表示
	function showTweet( tweetdiv ){
		//ツイートのdivの高さ
		var tweetHeight = 64;
		//どのくらいの高さから流すかランダムに
		var space = Math.round( Math.random() * ( $(window).height() - tweetHeight ));
		var div = $('<div style="position: fixed; top: ' + space + 'px; left: ' + $(window).width() + 'px; white-space: nowrap; z-index: 9999;">' + tweetdiv + '</div>');
		$(document.body).append(div);
		div.animate({
			left: '-=' + ( $(window).width() + div.width() + 64) + 'px'
		}, 10000, function() {
			div.remove();
		});
		div.hover(function(){
			div.pause();
		},
		function(){
			div.resume();
		}
		);
	}
	</script>
</head>
<body>
<a id="simple-menu" class="tiny button secondary radius" href="#sidr">Simple menu</a>
<div id="sidr">
  <!-- Your content -->
  <ul>
    <li><a href="#">List 1</a></li>
    <li class="active"><a href="#">List 2</a></li>
    <li><a href="#">List 3</a></li>
  </ul>
</div>
<div class="container" style="padding-top: 70px;">
	
<?php
set_time_limit(0);
session_start();
require_once("./lib/setting.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/twi/index.php");
require_once($_SERVER["DOCUMENT_ROOT"] . '/lib/Phirehose/Phirehose.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/lib/Phirehose/OauthPhirehose.php');
require_once("./format.php");

$tmh = new tmhOauth(array(
	'consumer_key' => $consumer_key,
	'consumer_secret' => $consumer_secret,
	"token" => $_SESSION['user']['oauth_token'],
	"secret" => $_SESSION['user']['oauth_token_secret']
));
if( $tmh->request('GET', $tmh->url('1.1/account/verify_credentials')) !== 200 ){
	//非ログイン状態
	$tmh->request( 'POST' , $tmh->url( 'oauth/request_token', ''), array( 'oauth_callback' => $callBackUrl ) );
	$response = $tmh->extract_params( $tmh->response['response'] );
	$_SESSION['oauth_token'] = $response['oauth_token'];
	$_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
	$url = $tmh->url( 'oauth/authorize', '' ) . "?oauth_token=" . $response['oauth_token'];
?>
<div class="jumbotron">
	<h1>ダラ見たん</h1>
	<p>第二のTimelineを</p>
	<p><a class="btn btn-primary btn-lg" href="<?php echo $url ?>">さっそく眺める</a></p>
</div>
<?php
}else{
?>
	<form class="form-inline" action="" method="get">
		<fieldset>
			<div class="form-group">
			<label><input type="checkbox" name="rtflag" <?php if( isset($_GET['rtflag']) ) echo 'checked'?>>　リツイートを表示</label>
			<label><input type="checkbox" name="botflag" <?php if( isset($_GET['botflag']) ) echo 'checked'?>>　botを表示</label>
			<input type="text" class="form-control" name="filter" placeholder="/正規表現/" value="<?php if( isset($_GET['filter']) ) echo $_GET['filter']?>">
			<button type="submit" class="btn btn-default">更新</button>
			<a href="./logout.php">ログアウト</a>
		</fieldset>
	</form>
	<div class="col-md-offset-1 col-md-8">
		<ul id="timeline" class="media-list"></ul>
	</div>
<?php
	//ログイン状態
	class SampleConsumer extends OauthPhirehose{
		public function enqueueStatus($status){
			callback( json_decode($status) );
		}
	}
	
	define("TWITTER_CONSUMER_KEY", $consumer_key);
	define("TWITTER_CONSUMER_SECRET", $consumer_secret);
	define("OAUTH_TOKEN", $_SESSION['user']['oauth_token']);
	define("OAUTH_SECRET", $_SESSION['user']['oauth_token_secret']);
	
	$sc = new SampleConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_SAMPLE);
	//$sc->consume();
}
?>
</div>
<script>
$(document).ready(function() {
	$('#simple-menu').sidr();
});
</script>
</body>
</html>