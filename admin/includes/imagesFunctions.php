<?php

function getResized($imgFilename,$width,$height){
	$cachedFilename = getFromConfig('cacheddirectory').'/'.getResizedFilename($imgFilename,$width,$height);
	if (!file_exists($cachedFilename)) {
		resize($imgFilename,$_SERVER['DOCUMENT_ROOT'].'/'.$cachedFilename,$width,$height);
	}
	return $cachedFilename;
}

function resize($imgOriFilename,$imgDestFilename,$width,$height){
	if (!is_string($createFunction = getImageCreateFunctionFromFile($imgOriFilename))) return false;
	if (!is_string($writeFunction = getImageWriteFunctionFromFile($imgOriFilename))) return false;
	$oriImg = $createFunction($imgOriFilename);
	$oriWidth = imagesx($oriImg);
	$oriHeight = imagesy($oriImg);

	if ($oriWidth/$oriHeight>$width/$height) {
		$height=$oriHeight/$oriWidth*$width;
	}
	else {
		$width=$oriWidth/$oriHeight*$height;
	}

	$destImg = imagecreatetruecolor($width, $height);
	imageinterlace($destImg, true); 
	imagecopyresized($destImg, $oriImg, 0,0,0,0, $width, $height, $oriWidth, $oriHeight);

	return $writeFunction($destImg,$imgDestFilename);
}

function getResizedFilename($imgFilename,$width,$height) {
	$ext = getExtension($imgFilename);
	$algo = getFromConfig('cachehashalgo');
	$hash = hash($algo,$imgFilename);
	return $hash.'-'.$width.'x'.$height.'.'.$ext;
}

function getImageCreateFunctionFromFile($filename){
	$type2function = array(
		IMAGETYPE_GIF=> 'imagecreatefromgif',
		IMAGETYPE_JPEG=> 'imagecreatefromjpeg',
		IMAGETYPE_PNG=> 'imagecreatefrompng',
		);
	$type = exif_imagetype($filename);
	return array_key_exists($type,$type2function)?$type2function[$type]:false;
}

function getImageWriteFunctionFromFile($filename){
	$type2function = array(
		IMAGETYPE_GIF=> 'imagegif',
		IMAGETYPE_JPEG=> 'imagejpeg',
		IMAGETYPE_PNG=> 'imagepng',
		);
	$type = exif_imagetype($filename);
	return array_key_exists($type,$type2function)?$type2function[$type]:false;
}