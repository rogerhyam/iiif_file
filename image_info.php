<?php
include_once('config.php');
include_once('functions.php');

// do we even have that file?
if(!file_exists($file_path_full)){
	throw_badness('Image does not exist.');
}

$props = get_image_properties($file_path_full);

// generate the json
$out = new stdClass();
$out->__at__context = "http://iiif.io/api/image/3/context.json";
$out->id = $base_uri;
$out->__at__id = $base_uri;
$out->type = "ImageService3";
$out->protocol = "http://iiif.io/api/image"; 
$out->profile = "level0"; // what features are supported
$out->width = $props['width'];
$out->height = $props['height'];
$out->maxWidth = $props['width'];
$out->maxHeight = $props['height'];
$out->maxArea = $props['width'] * $props['height'];

if($props['is_tile_pyramid']){
	$scale_factors = $props['layers'];
	$tiles = new stdClass();
	$tiles->width = 256;
	$tiles->height = 256;
	$tiles->scaleFactors = $scale_factors;


	$out->tiles = array($tiles);
	$out->sizes = array();
	foreach($props['zoomify_layers'] as $layer){
		$size = new stdClass();
		$size->width = $layer["width"];
		$size->height = $layer["height"];
		$out->sizes[] = $size;
	}
}

//print_r($out);
$json = json_encode( $out, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES );
// total hack to add the @ to the context attribute (not acceptable in php)
$json = str_replace('__at__','@', $json);
header('Content-Type: application/json');
echo $json;

?>