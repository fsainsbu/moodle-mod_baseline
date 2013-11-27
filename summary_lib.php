<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Moodle Pty Ltd    http://moodle.com                //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

//for summary
// will select all data for user for ring, less for table, and different for height.
  require_once(dirname(__FILE__) . '/../../config.php');
    require_once($CFG->dirroot . '/mod/baseline/lib.php');
    require_once($CFG->libdir . '/rsslib.php');
    require_once($CFG->libdir . '/completionlib.php');

//global $DB,$CFG,$USER;
  $CFG->summary    = True;

function  baseline_print_summary($baseline, $summary, $page=0) {

/* issue with no user  */
//FPS why???  $user = $DB->get_record('user',array('id'=>$summary->userid));

$str= '<form id="searchform" action="summary.php?d=5&rid=22&base=0&mode=table&sesskey=BJPCl2FEuw" method="get">';

	$str.= '<table cellspacing="0" align="center" width="50%" class="baselinecomment forumpost">';
	$str.= '<caption>Select date range or type </caption>';
/* issue with no user 
    $str.= '<tr class="header"><td class="picture left">';
    print_user_picture($user, $baseline->course, $user->picture);
    $str.= '</td>';
*/
// Actual content
    $str.= '</td><td class="content" align="left">'."\n";
 return $str;
}
function sum_table2_graph($fieldName, $field_array ,$datefrom,$dateto) {
global $DB,$CFG,$USER;
	table_graph();
//How come it shows a day earlier than the pulldowns. must be in the conversion to timestamp...
$str ='';
if ($USER->id == '2' )$demo = true;
 if ($demo) {
$dateRange = 'Breath<br>';
}
$dateRange .= date("d F",$datefrom);
$dateRange .= " - ";
$dateRange .= date("d F",$dateto);
 $selectCount = 13;
for($k=0; $k < $selectCount; ++$k ) {
 if ($demo) {
 $n = 7;
 $n= floor(1 + ( $dateto - $datefrom)/ (60*60*24));
//should we send them away if the days > 10?? to rings.
for($i=0; $i < $n; ++$i ) {
        $answer = rand(1,5) -3 ;
        if ($answer > '0' ) $datax[$i] = true; else $datax[$i] = false;
        if ($answer == '0' ) $datay[$i] = true; else $datay[$i] = false;
        if ($answer < '0' ) $dataz[$i] = true; else $dataz[$i] = false;
}
} //demo data

for($i=0; $i < $n; ++$i ) {
   if($datax[$i] == 1)   $dstx[$i]='<td class="co1"><img src="cf/sm_red.gif" width=30 height=25</td> alt="worse"';  else   $dstx[$i] = '<td></td> ' ;
    if($datay[$i] == 1)  $dsty[$i]='<td class="co1"><img src="cf/sm_amber.gif" width=30 height=25</td> alt="same"';   else  $dsty[$i] = '<td></td> ' ;
     if($dataz[$i] == 1)  $dstz[$i]='<td class="co1"><img src="cf/sm_green.gif" width=30 height=25 alt="better"</td>'; else $dstz[$i] = '<td></td> ';
	$myDay = $datefrom + ($i * 60*60*24);
	$dsdate[$i] = '<td>'.date("d/M",$myDay).'<br>'.date("D", $myDay).'</td>';

}


 $str .=  ' <table class="ca ca1" border="5" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><caption>Display of Results </caption><tbody><tr class="cl"><td>' .$dateRange.'</td>';
for($i=0; $i < $n; ++$i ) { $str .=$dsdate[$i];}
 $str .= '</tr><tr><td>Worse than usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dstx[$i]; }
 $str .= '</tr><tr><td>Usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dsty[$i]; }
 $str .= '</tr> <tr><td>Better than usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dstz[$i]; }
 $str .= '</tr> </tbody></table><P>';
 }
return $str;
}


