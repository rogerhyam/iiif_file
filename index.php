<?php
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Simple IIIF Image Server</title>

  
</head>
<body>
	
	<h1>Simple IIIF Image Server</h1>
  
  	<h2>Examples</h2>
  
  	<h3>Zoomify Image Stack</h2>
  
  	<p>Path: examples/zoom_example</p>
<?php
		$path_64 = base64_encode('examples/zoom_example');
		
		$uri = $path_64 . '/info.json';
		echo "<p>Image Info: <a href=\"$uri\">$uri</a></p>";

		$uri = $path_64 . '/manifest';
		echo "<p>Manifest: <a href=\"$uri\">$uri</a></p>";

?>

  	<h3>Single File Image</h2>
  
  	<p>Path: examples/single_file_example.jpg</p>
<?php
		$path_64 = base64_encode('examples/single_file_example.jpg');
		
		$uri = $path_64 . '/info.json';
		echo "<p>Image Info: <a href=\"$uri\">$uri</a></p>";

		$uri = $path_64 . '/manifest';
		echo "<p>Manifest: <a href=\"$uri\">$uri</a></p>";

?>


	
  
</body>
</html>
