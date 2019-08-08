<?php
	
include_once('config.php');
include_once('functions.php');

$props = get_image_properties($file_path_full);

$out = new stdClass();
$out->context = array("http://www.w3.org/ns/anno.jsonld","http://iiif.io/api/presentation/3/context.json");
$out->id = "$base_uri/manifest";
$out->type = "Manifest";
$out->label = create_label("Default Mainfest for $file_64" );

$out->summary = new stdClass();
$out->summary = array($file_64);
$out->viewingDirection = "left-to-right";

$out->thumbnail = array();
$out->thumbnail[] = new stdClass();
// the thumbnail is an actual link to an image of an appropriate size 
// we pick the level 1 of the zoomify tile pyramid and ask for that.
// https://iiif.rbge.org.uk/herb/iiif/E00001237/full/824,1258/0/default.jpg

if($props['is_tile_pyramid']){
	
	// we select a size based on one of the zoomify tile layers
	$out->thumbnail[0]->id = $base_uri . '/full/' . $props['zoomify_layers'][1]['width'] . ',' . $props['zoomify_layers'][1]['height'] . '/0/default.jpg';;
	$out->thumbnail[0]->type = "Image";
}else{
	
	// we choose a size that fits in a 200px box
	if($props['width'] > $props['height']){
		$ratio = 200 / $props['width'];
	}else{
		$ratio = 200 / $props['height'];
	}
	
	$thumb_width = floor($props['width'] * $ratio);
	$thumb_height = floor($props['height'] * $ratio);
	
	$out->thumbnail[0]->id = $base_uri . '/full/' . $thumb_width . ',' . $thumb_height . '/0/default.jpg';;
	$out->thumbnail[0]->type = "Image";
}



$out->thumbnail[0]->service = array();
$out->thumbnail[0]->service[0] = new stdClass();
$out->thumbnail[0]->service[0]->id = $base_uri;
$out->thumbnail[0]->service[0]->type = "ImageService3";
$out->thumbnail[0]->service[0]->profile = "level0";

$out->rights = "https://creativecommons.org/licenses/by-sa/2.5/";
$out->requiredStatement = create_key_value_label("Attribution", "Royal Botanic Garden Edinburgh");

$canvas = new stdClass();
$out->items = array($canvas);
$canvas->id = "$base_uri#canvas";
$canvas->type = "Canvas";
$canvas->label = create_label($file_64);

$canvas->height = $props['height'];
$canvas->width = $props['width'];

// annotation page
$canvas->items = array();
$image_anno_page = new stdClass();
$canvas->items[] = $image_anno_page;
$image_anno_page->id = "$base_uri#annotation_page";
$image_anno_page->type = "AnnotationPage";

// annotation
$image_anno = new stdClass();
$image_anno_page->items = array($image_anno);
$image_anno->id = "$base_uri#annotation";
$image_anno->type = "Annotation";
$image_anno->motivation = "Painting";
$image_anno->body = new stdClass();
$image_anno->body->id = "$base_uri/info.json";
$image_anno->body->type = "Image";
$image_anno->body->format = "image/jpeg";	
$service = new stdClass();
$service->id = $base_uri;
$service->type = "ImageService3";
$service->profile = "level0";
$image_anno->body->service = array($service);
$image_anno->body->height = $props['height'];
$image_anno->body->width = $props['width'];
$image_anno->target = "$base_uri#canvas";
//print_r($out);
$json = json_encode( $out, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES );
// total hack to add the @ to the context attribute (not acceptable in php)
$json = str_replace('"context":','"@context":', $json);
header('Content-Type: application/json');
echo $json;

	
?>