function sum_table3_graph($fieldName, $field_array ,$datefrom,$dateto) {
global $DB,$CFG,$USER;
        $str = '';
	table_graph();
 /*if  ( $content = $DB->get_record( $this->field->mycontent,'fieldid',array( $this->field->id=>array('recordid'=>$recordid)))) {
         // if ( $content = $DB->get_record( 'baseline_content','fieldid',array( $this->field->id=>array('recordid'=>$recordid)))) {
            if (isset($content->content)) {
                $options = new object();
                if ($this->field->param1 == '1') {  // We are autolinking this field, so disable linking within us
                    //$content->content = '<span class="nolink">'.$content->content.'</span>';
                    //$content->content1 = FORMAT_HTML;
                    $options->filter=false;
                }
                $options->para = false;
                $str = format_text($content->content, $content->content1, $options);
            } else {
                $str = '';
            }
}
  */
          // fps check base $str .= " the file".$this->field->mycontent." the id". $this->field->id;

// Show the current value as a highlighted value if found by search
        if ($base) {
        /* Mod to show rids baseline entries  FPS */
                    notify(get_string('rbase','baseline'), 'notifysuccess');
  } else {
//How come it shows a day earlier than the pulldowns. must be in the conversion to timestamp... It was fixed in summary.php
// FPS Sep if ($USER->id == '2' )$demo = true;
$dateRange = date("d F",$datefrom);
$dateRange .= " - ";
$dateRange .= date("d F",$dateto);
 $selectCount = 13;
for($k=0; $k < $selectCount; ++$k ) {
 if ($demo) {
 $n = 7;
 $n= floor(1 + ( $dateto - $datefrom)/ (60*60*24));
for($i=0; $i < $n; ++$i ) {
	$answer = rand(1,5) -3 ;
	if ($answer == '0' ) $datay[$i] = true; else $datay[$i] = false;
	if ($answer > '0' ) $datax[$i] = true; else $datax[$i] = false;
	if ($answer < '0' ) $dataz[$i] = true; else $dataz[$i] = false;
}
} //demo data

for($i=0; $i < $n; ++$i ) {
    if($datax[$i] == 1)   $dstx[$i]='<td class="co1"><img src="cf/red.gif alt="worse""</td>';  else   $dstx[$i] = '<td></td> ' ;  
    if($datay[$i] == 1)  $dsty[$i]='<td class="co1"><img src="cf/amber.gif" alt="worse"</td>';   else  $dsty[$i] = '<td></td> ' ;
     if($dataz[$i] == 1)  $dstz[$i]='<td class="co1"><img src="cf/green.gif" alt="worse"</td>'; else $dstz[$i] = '<td></td> '; 
	$myDay = $datefrom + ($i * 60*60*24);
	$dsdate[$i] = '<td>'.date("d/M",$myDay).'<br>'.date("l", $myDay).'</td>';

}


 $str .=  ' <table class="ca ca1" border="5" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><caption>Display Results</caption><tbody><tr class="cl"><td>' .$dateRange.'</td>';
// $str .='<td>Sunday</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td><td class="cr">Saturday</td></tr> ';
for($i=0; $i < $n; ++$i ) { $str .=$dsdate[$i];}
 $str .= '</tr><tr><td>Worse than usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dstx[$i]; }
 //$str .=  '<td></td><td></td><td></td><td></td><td></td><td class="co1"><img src="'.$CFG->wwwroot.'/pix/cf/red.gif"</td><td class="co1 cr"></td></tr>';
 $str .= '</tr><tr><td>Usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dsty[$i]; }
//$str .= '<img src="'.$CFG->wwwroot.'/pix/cf/amber.gif"</td><td class="co1"></td><td></td><td></td><td></td><td></td><td class="co4 cr"></td></tr>';
 $str .= '</tr> <tr><td>Better than usual</td>';
for($i=0; $i < $n; ++$i ) { $str .=  $dstz[$i]; }
 //$str .= '<td></td><td></td><td></td><td><img src="'.$CFG->wwwroot.'/pix/cf/green.gif"</td><td></td><td class="co4 cr"></td></tr> <tr class="cb"><td></td><td></td><td></td><td></td><td></td><td></td><td class="cr"></td><td></td></tr>';
 $str .= ' </tbody></table><P>';
 }
 }
return $str;
}

 function sum_date_range($datefrom,$dateto)
{
    $str = '<tr>';
    $str.= '<td class="c0">'.get_string('searchdatefrom', 'forum');
// .'</td>';
;
//     $str .= '<td class="c1">';
    if (empty($datefrom)) {
        $datefromchecked = '';
        $datefrom = make_timestamp(2000, 1, 1, 0, 0, 0);
    }else{
        $datefromchecked = 'checked="checked"';
    }

    $str.= '<input name="timefromrestrict" type="checkbox" value="1" alt="'.get_string('searchdatefrom', 'forum').'" onclick="return lockoptions(\'searchform\', \'timefromrestrict\', timefromitems)" '.  $datefromchecked . ' /> ';
    $selectors = html_writer::select_time('days', 'fromday', $datefrom)
               . html_writer::select_time('months', 'frommonth', $datefrom)
               . html_writer::select_time('years', 'fromyear', $datefrom)
      ;//         . html_writer::select_time('hours', 'fromhour', $datefrom)
     //          . html_writer::select_time('minutes', 'fromminute', $datefrom);
    $str.= $selectors;
 /*   $str .= '<input type="hidden" name="hfromday" value="0" />';
    $str .= '<input type="hidden" name="hfrommonth" value="0" />';
    $str .= '<input type="hidden" name="hfromyear" value="0" />';
    //$str .= '<input type="hidden" name="hfromhour" value="0" />';
    //$str .= '<input type="hidden" name="hfromminute" value="0" />';
*/
    $str.= '</td>';
    $str.= '</tr>';

    $str.= '<tr>';
    $str.= '<td class="c0">'.get_string('searchdateto', 'forum');
    // .'</td>';
    //  $str .= '<td class="c1">';
    if (empty($dateto)) {
        $datetochecked = '';
        $dateto = time()+3600;
    }else{
        $datetochecked = 'checked="checked"';
    }

    $str.= '<input name="timetorestrict" type="checkbox" value="1" alt="'.get_string('searchdateto', 'forum').'" onclick="return lockoptions(\'searchform\', \'timetorestrict\', timetoitems)" ' .$datetochecked. ' /> ';
    $selectors = html_writer::select_time('days', 'untilday', $dateto)
               . html_writer::select_time('months', 'untilmonth', $dateto)
               . html_writer::select_time('years', 'untilyear', $dateto)
   ;//            . html_writer::select_time('hours', 'untilhour', $dateto)
    //           . html_writer::select_time('minutes', 'untilminute', $dateto);
    $str.= $selectors;
/*
    $str .= '<input type="hidden" name="htoday" value="0" />';
    $str .= '<input type="hidden" name="htomonth" value="0" />';
    $str .= '<input type="hidden" name="htoyear" value="0" />';
   // $str .= '<input type="hidden" name="htohour" value="0" />';
   // $str .= '<input type="hidden" name="htominute" value="0" />';
*/
    $str.= '</td>';
    $str.= '</tr>';

   return $str; 
}
 function sum_choice($choices,$course,$rid,$curr,$d)
{
$str ='';
$str .= '<tr><td class="c0"><label for="menumode">'.get_string('setnewgraph', 'baseline').'</label></td>';
     $str .= '</tr><tr><td class="c1">';
 	//choose_from_menu($choices, 'mode', $curr, get_string('submitchoices', 'baseline'));
 	// loose auto Maria Moore choose_from_menu($choices, 'mode', $curr, get_string('submitchoices', 'baseline'), 'this.form.submit()');
 $str .= 	html_writer::select($choices, 'mode',  $curr,get_string('submitchoices', 'baseline'));
//	print_r($choices);
    $str .= '</td>';
    $str .= '</tr>';

$str .= '<tr>';
    $str .= '<input type="hidden" value="'.$course->id.'" name="id" alt="" /></td>';
    $str .= '<input type="hidden" value="'.$d.'" name="d" alt="" /></td>';
    //$str .= '<input type="hidden" value="22" name="rid" alt="" /></td>';
    $str .= '</tr>';
$str .= '<tr>';
    $str .= '<input type="hidden" value="'.$rid.'" name="rid" alt="" /></td>';
    //$str .= '<input type="hidden" value="5" name="d" alt="" /></td>';
    $str .= '</tr>';
 $str .= '<tr>';
//if ($curr == 0 ) {
    $str .= '<td class="submit" colspan="2" align="center">';
    $str .= '<input type="submit" value="'.get_string('submitgraph', 'baseline').'" alt="" /></td>';
 //}
    $str .= '</tr>';
return $str;
}

