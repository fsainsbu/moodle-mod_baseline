<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_utils.inc.php');
import_request_variables('g', 'url_');

$title= htmlentities($url_title);
$xvals= htmlentities($url_xvals);
$yvals= htmlentities($url_yvals);
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
                        //line
                        $data6y = $paX;
                        $data1y = $paY;
                }
        }
}

require_once ('jpgraph/jpgraph_bar.php');
 
$l1datay = $data1y;
$l2datay =  $data6y;
 
$datax=$gDateLocale->GetShortMonth();
 
// Create the graph. 
$graph = new Graph(400,200);    
$graph->SetScale("textlin");
$graph->SetMargin(40,130,20,40);
$graph->SetShadow();
#$graph->xaxis->SetTickLabels($datax);
 
// Create the linear error plot
$l1plot=new LinePlot($l1datay);
$l1plot->SetColor("red");
$l1plot->SetWeight(2);
$l1plot->SetLegend("Diary");
 
//Center the line plot in the center of the bars
$l1plot->SetBarCenter();
 
 
// Create the bar plot
$bplot = new BarPlot($l2datay);
$bplot->SetFillColor("orange");
$bplot->SetLegend("Baseline");
 
// Add the plots to t'he graph
$graph->Add($bplot);
$graph->Add($l1plot);
 
 
$graph->title->Set( ucwords($title)." Trend");
$graph->xaxis->title->Set("Days");
$graph->yaxis->title->Set("Better");
 
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
 
 
// Display the graph
$graph->Stroke();
?>
