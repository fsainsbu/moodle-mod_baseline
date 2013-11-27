<?php

include ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_pie.php");
require_once ("jpgraph/jpgraph_pie3dc.php");
include ("jpgraph/jpgraph_regstat.php");

/**
 * RingChart class
 * 
 * @author Jonathan Abbett <jonathan.abbett@childrens.harvard.edu>
 * @version 1.1
 * @copyright Jonathan Abbett and Children's Hospital Informatics Program, 2007
 * Modified by ITSOIL Pty Ltd to print ring graphs for TCFCF
 */
class RingChart
{
	/**
	 * Male title
	 *
	 */
	const title_MALE			= 1;
	/**
	 * Female title
	 *
	 */
	const title_FEMALE		= 2;

	private $style;
	private $title;
	private $maxAgeMonths;
	private $patientXarray;
	private $patientYarray;
	private $width;
	private $height;

	/**
	 * Constructor, used to initialize necessary variables.
	 *
	 * @param string $style Chart style, from available dataset filenames, i.e. bmi-age, weight-length
	 * @param integer $title Patient title, from available title constants
	 * @param decimal $maxAgeMonths The greatest patient age used in the chart, used to decide whether chart is infant (0-36 mo.) or regular (2-20 yrs.)
	 * @param integer $width Width of chart in pixels
	 * @param integer $height Height of chart in pixels
	 * @param array $patientXarray Array of decimals for patient X data (i.e. age in months)
	 * @param array $patientYarray Array of decimals for patient Y data (i.e. length, height, BMI, etc.)
	 * @return RingChart
	 */
	public function RingChart($style, $title, $maxAgeMonths, $width = 800, $height = 800, $patientXarray = null, $patientYarray = null)
	{
		$this->style = $style;
		$this->title = $title;
		$this->maxAgeMonths = $maxAgeMonths;
		$this->width = $width;
		$this->height = $height;
		$this->patientXarray = $patientXarray;
		$this->patientYarray = $patientYarray;
	}
	
	private static function generateSourceXData($min, $max) {
		
		$data = array();
		
		$data[] = $min;
		
		for ($i = $min + 0.5; $i < $max; $i++) {
			$data[] = $i;
		}
		
		$data[] = $max;
		
		return $data;
		
	}
	
	/**
	 * Renders the chart, outputting a PNG image.
	 *
	 */
	public function render()
	{
 $data =  $this->patientXarray;
$missing = $this->maxAgeMonths/120;
		// Create and set-up the graph
// A new pie graph
$graph = new PieGraph(300,300,'auto');
 
// Setup title
$graph->title->set($this->title);
$graph->title->SetFont(FF_FONT1,FS_BOLD,14); 
$graph->title->SetMargin(8); // Add a little bit more margin from the top
 
// Create the pie plot
$p1 = new PiePlot3DC($data);
$p1->SetSliceColors(array('green','orange','red'));
$p1->SetLegends(array('better','same','worse')); 
// Set size of pie
$p1->SetSize(0.32);
$p1->SetMidSize($missing);
//Specify size for center circle as fraction of the radius.
 
// Label font and color setup
$p1->value->SetFont(FF_FONT1,FS_BOLD,10);
$p1->value->SetColor('black');
 
// Setup the title on the center circle
$missper =  $this->maxAgeMonths;
$p1->midtitle->Set("$missper % Days\n no\n data");
$p1->midtitle->SetFont(FF_FONT1,FS_NORMAL,8);
 
// Set color for mid circle
$p1->SetMidColor('white');
 
// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);
 
// Add plot to pie graph
$graph->Add($p1);
 
// .. and send the image on it's merry way to the browser
$graph->Stroke();
 

		
		if (!empty($this->patientXarray) && !empty($this->patientYarray) && sizeof($this->patientXarray) == sizeof($this->patientYarray))
		{
 			echo "here";
		}

	}
}