function get_table($datefrom,$dateto)
{
        global $DB,$USER,$CFG;
$str ='';
//  select * from { (select 'base',br.timecreated, br.userid, bc.fieldid, bc.content as content, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from baseline_base_content bc, baseline_base_records br where br.id=bc.recordid union  select 'diary',br.timecreated, br.userid, bc.fieldid, bc.content as content, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from baseline_content bc,  baseline_records br} where br.id=bc.recordid) as amy order by  fieldid,dow ;
	$sql = "select * from  (select 'base',br.timecreated, br.userid, bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id  union  select 'diary',br.timecreated, br.userid, bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,  {baseline_records} br ,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id ) amy ) where userid=";
  $sql .= $USER->id;
	$sql.= ' and id != 107 and id != 106 ';
        if($datefrom > 0 ) $sql .=" and timecreated between ".$datefrom." and ".$dateto;
        $sql .= " order by name, timecreated ";
 if ($rs = $DB->get_recordset_sql($sql)) {
	$baseValue = 0;
	$countRecord = 0;
  if ($rs) {
           foreach ($rs as $record) {
	if ($record->base == 'base' ) $baseValue =  $record->content;
	else  {$recValue[$countRecord] =  $record->content - $baseValue;
	 $Date[$countRecord] = $record->timecreated;
//	$str .= $record->name.' , ';
	//$str .= $record->dow.' , ';
	// $str .=   $record->timecreated.' , ';
	 $str .=   $record->content.' , ';
	 $str .=   $baseValue.' : ';
	 $str .=   $recValue[$countRecord].' : ';

	$countRecord++;
 //print_r($record);
}
}
}
}
for($i=0; $i < $countRecord; $i++ ) {
//$str .=  $Date[$i].' , ';
$str .=  $recValue[$i].' : ';
}
return $str;
}
 function ring_display() {
$str ='';
 global $DB,$USER,$CFG;

//totalDays is diff between 1st and last timestamp in days 86400
if ($USER->id == '2' )$demo = true;
 if ($demo) {
 $str .= '</table><table><caption>Data layout </caption><tr>';
 $selectCount = 13;
for($k=0; $k < $selectCount; ++$k ) {
 $n = 17;
$same=0;
$worse=0;
$better=0;
for($i=0; $i < $n; ++$i ) {
        $answer = rand(1,5) -3 ;
        if ($answer == '0' ) $same++;
        if ($answer > '0' ) $worse++;
        if ($answer < '0' ) $better++;
}
$miss = $k * 9;
$title = 'Demo_Data'.$answer;
 $Mystring = $better.','.  $same.','.$worse;
 $str .= '<td class="graph"><img src="php-growth-charts/r3.php?style=bmi-age&title='.$title.'&missing='.$miss.'&xvals='.$Mystring.'&yvals='.$Mystring.'" alt="'.$title.'"> </td>';
if ($k % 3 == '2' ) $str .= '</tr><tr>';
}

} //demo data
else {
$str .= " Not Demo style<table>";
 $sql = "select * from (select 'base',br.timecreated, br.userid, bf.id,bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id  union  select 'diary',br.timecreated, br.userid , bf.id,bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,  {baseline_records} br ,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id ) as amy  where userid=";
  $sql .= $USER->id;
//Nov2010	$sql.= ' and  bf.id not 107 and bf.id not 106';
	$sql.= ' and id != 107 and id != 106';

     $sql .= " order by name, timecreated desc";
 if ($rs = $DB->get_recordset_sql($sql)) {
        $baseValue = 0;
        $countRecord = 0;
        $title = '';
	$numberOfRings = 0;
	$better=0;
	$worse=0;
	$same=0;
  if ($rs) {
           foreach ($rs as $record) {
        $fieldName = $record->name;
        if ($record->base == 'base' ) $baseValue =  $record->content;
        else  {$recValue[$countRecord] =  $record->content - $baseValue;
         $Date[$countRecord] = $record->timecreated;
         if($recValue[$countRecord] < 0 ) $better++;
         if($recValue[$countRecord] == 0 ) $same++;
         if($recValue[$countRecord] > 0 ) $worse++;
	$countRecord++;
 	if (trim($title) != trim( $fieldName) && ($title != '')) {
	$totalDays = ($Date[0] - $Date[$countRecord-1])/86400;
//$str .= 'date '.$Date[$countRecord -1 ].'  begin '. $Date[0].' recno. '. $countRecord .' Tot Days '.$totalDays;
// Calculation faulty...
	$miss = floor((($totalDays -  $countRecord)/$totalDays ));
	$Mystring = $better.','.  $same.','.$worse;
	$numberOfRings ++;
 $str .= '<td class="graph"><img src="php-growth-charts/r3.php?style=bmi-age&title='.$fieldName.'&missing='.$miss.'&xvals='.$Mystring.'&yvals='.$Mystring.'" alt="'.$title.'"> </td>';
if ($numberOfRings % 3 == '0' ) $str .= '</tr><tr>';
	$better=0;
	$worse=0;
	$same=0;
	$countRecord = 0;

} //output new field.
$title =  $fieldName;
        }
        }
}
  $totalDays = ($Date[$countRecord] - $Date[0])/86400;
	 if($recValue[$countRecord] < 0 ) $better++;
         if($recValue[$countRecord] == 0 ) $same++;
         if($recValue[$countRecord] > 0 ) $worse++;
        $countRecord++;
        $miss = floor((($totalDays -  $countRecord)/$totalDays ));
        $Mystring = $better.','.  $same.','.$worse;
        $numberOfRings ++;
 $str .= '<td class="graph"><img src="php-growth-charts/r3.php?style=bmi-age&title='.$title.'&missing='.$miss.'&xvals='.$Mystring.'&yvals='.$Mystring.'" alt="'.$title.'"> </td>';


}
}
//$str .= $sql."xx";
 $str .= '</tr></table>';
return $str;
}

