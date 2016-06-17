<?php

$url = '';
$email = '';

try {
	//$dbfile = '/var/tmp/database.sqlite';
	$dbfile = dirname(__FILE__).'/database.sqlite';
	$pdo = new PDO('sqlite:'.$dbfile);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT

	$create = $pdo->prepare('create table if not exists settings (
				  	id integer primary key,
				  	key text,
				  	value text)');
	$create->execute();

	if (isset($_POST['url'])){
		$set = $pdo->prepare('insert or replace into settings (id, key, value) values (
						(select id from settings where key = "url"), "url", ?)');
		$set->execute(array($_POST['url']));
	}
	if (isset($_POST['email'])){
		$set = $pdo->prepare('insert or replace into settings (id, key, value) values (
						(select id from settings where key = "email"), "email", ?)');
		$set->execute(array($_POST['email']));

	}
	$get = $pdo->prepare('select key, value from settings');
	$get->execute();
	$result = $get->fetchAll();
	$pdo = null;
	$settings = [];
	foreach ($result as $row){
		$settings[$row['key']] = $row['value'];
	}
	if (isset($settings['url'])){
		$url = $settings['url'];
	}
	if (isset($settings['email'])){
		$email = $settings['email'];
	}
}
catch(PDOException $e) {
	die($e->getMessage());
}


function url_origin( $s, $use_forwarded_host = false ){
	$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
	$sp       = strtolower( $s['SERVER_PROTOCOL'] );
	$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
	$port     = $s['SERVER_PORT'];
	$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
	$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
	$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
	return $protocol . '://' . $host;
}
function full_url( $s, $use_forwarded_host = false ){
	return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}
$absolute_url = full_url($_SERVER);
$front = str_replace('config.php','',$absolute_url);

?>

<html>
<head>
	<title>PhpStalkerTrap - Configuration</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<style>
		body {
			margin:35px;
			max-width:400px;
		}
		input {
			margin-bottom:15px;
			display:block;
			width:100%;
		}
		input {
			margin-bottom:15px;
			display:block;
			width:100%;
		}
		input[type="submit"] {
			max-width:200px;
		}
		fieldset {
			margin-bottom:25px;
		}
	</style>
</head>
<body>
<form action="config.php" method="post">
	<fieldset>
		<legend>Fake web page</legend>
		<label for="url">url of the web page to mimic</label>
		<input type="url" name="url" id="url" value="<?=$url?>">
	</fieldset>
	<fieldset>
		<legend>Report</legend>
		<label for="email">recipient email address</label><br>
		<input type="email" name="email" id="email" value="<?=$email?>">
		<a href="visits.log" target="_blank">read log</a>
	</fieldset>
	<input type="submit">
</form>
<p>
	link to send :<br>
	<a href="<?=$front?>" target="_blank"><?=$front?></a>
</p>
</body>
</html>
