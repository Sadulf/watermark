<?php
/**
 * module imgwork
 * function convert_image_relative_inbox($source,$dest=null,$dest_w=false,$dest_h=false,$dest_format=false, $force_resize = false)
 * function png_mark($source,$dest,$mark, $align = 'rb',$indent_x = 10, $indent_y = 10)
 */
namespace imgwork;

/**
 * Proportionally scales the image, resulting in one of the sizes for a given value (the second size is calculated respectively and always less than  specified). If both actual sizes of the image is less than or equal to a determined sizes to resize - scaling is not performed. If $force_resize = true - an increase instead of decrease can be made as needed.
 * @param  string  $source       source file
 * @param  string  $dest         destination file or NULL to return the image recource as the return value
 * @param  integer $dest_w       target width dimension
 * @param  integer $dest_h       target height dimension
 * @param  integer $dest_format  target format: IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG or FALSE if you want to use format of source file
 * @param  boolean $force_resize permission to increase the image
 * @return integer if successful returns 1 or image resource. On errors return integer <= 0.
 */
function convert_image_relative_inbox($source,$dest=null,$dest_w=false,$dest_h=false,$dest_format=false, $force_resize = false){
	if($dest_w==0 || $dest_h==0)
		return -1;

	// obtain information about the source file
	$ii=getimagesize($source);
	if($ii===false)
		return -2;

	// check to see if the format is supported
	if($ii[2]!=1 && $ii[2]!=2 && $ii[2]!=3)
		return -3;
	if($dest_format===false)
		$dest_format = $ii[2];
	if($dest_format!=IMAGETYPE_GIF && $dest_format!=IMAGETYPE_JPEG && $dest_format!=IMAGETYPE_PNG)
		return -4;

	// scaling calculation
	if($ii[0]<=$dest_w && $ii[1]<=$dest_h && !$force_resize)
		// image is smaller than required. Will not scale.
		$ri = 1;
	else{
		// We calculate the size to which the image should be reduced
		$rwi = $ii[0] / $dest_w;
		$rhi = $ii[1] / $dest_h;
		if($rwi >= $rhi){
			$ri = $rwi;
			$dest_h = round($ii[1] / $ri);
		}else{
			$ri = $rhi;
			$dest_w = round($ii[0] / $ri);
		}
	}

	// If scaling and format change is not required - just copy image
	if($ri==1.0 && $ii[2]==$dest_format){
		if($dest===false){
			// to return the image resource, we must load it into memory
		}elseif($source==$dest)
			return 1;
		else{
			if(false===copy($source,$dest)){return -5;}
			return 1;
		}
	}

	// Load original image into memory
	if($ii[2]==1)
		$in=imagecreatefromgif($source);
	elseif($ii[2]==2)
		$in=imagecreatefromjpeg($source);
	elseif($ii[2]==3)
		$in=imagecreatefrompng($source);
	else
		return -6;
	if($in===false)
		return -7;
	

	if($ri==1.0 && is_null($dest))
		return $in;
	

	// if we only need to change the format - simplify work
	if($ri==1.0 && !is_null($dest)){
		if($dest_format==IMAGETYPE_JPEG)
			if(false===imagejpeg($in,$dest,90))
				return -8;
		elseif($dest_format==IMAGETYPE_PNG)
			if(false===imagepng($in,$dest,9))
				return -9;
		elseif($dest_format==IMAGETYPE_GIF)
			if(false===imagegif($in,$dest))
				return -10;
		
		imagedestroy($in);
		return 1;
	}

	// create a target image
	$out=imagecreatetruecolor($dest_w, $dest_h);
	if($out===false)
		return false;

	if(false===imagealphablending($out,false))
		return -11;

	// scale
	if(false===imagecopyresampled($out, $in, 0, 0, 0, 0, $dest_w, $dest_h, $ii[0], $ii[1]))
		return -12;

	// free memory from the source image
	imagedestroy($in);

	// save the scaled image
	if(is_null($dest))
		return $out;
	elseif($dest_format==IMAGETYPE_JPEG)
		if(false===imagejpeg($out,$dest,90))
			return -13;
	elseif($dest_format==IMAGETYPE_PNG)
		if(false===imagepng($out,$dest,9))
			return -14;
	elseif($dest_format==IMAGETYPE_GIF)
		if(false===imagegif($out,$dest))
			return -15;
	
	// free memory from the target image
	imagedestroy($out);
	return 1;
}