function line_display($datefrom,$dateuntil) {
	global $DB,$USER,$CFG;
/* Sep 2012  Asked for :	Add option on the symptom reporting to display a line graph representing all daily entries (not comparing to baseline). This will allow the user to track over time their symptoms and may be a preferred option for those with a very good baseline etc. Line Graph with x axis being time and the y axis representing the scale from worse to better(moving up the axis).
*/
// Invert data required...  0 is good 5 is bad, but extent not fixed. ie bowel is bad either sidde of good...
// My idea to have a bar showing baseline as a background barchart

 $str=''; //used to output page FPS

	 $sql = "select * from (select 'base',br.timecreated, br.userid, bf.id,bf.name as name,bf.param1   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id  union  select 'diary',br.timecreated, br.userid , bf.id,bf.name as name ,bf.param1  , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,  {baseline_records} br ,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id ) as amy  where userid=";
  $sql .= $USER->id;
  // $sql .= 3;         
//Nov2010	$sql.= ' and  bf.id not 107 and bf.id not 106';
	// show all ine linegraphs $sql.= ' and id != 107 and id != 106';
 //  if($datefrom > 0 ) $sql .=" and timecreated between ".$datefrom." and ".$dateuntil;

     $sql .= " order by name, timecreated desc"; 
 if ($rs = $DB->get_recordset_sql($sql)) {
        $baseValue = 0;
        $title = '';
	$numberOfRings = 0;
	$better=0;
	$worse=0;
	$same=0;
         $basey = '';
         $datay = '';
         $Date = '';
        $countRecord = 0;
        foreach ($rs as $record) {
      //if ($countRecord == 0 )  $title =  $fieldName;
         $fieldName = $record->name;
         $maxrec =  0;
	if (is_numeric($record->content)) {
	 if (isset($record->param1)) foreach (explode("\n",$record->param1) as $myvalue) {
          if ($maxrec < $myvalue) $maxrec = $myvalue;
          }
         if (is_numeric($maxrec)) $graph = true;  else unset($maxrec) ;
 	if ((trim($title) != trim( $fieldName)) && ($countRecord > 1)) {
	$totalDays = 1 + ($Date[0] - $Date[$countRecord-1])/86400;
// Calculation faulty...
	$miss = floor((($totalDays -  $countRecord)/$totalDays ));
	$Mystring = $better.','.  $same.','.$worse;
 $str .= '<td class="graph"><img src="php-growth-charts/baseline_chart.php?style=bmi-age&title='.$title.'&missing='.$miss.'&xvals='.substr($basey,0,-1).'&yvals='.substr($datay,0,-1).'" alt="'.$title.'"> </td>';
 $str .= '</tr><tr>';
      	$title =  $fieldName;

         $basey = '';
         $datay = '';
         $Date = '';
         $maxrec =  0;
         $countRecord = 0;
      } //output new field.
         if ($record->base == 'base' )   $baseValue =  $record->content;
         $basey .= $baseValue.',';
         if ( isset($maxrec)) $datay .= $maxrec - $record->content.','; else $datay .= $record->content.',';
         $Date[$countRecord] = $record->timecreated;
	$countRecord++;
        
}
      }
 $str .= '</tr></table>';
return $str;
      }
      }


