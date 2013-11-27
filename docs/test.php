<?php 
function display_graph() {
    list ($usec, $sec) = explode(" ", microtime());
    $filename_pie_chart = 'dyna_' . $sec . $usec . '.png';
    $baseline_dir = $CFG->dirroot.'/mod/baseline/cf';
    $baseline_pic = $CFG->httpswwwroot.'/mod/baseline/cf';
    // Build the url to retrieve the chart image.
 //"http://chart.apis.google.com/chart?cht=p&chs=500x250&chtt=Regional+Representation|All+States&chd=t:$data&chl=$label";?=
    $url = "http://chart.apis.google.com/chart?cht=lxy&chdl=Daily|Baseline&chs=120x80&chco=ff0020,00ff00,0000ff,000000&chd=t:10,20,30,40,50|20,30,20,30,40|20,30,40,50,60|70,80,70,80,60";

    // Use the unix utility curl to retrieve the image and store it locally.
// curl needs  a gateway through the firewall.
    system("/usr/bin/curl --silent --output $baseline_dir/$filename_pie_chart --url \"$url\"");
//debug line    $str .= "xxxecho $baseline_pic/$filename_pie_chart";
//system("/usr/bin/curl  --output $baseline_dir/$filename_pie_chart --url \"$url\"");

    // The variable that will be used to display the pie chart.
    $reg_rep_pie_chart = "<img src=\"$baseline_pic/$filename_pie_chart\" alt=\"".$this->field->name."\" />";
//xxxecho $reg_rep_pie_chart;

          $str .= $reg_rep_pie_chart;
      return $str;
}
?>
