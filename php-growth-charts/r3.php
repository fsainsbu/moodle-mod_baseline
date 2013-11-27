<?php
include_once('3Chart.class.php');
import_request_variables('g', 'rl_');

$url_style = htmlentities($rl_style);
$url_title = htmlentities($rl_title);
$url_missing = htmlentities($rl_missing);
if (isset($rl_width) ) $url_width = htmlentities($rl_width);
if (isset($rl_height) ) $url_height = htmlentities($rl_height);
if (isset($rl_xvals) ) $url_xvals = htmlentities($rl_xvals);
if (isset($rl_yvals) ) $url_yvals = htmlentities($rl_yvals);
$style = null;
$title = null;
$missing = null;
$width = null;
$height = null;

// check for minimum required variables
if (!isset($url_style) || !isset($url_title) || !isset($url_missing))
{
	die('One or more expected request variables not present.');
}

$style = $url_style;

// check title value
//if (!is_alpha($url_title) || $url_title < 1 || $url_title > 2)
//{
//	die('title value out of range: ' . $url_title);
//}

$title = $url_title;

// check maximum age value
if (!is_numeric($url_missing) || $url_missing < 0)
{
	die('Maximum age value out of range: ' . $url_missing);
}

$missing = $url_missing;

// check image width
if (isset($url_width) && !is_numeric($url_width))
{
	die('Image width value out of range: ' . $url_width);
}

$width = (isset($url_width) ? $url_width : 800);

// check image height
if (isset($url_height) && !is_numeric($url_height))
{
	die('Image height value out of range: ' . $url_height);
}

$height = (isset($url_height) ? $url_height : 800);


$patientXarray = null;
$patientYarray = null;

// check patient data values
// expecting comma-separated lists of x coordinates and y coordinates
if (isset($url_xvals) && isset($url_yvals))
{

	$paX = split(',', $url_xvals);
	$paY = split(',', $url_yvals);
	
	if (sizeof($paX) == sizeof($paY))
	{
		$okay = true;
		foreach ($paX as $value)
		{
			if (!is_numeric($value))
				$okay = false;
		}
		
		foreach ($paY as $value)
		{
			if (!is_numeric($value))
				$okay = false;
		}
		
		if ($okay)
		{
			$patientXarray = $paX;
			$patientYarray = $paY;
		}
	}
}

$chart = new RingChart($style, $title, $missing, $width, $height, $patientXarray, $patientYarray);

$chart->render();


?>