function  print_bmi(){
 global $DB,$USER,$CFG;
        $AdultBMI = false;
        $ChildBMI = false;
        $str = '';
	$amon ='';
	$maxage = 0;
/*  age , are theire child ones, two selects.
yvals are months
xvals are bmi
 maxage  Essentially, the patient's current age, but technically the maximum age (in months) found in the patient's xvals data.
  If under 36 months, an infant growth chart will be shown.  If over 36 months, a standard pediatric chart will be shown.
                If only an infant chart exists for the style you specify, but you give a max age over 36 (or vice-versa), bad things will happen.

*/
$sql = " select unix_timestamp(now()) as now,  date_format((date_add(str_to_date(d.data,'%d/%m/%Y'),interval 1 hour)),'%Y%m') as dob ,  unix_timestamp(date_add(str_to_date(d.data,".'"'."%d/%m/%Y".'"'.") ,interval 264 month )) as peads  from {user_info_data}  d,  {user_info_field} f  where f.shortname = 'DOB' and d.fieldid = f.id and d.userid =";
  $sql .= $USER->id;
//$str .= $sql.';';
if ($rs = $DB->get_recordset_sql($sql)) {
   if ($rs) {
           foreach ($rs as $record) {
	$DOB=$record->dob;
	$peads=$record->peads;
	$now=$record->now;
        }
// $str .= "Date of Birth ".$DOB." Peads ".$peads." Now ".$now;
  if (!$DOB) $DOB = '14/07/2009';
}
}
 
$sql ="select * from (select  period_diff(from_unixtime(timecreated,'%Y%m'),'".$DOB."') date ,avg(at.weight),at.* from (select 'base',br.timecreated, br.userid, bc.fieldid, bc.content as weight, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'height') union select 'diary',br.timecreated, br.userid, bc.fieldid, bc.content as weight, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,{baseline_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'height')) at where  userid=";
  $sql .= $USER->id;
  $sql .= " group by date ";
  $sql .= "union select  period_diff(from_unixtime(timecreated,'%Y%m'),'".$DOB."') date ,avg(at.weight),at.* from (select 'wbase',br.timecreated, br.userid, bc.fieldid, bc.content as weight, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight') union select 'wdiary',br.timecreated, br.userid, bc.fieldid, bc.content as weight, dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,{baseline_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight')) at where  userid=";
  $sql .= $USER->id;
  $sql .= " group by date ";
  $sql .= " ) as fred  where weight is not null order by timecreated ";

 // $str .= $sql.';';
 if ($rs = $DB->get_recordset_sql($sql)) {
        $baseValue = 0;
        $countRecord = 0;
        $acountRecord = 0;
        $title = '';
        $numberOfRings = 0;
        $better=0;
        $height=0.4;
        $same=0;
// This is not correct as height may not match months ...
 if ($rs) {
           foreach ($rs as $record) {
	 	/*print_r($record);
                print_r('<br>');
*/

		if( $record->date < 264 ) {
		$ChildBMI= true;
		switch($record->base) {
			case 'base':
			 $height=$record->weight/100;
			break;
			case 'diary':
			 $height=$record->weight/100;
			break;
			case 'wbase':
			 $bmi[$countRecord] = $record->weight/($height*$height);
			$infbmi[$countRecord] = $record->weight;
			 $mon[$countRecord] = $record->date;
			 $countRecord++;
			break;
			case 'wdiary':
			 $bmi[$countRecord] = $record->weight/($height*$height);
			$infbmi[$countRecord] = $record->weight;
			 $mon[$countRecord] = $record->date;
			 $countRecord++;
			break;
		}
		} else {
 		$AdultBMI= true;
                switch($record->base) {
                        case 'base':
                         $height=$record->weight/100;
                        break;
                        case 'diary':
                         $height=$record->weight/100;
                        break;
                        case 'wbase':
                         //$adbmi[$acountRecord] = $record->weight/($height*$height);
                         $adbmi[$acountRecord] = $record->weight;
                         $amont[$acountRecord] = $record->date;
                         $acountRecord++;
			$str .= $amon[$acountRecord];
                        break;
                        case 'wdiary':
                         //$adbmi[$acountRecord] = $record->weight/($height*$height);
                         $adbmi[$acountRecord] = $record->weight;
			//$str .= $adbmi[$acountRecord];
                         $amont[$acountRecord] = $record->date;
			$str .= $amon[$acountRecord];
$str .= '<br> ';
                         $acountRecord++;
                        break;

		}
		}
	}
	}
	$amon='';
	$abmi='';
	$cmths='';
	$cbmi='';
	$icbmi='';
	if ($AdultBMI) { 
		for($k=0; $k < $acountRecord ; ++$k ) {
			 $abmi.= $adbmi[$k].',';
			 $amon.= $amont[$k].',';
			}
                         $abmi.= $adbmi[$acountRecord - 1];
                         $amon.= $amont[$acountRecord - 1];
			$CurrentWeight = $adbmi[$acountRecord - 1];
			}
	if ($ChildBMI) { 
 
		for($k=0; $k < $countRecord - 1; ++$k ) {
			// Years 
			 $cmths.= ($mon[$k] / 12 ).',';
			 $cbmi.= $bmi[$k].',';
			$icbmi.= $infbmi[$k].',';
			}
			$icbmi.= $infbmi[$countRecord - 1];
                        $cmths.= ($mon[$countRecord - 1] / 12 );
                         $cbmi.= $bmi[$countRecord - 1];
			$maxage = $mon[$countRecord -1];
			}
 	if ($maxage < 24.0  ) {$cbmi = $icbmi;}	
} else {
	$amon='27,34,45,55,57,60,62,63,66,67,69,72,77,81,84';
//14,15,17,10,13,15,15,22,26,26,30,34,40,43,47,45,40,42
	$abmi='16.28,16.8,16.99,16.4,16.0,16.06,16.7,16.96,17.1,17.81,18.62,18.9,19.24,19.54,20.2';
	$cmths='27,34,45,55,57,60,62,63,66,60,69,72,77,81,84';
	$cbmi='19.28,17.8,17.07,15.4,16.6,17.06,16.7,16.96,17.1,18.81,16.62,16.9,17.24,17.54,18.2';
	 $AdultBMI= true;
	 $ChildBMI= true;
}
		if ($AdultBMI) {
		$heightcm=100*$height;
                $str .= '<img src="php-growth-charts/bmi-adult.php?height='.$heightcm.'&xvals='.$abmi.'&yvals='.$amon.'"  alt="bmi adult graph" />';
                $str .= "<tr><td><br>";
weight_alert($CurrentWeight);
		$str .= "Red (BMI <18.5 kg/m2) Very underweight - high nutritional risk <br>
Yellow (BMI  18.5 - 20 kg/m2) Underweight - at nutritional risk <br>
Green/Pink (BMI 20-27kg/m2) Healthy weight range <br>
Light brown (BMI >27kg/m2) Overweight</td></tr><tr><td>";
		}

		if ($ChildBMI) {
//get the sex here;
$sql = " select d.data gender from {user_info_data}  d,  {user_info_field} f  where f.shortname = 'gender' and d.fieldid = f.id and d.userid =";
  $sql .= $USER->id;
//$str .= $sql.';';
$sex=1;
if ($rs = $DB->get_recordset_sql($sql)) {
           foreach ($rs as $record) {
	   if ($record->gender == 'F' ) $sex = 2;
        }
}
	// $record=getrecord(
                $str .= '<img src="php-growth-charts/chart.php?style=bmi-age&sex='.$sex.'&maxage='.$maxage.'&xvals='.$cmths.'&yvals='.$cbmi.'"  alt="bmi-age&sex graph"/>';

                // $str .= '<img src="php-growth-charts/chart.php?style=bmi-age&sex=1&maxage=35&xvals=27,34,45,55,57,60,62,63,66,60,69,72,77,81,84&yvals=19.28,17.8,17.07,15.4,16.6,17.06,16.7,16.96,17.1,18.81,16.62,16.9,17.24,17.54,18.2" />';
                $str .= "</td></tr>";
	}
$str .= "</table><br>";
/*$sql = "select max(weight) * 0.95 as max  from  ( select  br.userid ,bc.content as weight from {baseline_base_content} bc,{baseline_base_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  union select  br.userid ,bc.content as weight from {baseline_content} bc, {baseline_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  ) at where userid= ";
 $sql .= $USER->id;
 //$str .= $sql;
if ($rs = $DB->get_recordset_sql($sql)) {
           foreach ($rs as $record) {

 if ($CurrentWeight < $record->max )  { 
 $str .= " Your weight is ".$CurrentWeight. " your maximum was " .$record->max."<br>";
$str .= "Yellow alert: Your weight has dropped by 5% in 2 months"; }
}
}
*/
return $str;
}
 function graph_data($fieldName, $field_array ,$datefrom,$dateto,$myType) {
global $DB,$CFG,$USER;
/*
FPS  Line graph
*/
}

 function sum_table_graph($fieldName, $field_array ,$datefrom,$dateto,$myType) {
/* table FPS */
global $DB,$CFG,$USER;
$str ='';
/*
Doublecheck datefrom ande date to , limit to some max figure from one.
for each day as date/month 
Find difference betwen last and this in days and fill in  $datefrom is in the first record.
*/
$sql = "select * from  (select 'base',br.timecreated, br.userid, bf.id, bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_base_content} bc, {baseline_base_records} br, {baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id  union  select 'diary',br.timecreated, br.userid, bf.id, bf.param5 as name   , bc.content , dayname(from_unixtime(br.timecreated,'%Y-%m-%d')) as dow from {baseline_content} bc,  {baseline_records} br ,{baseline_fields} bf where br.id=bc.recordid and bc.fieldid = bf.id ";
if($datefrom > 0 ) $sql .=" and timecreated between ".$datefrom." and ".$dateto;
$sql .= ") as amy  where userid=";
  $sql .= $USER->id;
	$sql.= ' and id != 107 and id != 106 and   id != 111';
// Only map selected fields FPS
        $sql.= ' and id IN (select field_id from  {baseline_field2user} where userid='.$USER->id.')';
        $sql .= " order by name, timecreated ";
$dateRange = date("d F",$datefrom);
$dateRange .= " - ";
$dateRange .= date("d F",$dateto);
$str .= '</tbody></table>';

		$RED = 'cf/sm_red.gif" alt="worse" width=60 height=25> </td>';
		$AMBER ='cf/sm_amber.gif" alt="same" width=60 height=25> </td>';
		$GREEN ='cf/sm_green.gif" alt="better" width=60 height=25> </td>';
		$MyDATEFMT='d/M';
		$MyDAYFMT='D';
  switch ($myType) {
        case 'table':
		$RED = 'cf/red.gif" alt="worse"> </td>';
		$AMBER ='cf/amber.gif" alt="same"> </td>';
		$GREEN ='cf/green.gif" alt="better"> </td>';
		$MyDATEFMT='d/M';
		$MyDAYFMT='l';
	  break;
        case 'table2':
		$MyDATEFMT='d/M';
	  break;
        case 'table3':
		$MyDATEFMT='d/M';
	  break;
        case '*':
	  break;
}

 if ($rs = $DB->get_recordset_sql($sql)) {
        $baseValue = 0;
        $title = 'XX';
         $NoOfDays = -1;
        $numberOfRings = 0;
        $countRecord = 0;
        $TimeScale = 1;
         $datax[0] = '';
         $datay[0] = '';
         $dataz[0] = '';
        $better=0;
        $worse=0;
	$MyDate  = array();
	$recValue  = array();
        $same=0;
           foreach ($rs as $record) {
        $fieldName = $record->name;
        if ($record->base == 'base' ) {
		$baseValue =  $record->content;
        } else  {
	 // $str .= $NoOfDays . ': '.$fieldName . ' '.trim($title). ' <br>';
// If the recordname has changed and its not the first record of all
	 //if (trim($title) != trim( $fieldName)  &&($title != 'XX')) {
	 if (trim($title) != trim( $fieldName)  &&($NoOfDays != -1)) {
/* end of group print out and reset */
        
if ($countRecord++ > 1) $NoOfDays = floor($MyDate[$countRecord -1 ] - $MyDate[0]/86400);
// $str .= '$';
 // $str .= '<br>';
//	 $str .= $NoOfDays . ': '.$fieldName . ' XX'.trim($title);
 //  $str .=   floor($MyDate[0]/86400). ': '.$MyDate[$countRecord-1];
//  $str .= '<br>';
//print_r($recValue);
$TimeScale=floor(($dateto- $datefrom)/86400)  ; 
if ($TimeScale <= 1 )$TimeScale = 1;
for ($i=0; $i < $TimeScale; ++$i ) {
   if($datax[$i] == 1)   $dstx[$i]='<td class="co1"><img src="'.$RED;  else   $dstx[$i] = '<td></td> ' ;
    if($datay[$i] == 1)  $dsty[$i]='<td class="co1"><img src="'.$AMBER;   else  $dsty[$i] = '<td></td> ' ;
     if($dataz[$i] == 1)  $dstz[$i]='<td class="co1"><img src="'.$GREEN ; else $dstz[$i] = '<td></td> ';
        $myDay = $datefrom + ($i * 60*60*24);
        $dsdate[$i] = '<td>'.date($MyDATEFMT,$myDay).'<br>'.date($MyDAYFMT, $myDay).'</td>';

 	}
 $str .= '<h4>'.$title.'</h4><br>';
 $str .=  ' <table class="ca ca1" border="5" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><caption>'.$title.'</caption><tbody><tr class="cl"><td>'.$dateRange.'</td>';
 //$str .=  ' <table class="ca ca1" border="10" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><tbody><tr class="cl"><td>'.$fieldName.'<br>'. $dateRange.'</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=$dsdate[$i];}
 $str .= '</tr><tr><td>Worse than usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dstx[$i]; }
 $str .= '</tr><tr><td>Usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dsty[$i]; }
 $str .= '</tr> <tr><td>Better than usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dstz[$i]; }
 $str .= '</tr> </tbody></table><P>';
for ($i=0; $i < $TimeScale; ++$i ) {
 $MyDate[$i] = '';
         $datax[$i] = '';
         $datay[$i] = '';
         $dataz[$i] = '';
         $recValue[$i] = '';
	 $MyDate[$i] = '';
	 $dstx[$i] = '';
	 $dsty[$i] = '';
	 $dstz[$i] = '';
	 $recValue= array();
 }
	 $countRecord= 0;
	$NoOfDays = -1;
}
$title =  trim( $fieldName);

	/* which day is referenced  */
        if ( $NoOfDays  == -1 ) { 
	//	 $NoOfDays = floor($record->timecreated/86400);
		 if($datefrom > 0 )$NoOfDays = floor(($record->timecreated - $datefrom)/86400)  ;
		 $MyDate[0] = floor($record->timecreated);
	 } else {	
		$NoOfDays = floor ( ($record->timecreated -  $MyDate[0])/86400);
		if($datefrom > 0 )$NoOfDays = floor(($record->timecreated - $datefrom)/86400)  ;
		$MyDate[$countRecord] =  floor($record->timecreated/86400);
	 }
	 //if($datefrom > 0 )$NoOfDays = floor(($record->timecreated - $datefrom)/86400)  ;
	//	$str .= 'base '.$baseValue;
	 ////$str .= 'Days '.$NoOfDays.'<br>';
	//print_r($datay);


	 $recValue[$countRecord] =  $record->content - $baseValue;
         if($recValue[$countRecord] > 0 ) $datax[$NoOfDays] = 1;
         if($recValue[$countRecord] < 0 ) $dataz[$NoOfDays] = 1;
         if($recValue[$countRecord] == 0 ) $datay[$NoOfDays] = 1;
	  // $str .= ': '.$NoOfDays . ': ';
	  //$str .=  $MyDate[$countRecord] . ': '.$recValue[$countRecord].' , ';
	 //$str .= $NoOfDays . ': '.$countRecord . ' XX';
        $countRecord++;
//$title =  $fieldName;
        }
        }
	//fall out with 1 left...
for ($i=0; $i <= $TimeScale; ++$i ) {
   if($datax[$i] == 1)   $dstx[$i]='<td class="co1"><img src="'.$RED;  else   $dstx[$i] = '<td></td> ' ;
    if($datay[$i] == 1)  $dsty[$i]='<td class="co1"><img src="'.$AMBER;   else  $dsty[$i] = '<td></td> ' ;
     if($dataz[$i] == 1)  $dstz[$i]='<td class="co1"><img src="'.$GREEN ; else $dstz[$i] = '<td></td> ';
}
 $str .= '<h4>'.$title.'</h4><br>';
 $str .=  ' <table class="ca ca1" border="5" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><caption>'.$title.'</caption><tbody><tr class="cl"><td>'.$dateRange.'</td>';
 //$str .=  ' <table class="ca ca1" border="10" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><tbody><tr class="cl"><td>'.$fieldName.'<br>'. $dateRange.'</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=$dsdate[$i];}
 $str .= '</tr><tr><td>Worse than usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dstx[$i]; }
 $str .= '</tr><tr><td>Usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dsty[$i]; }
 $str .= '</tr><tr><td>Better than usual</td>';
for($i=0; $i < $TimeScale; ++$i ) { $str .=  $dstz[$i]; }
 $str .= ' </tr></tbody></table><P>';
 } else {  // of records
$str= '<br><h3>No Records Found</h3><br>';
}
return $str;

 }
