<?php

include ("jpgraph-3.0.7/src/jpgraph.php");
include ("jpgraph-3.0.7/src/jpgraph_line.php");
include ("jpgraph-3.0.7/src/jpgraph_regstat.php");

/**
 * GrowthChart class
 * 
 * @author Jonathan Abbett <jonathan.abbett@childrens.harvard.edu>
 * @version 1.1
 * @copyright Jonathan Abbett and Children's Hospital Informatics Program, 2007
 *
 */
class GrowthChart
{
	/**
	 * Male sex
	 *
	 */
	const SEX_MALE			= 1;
	/**
	 * Female sex
	 *
	 */
	const SEX_FEMALE		= 2;

	private $style;
	private $sex;
	private $maxAgeMonths;
	private $patientXarray;
	private $patientYarray;
	private $width;
	private $height;

	/**
	 * Constructor, used to initialize necessary variables.
	 *
	 * @param string $style Chart style, from available dataset filenames, i.e. bmi-age, weight-length
	 * @param integer $sex Patient sex, from available SEX constants
	 * @param decimal $maxAgeMonths The greatest patient age used in the chart, used to decide whether chart is infant (0-36 mo.) or regular (2-20 yrs.)
	 * @param integer $width Width of chart in pixels
	 * @param integer $height Height of chart in pixels
	 * @param array $patientXarray Array of decimals for patient X data (i.e. age in months)
	 * @param array $patientYarray Array of decimals for patient Y data (i.e. length, height, BMI, etc.)
	 * @return GrowthChart
	 */
	public function GrowthChart($style, $sex, $maxAgeMonths, $width = 800, $height = 800, $patientXarray = null, $patientYarray = null)
	{
		$this->style = $style;
		$this->sex = $sex;
		$this->maxAgeMonths = $maxAgeMonths;
		$this->width = $width;
		$this->height = $height;
		$this->patientXarray = $patientXarray;
		$this->patientYarray = $patientYarray;
	}
	
	private static function generateSourceXData($min, $max) {
		
		$data = array();
		
		$data[] = $min/12;
		
		for ($i = $min + 0.5; $i < $max; $i++) {
		//for ($i = $max ; $i > $min + 0.5; $i--) {
			$data[] = $i/12;
		}
		
		$data[] = $max/12;
		
		return $data;
		
	}
	
	/**
	 * Renders the chart, outputting a PNG image.
	 *
	 */
	public function render()
	{

		// Create and set-up the graph
		$g  = new Graph($this->width, $this->height, "auto");
		$g->SetColor('orange@.6');
		$g->SetFrame(false);
		$g->SetMargin(25,20,20,25);
		$g->SetMarginColor('white');

		// Load data from XML
		if ($this->sex == GrowthChart::SEX_MALE) {
			$this->style .= '-male';
			$this->label = 'Boys-Body Mass Index (BMI) Chart';
		} else {
			$this->style .= '-female';
			$this->label = 'Girls-Body Mass Index (BMI) Chart';
		}
		
		if ($this->maxAgeMonths <= 36) {
			$this->style .= '-infant';
			 $this->label .=  ' 0 - 2 Years ';
		} else {
		 	$this->label .=  ' 2 - -20 Years';
		}
		 $txt = new Text($this->label);
			 //$txt->SetScalePos(0.2+(float)$xml->yMin/12, 15+(float)$xml->xMax/12);
			 $txt->SetScalePos(0.1+(float)$xml->yMin/12, 15+(float)$xml->xMax/12);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);

		
		$xml = simplexml_load_file("data/$this->style.xml");
		
