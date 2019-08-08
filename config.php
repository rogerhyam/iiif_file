<?php

// set the error reporting to high - so we can see our problems
// commercial sites this would be set lower for production
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('UTC');

$subdirectory = '/'; // within the URL - you may want to change this if you're not hosting at the web root.
$image_root_dir = 'images/'; // the directory where your images are. Could be absolute (real) or relative to script. End with a slash. 
$cache_dir = 'cache/'; // the directory where generated images will be cached so they don't have to be created with every request.

$cache_max_size_mb = 500; // the cache_manager.php (called by cron occassionally) will keep the cache dir to this size.

$file_64 = @$_GET['file'];
$file_path = base64_decode($file_64);
$file_path_full = $image_root_dir . $file_path;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ;
$base_uri = $protocol . $domainName . $subdirectory . $file_64;
	

?>