function weight_alert($CurrentWeight) {
global $DB,$CFG,$USER;
$str='';
$sql = "select max(weight) * 0.95 as max  from  ( select  br.userid ,bc.content as weight from {baseline_base_content} bc,{baseline_base_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  union select  br.userid ,bc.content as weight from {baseline_content} bc, {baseline_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'weight')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  ) at where userid= ";
 $sql .= $USER->id;
// $str .= $sql;
 if ($rs = $DB->get_recordset_sql($sql)) {
           foreach ($rs as $record) {
	 if ($CurrentWeight < $record->max )  { 
		$str .= '<FONT style="BACKGROUND-COLOR: yellow">Your weight has dropped by more than 5% in 2 months </FONT><br>'; 
 		$str .= " Your weight is ".$CurrentWeight. " your maximum was " .$record->max."<br>";
	}
   }
return $str;
 }
}
function ShowNotes() {
global $DB,$CFG,$USER;
                $str = " Your notes <br>";
$sql = "select  date, notes  from  ( select  br.userid ,bc.content as notes ,from_unixtime(br.timecreated,'%d/%m/%Y') as date from {baseline_base_content} bc,{baseline_base_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'notes')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  union select  br.userid ,bc.content as notes, from_unixtime(br.timecreated,'%d/%m/%Y') as date from {baseline_content} bc, {baseline_records} br where bc.recordid = br.id and bc.fieldid = ( SELECT c.id FROM {baseline_fields} c where name = 'notes')  and br.timecreated  >=  DATE_SUB(CURDATE(),INTERVAL 2 MONTH)  ) at where userid= ";
 $sql .= $USER->id;
 // $str .= $sql;
 if ($rs = $DB->get_recordset_sql($sql)) {
           foreach ($rs as $record) {
                $str .= (!empty($record->notes) ?  $record->date." ".$record->notes  : '');
        }
   }
return $str;
 }
?>