		$xdata = GrowthChart::generateSourceXData((float)$xml->sourceXStart, (float)$xml->sourceXEnd);
	//fps change to years	
		$g->SetScale("linlin", (float)$xml->yMin, (float)$xml->yMax, (float)$xml->xMin/12, (float)$xml->xMax/12);
		//$g->SetScale("linlin", (float)$xml->yMin, (float)$xml->yMax, (float)$xml->xMin, (float)$xml->xMax);
		if ((float)$xml->ticksMajor != 0) {
			$g->yscale->ticks->Set((float)$xml->ticksMajor, (float)$xml->ticksMinor);
		}
		$g->xaxis->SetLabelFormat('%1.1f');
		$g->xaxis->SetFont(FF_TREBUCHE, FS_NORMAL, 9);
		$g->xgrid->Show(true);
		$g->yaxis->HideZeroLabel();
		$g->yaxis->SetFont(FF_TREBUCHE, FS_NORMAL, 9);
		$g->ygrid->SetFill(true,'#EFEFEF@0.5','#FFFFFF@0.5');

		foreach ($xml->percentile as $p) {
		
			$percentile = $p->label;
			$yp = array();
			
			foreach ($p->value as $value) {
				$yp[] = (float)$value;
			}
		
			// Create the spline
			$spline = new Spline($xdata, $yp);

			// Get smoothed points
			list($newx, $newy) = $spline->Get(100);

			$lplot = new LinePlot($newy, $newx);
		// Set colours for shading..	
			switch ($percentile) {
				case 50:
		 			$lplot->SetColor('#666666');
					break;
				case 95:
		 			$lplot->SetColor('green');
		 			$lplot->SetFillColor('green@0.5');
					break;
				case 10:
		 			$lplot->SetColor('red');
		 			$lplot->SetFillColor('red@0.5');
					//$lplot->AddArea(2,5,LP_AREA_FILLED,"indianred1");
					break;
				case 25:
		 			$lplot->SetColor('green');
		 			$lplot->SetFillColor('orange@0.5');
					break;
				case 98:
		 			$lplot->SetColor('orange');
					break;
 				default:  $lplot->SetColor('#CCCCCC');
					break;
			}
/*	
			$lplot->SetColor('#CCCCCC');

			if ($percentile == '50')
			{
				$lplot->SetColor('#666666');
			}
*/
			// Add the plots to the graph and stroke
			$g->Add($lplot);
			// Add percentile label to graph
			$txt = new Text($percentile . ($percentile == '3' ? 'rd' : 'th'));
			$txt->SetScalePos(($xdata[sizeof($xdata)-1]+(float)$xml->percentileXNudge/12),$yp[sizeof($yp)-1]+(float)$xml->percentileYNudge);
			//$txt->SetScalePos(($xdata[sizeof($xdata)-1]-(float)$xml->percentileXNudge)/3,$yp[sizeof($yp)-1]+(float)$xml->percentileYNudge);
			$txt->SetColor('#666666');
			$txt->SetFont(FF_TREBUCHE, FS_NORMAL, 9);
			$g->AddText($txt);
		}
		 $txt = new Text('kg/m²');
			 //$txt->SetScalePos(0.2+(float)$xml->yMin/12, 15+(float)$xml->xMax/12);
			 $txt->SetScalePos(0.1+(float)$xml->yMin/12, 10+(float)$xml->xMax/24);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
			//SuperScriptText('kg/m',2,0,10);
		 $txt = new Text('Age (Years');
			 $txt->SetScalePos(0.1+(float)$xml->yMax/24, (float)$xml->xMax/12);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
		
		if (!empty($this->patientXarray) && !empty($this->patientYarray) && sizeof($this->patientXarray) == sizeof($this->patientYarray))
		{
			$patientPlot = new LinePlot($this->patientYarray, $this->patientXarray);
			$patientPlot->SetColor('orange');
			$patientPlot->SetWeight(3);
			//$patientPlot->value->Show();
//  Hide the values  set above in Show, oppisite
			$patientPlot->value->SetColor('brown');
			$patientPlot->value->SetFont(FF_COURIER, FS_BOLD);
			$patientPlot->value->SetAlign('left', 'top');
			$patientPlot->value->SetMargin(-5);
			$patientPlot->mark->SetType(MARK_DIAMOND);
			$patientPlot->mark->SetWidth(7);
			$patientPlot->mark->SetColor('orange');
			$patientPlot->mark->SetFillColor('red');
			
			$g->Add($patientPlot);
		}

		$g->Stroke();
	}
}

?>
