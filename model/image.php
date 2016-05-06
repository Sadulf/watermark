<?php
/**
 *  image generator
 */
namespace image;

require_once __DIR__.'/imgwork.php';

// get request params
if(isset($_GET['align']))
	$align = $_GET['align'];
else
	$align = 'rb';


$r=\imgwork\png_mark(__DIR__.'/../slide1.jpg',null,__DIR__.'/../mark.png',$align); 

if($r<=0)
	trigger_error('marker_error #'.(-$r),E_USER_ERROR);

?>