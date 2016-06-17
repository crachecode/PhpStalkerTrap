<?php

/*** get database data or redirect to config ***/

try {
	$dbfile = '/var/tmp/database.sqlite';
	//$dbfile = dirname(__FILE__).'/database.sqlite';
	if (!file_exists($dbfile)){
		header('Location: config.php');
	}
	$pdo = new PDO('sqlite:'.$dbfile);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT

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
	if (!isset($url) || !isset($email)){
		header('Location: config.php');
	}
}
catch(PDOException $e) {
	die($e->getMessage());
}


/*** retrieve information ***/

$date = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
if (isset($_SERVER['HTTP_REFERER'])){
	$referer = $_SERVER['HTTP_REFERER'];
}
else {
	$referer = 'undefined';
}

$output = $date.'
ip : '.$ip.'
user agent : '.$user_agent.'
accept language : '.$accept_language.'
referer : '.$referer.'

';


/*** send information by email ***/

$to = $email;
$subject = 'report from PhpStalkerTrap';
$message = $output;
mail($to, $subject, $message);


/*** log information ***/

file_put_contents('./visits.log', $output, FILE_APPEND);


/*** get the target web page content with curl ***/

$ch = curl_init();
curl_setopt_array($ch,
	array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FAILONERROR => true,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
		CURLOPT_CONNECTTIMEOUT => 999,
		CURLOPT_HTTPAUTH => CURLAUTH_ANY,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER=>array("Content-type: multipart/form-data"),
	)
);
$response = curl_exec($ch);
if(curl_error($ch)){
	die('curl error : ' . curl_error($ch));
}
if(!$response){
	die('error : curl returns a blank page');
}
curl_close ($ch);


/*** add a base tag with the correct href in the head for relative paths to work ***/

//$response = str_replace("<head>", "<head><base href=\"$url\">", $response);

$doc = new DOMDocument();
$internalErrors = libxml_use_internal_errors(true);
$doc->loadHTML($response);

$base = $doc->createElement('base');
$href = $doc->createAttribute('href');
$href->value = $url;
$base->appendChild($href);

$head = $doc->getElementsByTagName('head')->item(0);
$head->insertBefore($base, $head->firstChild);

$response = $doc->saveHTML();


/*** display content ***/

echo $response;