/**
 * Apply watermark on image
 * @param  string  $source   source file name
 * @param  string  $dest     destination file name OR NULL for direct output.
 * @param  string  $mark     watermark file name
 * @param  string  $align    Alignment of watermark: rb - right bottom, rt - right top, lb - left bottom, lt - left top, c - center. Add letter 's' to enable auto-resize of watermark.
 * @param  integer $indent_x indent from edge of image to watermark in pixels on the X axis.
 * @param  integer $indent_y indent from edge of image to watermark in pixels on the Y axis.
 * @return integer           1 if operation succesfull or <=0 if it fails.
 */
function png_mark($source,$dest,$mark, $align = 'rb',$indent_x = 10, $indent_y = 10){

	// get information about source files
	$ii=getimagesize($source);
	$im=getimagesize($mark);
	if(is_bool($ii) || is_bool($im))
		return -1;

	// check to see if the format is supported
	if($ii[2]!=1 && $ii[2]!=2 && $ii[2]!=3)
		return -2;
	if($im[2]!=1 && $im[2]!=2 && $im[2]!=3)
		return -3;

	// Load original image
	if($ii[2]==1)
		$in=imagecreatefromgif($source);
	elseif($ii[2]==2)
		$in=imagecreatefromjpeg($source);
	elseif($ii[2]==3)
		$in=imagecreatefrompng($source);
	else
		return -4;

	if($in === false)
		return -5;

	$out=imagecreatetruecolor($ii[0], $ii[1]);
	if($out === false)
		return -6;

	// coordinate calculation
	if($align=='rbs' || $align=='rts' || $align=='lbs' || $align=='lts' || $align=='cs'){
		$mrk = convert_image_relative_inbox($mark,null,$ii[0]-($indent_x*2),$ii[1]-($indent_y*2),false, true);
		if(is_numeric($mrk))
			return -7;
		$im[0] = imagesx($mrk);
		$im[1] = imagesy($mrk);
		if($im[0] === false || $im[1] === false)
			return -8;
		$sized = true;
	}else
		$sized = false;

	switch ($align) {
		case 'rb':
		case 'rbs':
			$xy=array(
				'x'=>$ii[0]-$im[0]-$indent_x,
				'y'=>$ii[1]-$im[1]-$indent_y);
			break;
		case 'rt':
		case 'rts':
			$xy=array(
				'x'=>$ii[0]-$im[0]-$indent_x,
				'y'=>$indent_y);
			break;
		case 'lb':
		case 'lbs':
			$xy=array(
				'x'=>$indent_x,
				'y'=>$ii[1]-$im[1]-$indent_y);
			break;
		case 'lt':
		case 'lts':
			$xy=array(
				'x'=>$indent_x,
				'y'=>$indent_y);
			break;
		case 'c':
		case 'cs':
			$xy=array(
				'x'=>floor(($ii[0]/2)-($im[0]/2)),
				'y'=>floor(($ii[1]/2)-($im[1]/2)));
			break;
		default:
			return -16;
	}


	// Load watermark
	if($sized){
		// it`s already loaded
	}elseif($im[2]==1)
		$mrk=imagecreatefromgif($mark);
	elseif($im[2]==2)
		$mrk=imagecreatefromjpeg($mark);
	elseif($im[2]==3)
		$mrk=imagecreatefrompng($mark);
	else
		return -9;
	if($mrk === false)
		return -10;

	// Apply watermark on image
	if(false===imagecopy($out, $in, 0, 0, 0, 0, $ii[0],$ii[1]))
		return -11;
	imagedestroy($in);
	if(false===imagecopy($out, $mrk, $xy['x'], $xy['y'], 0, 0, $im[0],$im[1]))
		return -12;
	imagedestroy($mrk);

	// Save the image
	if($ii[2]==1){
		if(is_null($dest))
			header('Content-Type: image/jpeg');
		if(false===imagejpeg($out,$dest,90))
			return -13;
	}elseif($ii[2]==2){
		if(is_null($dest))
			header('Content-Type: image/png');
		if(false===imagepng($out,$dest,9))
			return -14;
	}elseif($ii[2]==3){
		if(is_null($dest))
			header('Content-Type: image/gif');
		if(false===imagegif($out,$dest))
			return -15;
	}

	// Free memory
	imagedestroy($out);
	return 1;
}

?>