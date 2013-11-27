<?php
	include('GrowthChart.class.php');

	$patientXData = array(2,  4,  6,  8, 10, 12);
	$patientYData = array(36, 40, 42.2, 44, 45, 45.8);

	$xvals = join(',', $patientXData);
	$yvals = join(',', $patientYData);
	
?>
<html>
<head>
	<title> myCF  Chart Example</title>
</head>
<body>
<li style="display: block;" id="cough-5" class="5 active"><p>All the time</p></li></ul> </td></tr><tr><td align="right" valign="top"></td></tr><tr class="question 3"> <td class="title"> <h2>Breathlesness</h2><p>When you were at your best health, how breathless did you get?</p></td></tr> <tr>

<table class="ca ca1" border="0" cellpadding="2" cellspacing="0"><tbody>
<tr><td>Worse</td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td class="co1"><img src="/cf3/pix/cf/red.gif"></td><td class="co1 cr"></td></tr>

<tr><td>Baseline</td><td class="co1"><img src="/cf3/pix/cf/amber.gif"></td><td class="co1"></td><td></td><td></td><td></td><td></td><td class="co4 cr"><img src="/cf3/pix/cf/blank.gif"></td></tr>
	<tr><td>Best</td><td class="co1"><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/green.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td class="co4 cr"><img src="/cf3/pix/cf/blank.gif"></td></tr>
<tr class="cb"><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td><img src="/cf3/pix/cf/blank.gif"></td><td class="cr"><img src="/cf3/pix/cf/blank.gif"></td></tr>
<tr class="cl"><td></td><td>Sunday</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td><td class="cr">Saturday</td></tr>
</tbody></table>



	<img src="chart.php?style=head-age&sex=<?php echo GrowthChart::SEX_MALE; ?>&maxage=35&xvals=<?php echo $xvals; ?>&yvals=<?php echo $yvals; ?>" />

<td></td><td class="graph"><img src="http://chart.apis.google.com/chart?cht=lxy&amp;chdl=Daily%7CBaseline&amp;chs=480x320&amp;chco=ff0020,00ff00,0000ff,000000&amp;chd=t:0,29.090909090909,72.727272727273,80,80,109.09090909091,109.09090909091,109.09090909091,109.09090909091,160|8,56,40,56,72,40,40,8,40,8|0,7.2727272727273,7.2727272727273,7.2727272727273,7.2727272727273,36.363636363636,72.727272727273,80,80|88,88,72,40,88,56,24,24" alt="breath"> </td>



<p>
</body>
</html>
