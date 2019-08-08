<?php
	
function create_label($txt){
	$out = new stdClass();
	$out->en = array($txt);
	return $out; 
}

function create_key_value_label($key, $val){
	$out = new stdClass();
	$out->label = create_label($key);
	$out->value = new stdClass();
	$out->value->en = array($val);
	return $out;
}

function get_image_properties($image_path){
	
	
	// is it a directory or a file?
	if(is_dir($image_path)){
		return get_image_properties_zoom($image_path);		
	}else{
		return get_image_properties_file($image_path);
	}
	
}

function get_image_properties_file($image_path){
	
	$out = array();
	$out['is_tile_pyramid'] = false;
	
	$image_size = getimagesize($image_path);
	
	$out['width'] = $image_size[0];
	$out['height'] = $image_size[1];
	
	return $out;
	exit;
}

function get_image_properties_zoom($image_path){

	$xml_string = file_get_contents($image_path . '/ImageProperties.xml');
	
	$xml=simplexml_load_string($xml_string);
	
	$out = array();
	$out['is_tile_pyramid'] = true;
	$out['width'] = (int)$xml['WIDTH'];
	$out['height'] = (int)$xml['HEIGHT'];
	$out['number_tiles'] = (int)$xml['NUMTILES'];
		
	$largest = $out['width'] > $out['height'] ? $out['width'] : $out['height'];
	$out['largest_dimension'] = $largest;
		
	// these are the Scale Factors
	$layers[] = 1;
	$half = $largest/2;
	while($half > 256){
		$layers[] = end($layers) * 2;
		$half = floor($half / 2);
	}
	
	//array_pop($layers);
	
	$out['layers'] = $layers;
	
	// create a description of the zoomify layers in the image
	$w = $out['width'];
	$h = $out['height'];
	$zlayers = array();
	for ($i=count($out['layers']); $i >= 0 ; $i--) { 
		$layer = array();
		$layer['width'] = $w;
		$layer['height'] = $h;
		$layer['cols'] = ceil(floor($w) / 256);
		$layer['rows'] = ceil(floor($h) / 256);
		$layer['tiles_in_layer'] = $layer['rows'] * $layer['cols'];
		
		// half it for the next time around
		$w = floor($w/2);
		$h = floor($h/2);
	
		$zlayers[] = $layer;
	}
	
	$out['zoomify_layers'] = array_reverse($zlayers);
	return $out;
}

/* ------------------------------------ */
function get_closest($search, $arr) {
   $closest = null;
   foreach ($arr as $item) {
      if ($closest === null || abs($search - $closest) > abs($item - $search)) {
         $closest = $item;
      }
   }
   return $closest;
}
function return_thumbnail($file_path_full, $size, $dimension, $image_props){
	
	global $cache_dir;
	
	// check if we have a cached version of the thumbnail
	$thumb_cached_path = $cache_dir . str_replace('/', '_', $file_path_full) . '-thumb-' . $dimension . '-'. $size . '.jpg';
	if(file_exists($thumb_cached_path)){
		header("Content-Type: image/jpeg");
		readfile($thumb_cached_path);
		exit;
	}
	
	$layers = $image_props['zoomify_layers'];
	$level = -1;
	for ($i=0; $i < count($layers); $i++) { 
		if($layers[$i][$dimension] >= $size){
			$level = $i;
			break;
		}
	}
	if($level == -1){
		http_response_code(400);
		echo "Sorry: Can only handle full image requests of specific size. Not width $width";
		exit;
	}
	
	// load the full image 
	$full_cached_path = $cache_dir . str_replace('/', '_', $file_path_full) . '_level-' . $level . '.jpg';
	if(file_exists($full_cached_path)){
		$image = new Imagick($full_cached_path);
	}else{
		$image  = get_full_image($level, $image_props);
		$image->writeImage($full_cached_path);
	}
	
	if($dimension == 'width'){
		$image->scaleImage($size, 0, false);
	}else{
		$image->scaleImage(0, $size, false);
	}
	
	$image->writeImage($thumb_cached_path);
	
	header('Content-Type: image/jpeg');
	echo $image;
	
}
function return_full_image($file_path, $level, $image_props){
	
	global $cache_dir;
	
	$cached_file_path = $cache_dir . str_replace('/', '_', $file_path) . '_level-' . $level . '.jpg';

	// check if we have it cached before we do anything else
	if(file_exists($cached_file_path)){
		header("Content-Type: image/jpeg");
		readfile($cached_file_path);
		exit;
	}
	
	// not got it so make it
	$combined = get_full_image($level, $image_props);
	
	// cache it so we don't have to create it again
	$combined->writeImage($cached_file_path);
	header('Content-Type: image/jpeg');
	echo $combined;
	
}
function get_full_image($level, $image_props){
	
	global $file_path_full;
	
	$layers = $image_props['zoomify_layers'];
	$layer = $layers[$level];
	$rows = new Imagick();
	for ($i=0; $i < $layer['rows']; $i++) {
	
		$row = new Imagick();
	
		for ($j=0; $j < $layer['cols']; $j++) {		
			$tile_group = get_tile_group($layers, $level, $j, $i);
			$uri = "$file_path_full/TileGroup$tile_group/$level-$j-$i.jpg";
			$row->addImage(new Imagick($uri));
		}
	
		// stitch the row into a single image
		$row->resetIterator();
	
		// add it to the rows
		$rows->addImage($row->appendImages(false));
	
	}
	$rows->resetIterator();
	$combined = $rows->appendImages(true); // append them vertically
	$combined->setImageFormat("jpg");
	
	return $combined;
}

function get_tile_group($layers, $level, $col, $row){
	
	// count all the tiles to this point
	$number_tiles = 0;
	
	// add the tiles from previous layers
	for ($i=0; $i < $level; $i++) { 
		$layer = $layers[$i];
		$number_tiles += $layer['cols'] * $layer['rows'];
	}
	
	// add the ones to get to this point in this layer
	
	// all the full columns up to this one
	$current_layer = $layers[$level];
	$number_tiles += $current_layer['cols'] * $row +1 + $col -1;
	
	//return $number_tiles;
	
	return floor($number_tiles/256);
	
}

function throw_badness($message){
	header("HTTP/1.1 400 Bad Request");
	echo "<h1>400 Bad Request</h1>";
	echo "<p>$message</p>";
	exit;
}
	
?>