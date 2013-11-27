<?php

include ("jpgraph/jpgraph.php");
include ("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_regstat.php");

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
		$g->SetMargin(125,20,20,95);
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
		 	$this->label .=  ' 2 - 20 Years';
		}
	$data= "data/".$this->style.".xml";
		// echo $data;
		//$xml = simplexml_load_file("data/$this->style.xml");
		$xml = simplexml_load_file($data);
		$xdata = GrowthChart::generateSourceXData((float)$xml->sourceXStart, (float)$xml->sourceXEnd);
//	print_r($xml);
		$g->SetScale("linlin", (float)$xml->yMin, (float)$xml->yMax, (float)$xml->xMin/12, (float)$xml->xMax/12);
	//	$g->SetScale("linlin", (float)$xml->yMin, (float)$xml->yMax, (float)$xml->xMin, (float)$xml->xMax);
		if ((float)$xml->ticksMajor != 0) {
			$g->yscale->ticks->Set((float)$xml->ticksMajor, (float)$xml->ticksMinor);
		}
		$g->xaxis->SetLabelFormat('%1.1f');
		$g->xaxis->SetFont(FF_TREBUCHE, FS_NORMAL, 9);
		$g->xgrid->Show(true);
		$g->yaxis->HideZeroLabel();
		$g->yaxis->SetFont(FF_TREBUCHE, FS_NORMAL, 9);
		$g->ygrid->SetFill(true,'#EFEFEF@0.5','#FFFFFF@0.5');
		//$xml->percentile->label =array_reverse($xml->percentile->label);
		//$xml->label =array_reverse($xml->label);
		//$xml->percentile=$rxml;
	$plots =array();	

		foreach ($xml->percentile as $p) {
/*
Need to reverse order of drawing....
*/
/*
$i++;
		 $txt = new Text($p->label .' '.  $i);
			 //$txt->SetScalePos(0.3+(float)$xml->yMin/2, (float)$xml->xMax * 0.09);
			 $txt->SetScalePos($i* 0.2+(float)$xml->yMin/6, (float)$xml->xMax * 0.09);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
/* */		
			$percentile = $p->label;
			$yp = array();
			
			foreach ($p->value as $value) {
				$yp[] = (float)$value;
			}
			//$yp=array_reverse($yp);
			//$xdata=array_reverse($xdata);
		
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
		 			$lplot->SetFillColor('lightgreen');
					$lplot->SetLegend('Ideal');
					break;
				case 10:
		 			$lplot->SetColor('red');
		 			$lplot->SetFillColor('pink');
					//$lplot->AddArea(2,5,LP_AREA_FILLED,"indianred1");
					$lplot->SetLegend('High nutritional risk');
					break;
				case 25:
		 			$lplot->SetColor('orange');
		 			$lplot->SetFillColor('orange');
					$lplot->SetLegend('Nutritional risk');
					break;
				case 97:
		 			$lplot->SetColor('orange');
		 			$lplot->SetFillColor('orange@0.5');
					$lplot->SetLegend('Nutritional risk');
					break;
				case 98:
		 			$lplot->SetColor('orange');
		 			$lplot->SetFillColor('orange@0.5');
					$lplot->SetLegend('Nutritional risk');
					break;
 				default:  $lplot->SetColor('#CCCCCC');
					break;
			}
			$plots[]=$lplot;
			}
			$plots=array_reverse($plots);
			foreach ( $plots as $lplot) {
                                         $g->Add($lplot);
	}
  foreach ($xml->percentile as $p) {

                        $percentile = $p->label;
                        $yp = array();

                        foreach ($p->value as $value) {

                                $yp[] = (float)$value;
                        }
                        //$yp=array_reverse($yp);
                        //$xdata=array_reverse($xdata);

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
                                        break;
                                case 10:
                                        $lplot->SetColor('red');
                                        //$lplot->AddArea(2,5,LP_AREA_FILLED,"indianred1");
                                        break;
                                case 25:
                                        $lplot->SetColor('orange');
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
/*
		 $txt = new Text('BMI');
			 $txt->SetScalePos(-0.25, (float)$xml->xMax * 0.45);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
		 $txt = new Text('kg/m²');
			 $txt->SetScalePos(-0.25, (float)$xml->xMax * 0.44);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
		 $txt = new Text('Age (Years)');
			 $txt->SetScalePos((float)$xml->yMax/24 ,  0.45);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
		 /*$txt = new Text($this->style."  ".$data);
			 $txt->SetScalePos(0.5+(float)$xml->yMax/24, (float)$xml->xMax/10);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
			*/
	$g->yaxis->SetTitleMargin(50);
	$g->yaxis->title->Set("BMI\nkg/m²");
	$g->yaxis->title->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
	$g->yaxis->title->SetAngle(0);
	$g->title->Set($this->label);
	$g->title->SetFont(FF_TREBUCHE, FS_NORMAL, 12);

	$g->xaxis->title->Set("Age (Years)");
	$g->xaxis->title->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
/*
		 $txt = new Text("$this->label");
			 $txt->SetScalePos(0.1,$yp[sizeof($yp)-1]+(float)$xml->percentileYNudge);
			 $txt->SetColor('black');
                        $txt->SetFont(FF_TREBUCHE, FS_NORMAL, 12);
                        $g->AddText($txt);
*/
	//fps change to years	

		$g->legend->SetLayout(LEGEND_HOR);
		$g->legend->Pos(0.52, 0.85, 'center'); 
		
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
/*
print <<<EOS
<html>
<head>
<title>$page_title</title>
</head>
<body>
<div style="text-align: center">
<h3>$page_title</h3>
<img src="{$jpgcache}{$graph_name}" />
<p />
<table border="1" cellpadding="5" cellspacing="0">
<tr align="center"><th>Data</th>$row_head</tr>
<tr align="right"><td><b>Plan</b></td>$row_plan</tr>
<tr align="right"><td><b>Actual</b></td>$row_act</tr>
<tr align="right"><td><b>Forecast</b></td>$row_fcst</tr>
</table>
</div>
</body> 
</html>
EOS; 
*/
	}
}

?>
