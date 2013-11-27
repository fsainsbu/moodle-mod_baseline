<?php // content="text/plain; charset=utf-8"
// $Id
// Example of pie with center circle
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_pie.php");

 
// Some data
$data = array(50,28,25);
 
// A new pie graph
$graph = new PieGraph(300,300,'auto');
 
// Setup title
$graph->title->Set("Pie plot with center circle");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);
$graph->title->SetMargin(8); // Add a little bit more margin from the top
 
// Create the pie plot
$p1 = new PiePlotC($data);
$p1->SetSliceColors(array('green','red','orange'));
 
// Set size of pie
$p1->SetSize(0.32);
$p1->SetMidSize(0.80);
//Specify size for center circle as fraction of the radius. 
 
// Label font and color setup
$p1->value->SetFont(FF_ARIAL,FS_BOLD,10);
$p1->value->SetColor('black');
 
// Setup the title on the center circle
$p1->midtitle->Set("Days\n no\n data");
$p1->midtitle->SetFont(FF_ARIAL,FS_NORMAL,10);
 
// Set color for mid circle
$p1->SetMidColor('white');
 
// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);
 
// Add plot to pie graph
$graph->Add($p1);
 
// .. and send the image on it's merry way to the browser
$graph->Stroke();
 
?>

