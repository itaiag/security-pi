<?php
require 'KLogger.php';
$pathToImages = 'images/';
$pathToThumbs = 'thumbnails/';
$thumbWidth = 200;
$log = KLogger::instance('.',KLogger::INFO);


function delete_folder($path)
{
	global $log;
	$log->logInfo("About to delete folder " . $path);
	$files = glob($path . '*'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file)) 
			unlink($file); // delete file
	}
}

function images_delete( $request ) 
{	
	global $pathToImages;
	global $pathToThumbs;
	delete_folder($pathToThumbs);
	delete_folder($pathToImages);
}

function images_get( $request){
	global $pathToImages;
	global $pathToThumbs;
	global $thumbWidth;
	create_thumbs($pathToImages, $pathToThumbs, $thumbWidth);
	create_gallery($pathToImages, $pathToThumbs);
}

function images_error($request){
	echo "Error 501";
}

function create_thumbs( $pathToImages, $pathToThumbs, $thumbWidth ) 
{
	global $log;
 

	$log->logInfo('About to create thumbs');
	// open the directory
	$dir = opendir( $pathToImages );

	//Find all existing thumbs
	$existingThumbs = glob($pathToThumbs . '*.jpg');
	$log->logDebug("Thumbs found in thumbs folder " . implode(',',$existingThumbs));


 	 // loop through it, looking for any/all JPG files:
	while (false !== ($fname = readdir( $dir ))) {
		if (in_array($pathToThumbs . $fname,$existingThumbs)){
			$log->logDebug("Thumbnail with name " . $fname . " already exists. Skipping");
			//The thumbnail was already created
			continue;
		}
   		 // parse path for the extension
  		$info = pathinfo($pathToImages . $fname);
    		// continue only if this is a JPEG image
    		if ( strtolower($info['extension']) == 'jpg' ) 
    		{
      			// load image and get image size
      			$img = imagecreatefromjpeg( "{$pathToImages}{$fname}" );
      			$width = imagesx( $img );
      			$height = imagesy( $img );

      			// calculate thumbnail size
      			$new_width = $thumbWidth;
      			$new_height = floor( $height * ( $thumbWidth / $width ) );

			// create a new temporary image
      			$tmp_img = imagecreatetruecolor( $new_width, $new_height );

      			// copy and resize old image into new image 
      			imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

      			// save thumbnail into a file
			imagejpeg( $tmp_img, "{$pathToThumbs}{$fname}" );
			$log->logDebug('Creating thumb with name ' . $fname);
    		}
  	}
  	// close the directory
  	closedir( $dir );
}
class Image{
	public $thumbnail;
	public $image;
}

function create_gallery( $pathToImages, $pathToThumbs ) 
{
	$images = [];
	$dir = opendir( $pathToThumbs );
	$counter = 0;
	while (false !== ($fname = readdir($dir)))
	{
		$info = pathinfo($pathToImages . $fname);
    		// continue only if this is a JPEG image
    		if ( strtolower($info['extension']) == 'jpg' && $fname != '.' && $fname != '..') 
		{
			$img = new Image();
			$img->image = $pathToImages . $fname;
			$img->thumbnail = $pathToThumbs . $fname;
			$counter += 1;
			array_push($images,$img);
		}
	}
	closedir( $dir );

	echo json_encode($images);
}


$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/",substr(@$_SERVER['PATH_INFO'],1));
switch ($method){
	case 'GET':
		images_get($request);
		break;
	case 'DELETE':
		images_delete($request);
		break;
	default:
		images_error($request);
		break;
	}



?>
