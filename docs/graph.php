<?php

    //Google Chart API, SSL and Microsoft Internet Explorer Workaround

    // We're using the Google Chart API to create images representing our regional numbers.
    // We need to download and store the image instead of using an <img> tag because
    // Internet Explorer browsers throw confusing warnings when that is done from
    // an SSL directory.

    // Use the php microtime() function to generate the filename
    // to avoid duplicate filenames when the application is under heavy load.
    list ($usec, $sec) = explode(" ", microtime());
    $filename_pie_chart = 'dyna_' . $sec . $usec . '.png';
    $baseline_pic = "cf";
    // Build the url to retrieve the chart image.
 //"http://chart.apis.google.com/chart?cht=p&chs=500x250&chtt=Regional+Representation|All+States&chd=t:$data&chl=$label";?=
    $url = "http://chart.apis.google.com/chart?cht=lxy&chdl=Daily|Baseline&chs=120x80&chco=ff0020,00ff00,0000ff,000000&chd=t:10,20,30,40,50|20,30,20,30,40|20,30,40,50,60|70,80,70,80,60";

    // Use the unix utility curl to retrieve the image and store it locally.
// curl needs  a gateway through the firewall.
    system("/usr/bin/curl --silent --output $baseline_pic/$filename_pie_chart --url \"$url\"");
//debug line    system("/usr/bin/curl  --output $baseline_pic/$filename_pie_chart --url \"$url\"");

    // The variable that will be used to display the pie chart.
    $reg_rep_pie_chart = "<img src=\"$baseline_pic/$filename_pie_chart\" alt=\"Regional Representation\" />";
echo $reg_rep_pie_chart;
?>


