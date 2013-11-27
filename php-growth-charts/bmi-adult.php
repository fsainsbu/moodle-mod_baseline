<?php
include ("jpgraph/jpgraph.php");
include ("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_mgraph.php");
require_once ('jpgraph/jpgraph_utils.inc.php');
import_request_variables('g', 'url_');

$heightcm= htmlentities($url_height);
$xvals= htmlentities($url_xvals);
$yvals= htmlentities($url_yvals);
// check for minimum required variables
if (!isset($heightcm) || !isset($xvals) || !isset($yvals))
{
echo $heightcm;
        die('One or more expected request variables not present.');
}


// check height value
if (!is_numeric($heightcm) || $heightcm < 40|| $heightcm > 300)
{
        die('height value out of range: ' . $heightcm);
}
$url_xvals=$xvals;
$url_yvals=$yvals;

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
                        $datay6 = $paX;
                        $patientYarray = $paY;
                }
        }
}


// Pass in heights and weights or just bmi?
//$datay2 = 	array( 4,5,4,5,6,5,7,4,7,4,4,3,2,4,1,2,2,1);
//$datay3 = 		array(4,5,7,10,13,15,15,22,26,26,30,34,40,43,47,55,60,62);
//$datay4 =   array (70,70,70,70,70,70,70,70,70,70,70,70,70,70,70,70,70,70);
//$heightcm=185;
$height= $heightcm/100;
$sheiht=$height*$height;
$d1= 18.5 * $sheiht;
$d2= 20 * $sheiht -$d1;
$d3= 27 * $sheiht - $d2 -$d1;
$d4=  30 * $sheiht -$d3 -$d2 -$d1;
$s1= floor($d2+$d1);
$s2= ceil($d3+$d2+$d1);
$s3= floor($d4+d3+$d2+$d1);
$s4 =  45 * $sheiht;
$s5 =  15 * $sheiht;
// Goal weigh BMI 23 +- 1
$s6=floor(22 * $sheiht);
$s7=floor(24 * $sheiht);
$datay1 = array($d1,$d1);
$datay2 = array($d2,$d2);
$datay3 = array($d3,$d3);
$datay4 = array($d4,$d4);
$datay5 = array($s5,$s5);
//Line datat for weights:
//$datay5 = 		array(14,15,17,10,13,15,15,22,26,26,30,34,40,43,47,45,40,42);
// Create the graph. These two calls are always required
$graph = new Graph(800,400,"auto");	
$graph->SetScale("textlin",0,$s4);
$graph->SetShadow();
$graph->img->SetMargin(40,30,20,40);

// Create the linear plots for each category
$dplot[] = new LinePLot($datay1);
$dplot[] = new LinePLot($datay2);
$dplot[] = new LinePLot($datay3);
$dplot[] = new LinePLot($datay4);
$dplot[] = new LinePLot($datay5);

$dplot[0]->SetFillColor("red");
$dplot[1]->SetFillColor("yellow");
$dplot[2]->SetFillColor("green");
$dplot[3]->SetFillColor("pink");
$dplot[4]->SetFillColor("orange");
/*
$dplot[] = new LinePLot($datay6);
$dplot[5]->SetColor("black");
$dplot[5]->SetWeight( 4 );   // Two pixel wide
$dplot[5]->mark->SetType(MARK_UTRIANGLE);
$dplot[5]->mark->SetColor('blue');
$dplot[5]->mark->SetFillColor('red');
*/
// Create the accumulated graph
$accplot = new AccLinePlot($dplot);

$graph->Add($accplot);

// Add the plot to the graph
//$graph->addline(20,20,600,20);
//$graph->addline(20,20,600,20);

//$graph->xaxis->SetTextTickInterval(2);
$graph->title->Set("Height $height m Healthy Weight Range: $s1-".$s2."kg Goal Weight ".$s6." - ".$s7." kg");
$graph->xaxis->title->Set("Months");
$graph->yaxis->title->Set("Weight (KG)");

$graph->yaxis->HideTicks();
$graph->yaxis->HideLabels();
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph2 = new Graph(800,400,"auto");	
$graph2->SetScale("textlin",0,$s4);
//$graph2->yaxis->HideTicks();
//$graph2->yaxis->HideLabels();
list($tickPositions,$minTickPositions) = DateScaleUtils::GetTicks($patientYarray);
$graph2->SetShadow();
$graph2->img->SetMargin(40,30,20,40);
 $aplot = new LinePLot($datay6);
 $aplot->SetColor("black");
 $aplot->SetWeight( 4 );   // Two pixel wide
 $aplot->mark->SetType(MARK_UTRIANGLE);
 $aplot->mark->SetColor('blue');
$aplot->mark->SetFillColor('red');
//$graph2->xaxis->SetPos('min');
//$graph2->xaxis->SetTickPositions($tickPositions,$minTickPositions);
//$graph2->xaxis->SetLabelFormatString('My',true);
//$graph2->xaxis->SetFont(FF_ARIAL,FS_NORMAL,9);

$graph2->Add($aplot);
// $lineplot = new LinePlot($aplot);
// Display the graph
//$graph->Stroke();
$mgraph = new MGraph();
$xpos1=3;$ypos1=3;
$xpos2=3;$ypos2=200;
$mgraph->Add($graph,$xpos1,$ypos1);
 $mgraph->AddMix($graph2,$xpos1,$xpos2,50);
$mgraph->Stroke();

?>
