<?php
session_start();
require_once("./lib/setting.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/twi/index.php");

$oauth_token = $_GET['oauth_token'];
$oauth_verifier = $_GET['oauth_verifier'];

if($oauth_token != $_SESSION['oauth_token']){
	echo 'oauth_verifier error';
	exit;
}

$tmh = new tmhOauth(array(
	'consumer_key' => $consumer_key,
	'consumer_secret' => $consumer_secret,
	'token' => $_SESSION['oauth_token'],
	'secret' => $_SESSION['oauth_token_secret']
));

$tmh->user_request(array(
	'method' => 'POST',
	'url' => $tmh->url('oauth/access_token', ''),
	'params' => array(
		'oauth_verifier' => $oauth_verifier
	)
));

$_SESSION['user'] = $tmh->extract_params($tmh->response['response']);

header("Location: ./index.php");
?>