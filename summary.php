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

/* start with 7 day diary table
ie type= table/ ring/bmi/graph.
datefrom = now() - 7 days  dateuntil = now()

display selectors 
for bmi show adult if adult then child ie dateofbirth to dateofbirth + 264 months.


selector implies date information so  dateutil is paramount...  can limit them todatefrom to show better than without?  
Best done with graph.
 needs userid effective.
formslib be used?
*/
//define('CLI_SCRIPT', true);

    require_once('../../config.php');
    require_once('lib.php');
    require_once('summary_lib.php');
    require_once('summary_form.php');
    global $CFG,$USER;
    require_once($CFG->libdir . '/pdflib.php');

$getpdf     = optional_param('getpdf', 0, PARAM_INT);
$fontfamily = optional_param('fontfamily', PDF_DEFAULT_FONT, PARAM_ALPHA);  // to be configurable

/**
 * Extend the standard PDF class to get access to some protected values we want to display
 * at the test page.
 *
 * @copyright 2009 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_pdf extends pdf {
    public function returnFontsList() {
        return $this->fontlist;
    }
    public function _getfontpath() {
        return parent::_getfontpath();
    }
}
   $d  = required_param('d', PARAM_INT);   // Record ID
    $page = optional_param('page', 0, PARAM_INT);   // Page ID

    //param needed for summary operations
    $timefromrestrict = optional_param('timefromrestrict', 0, PARAM_INT); // Use starting date
    $mode = optional_param('mode',0,PARAM_INT);
    $summaryid = optional_param('summaryid','',PARAM_INT);
    $fromday = optional_param('fromday', 0, PARAM_INT);      // Starting date

    $frommonth = optional_param('frommonth', 0, PARAM_INT);      // Starting date
    $fromyear = optional_param('fromyear', 0, PARAM_INT);      // Starting date
    $fromhour = optional_param('fromhour', 0, PARAM_INT);      // Starting date
    $fromminute = optional_param('fromminute', 0, PARAM_INT);      // Starting date
    if ($timefromrestrict) {
        $datefrom = make_timestamp($fromyear, $frommonth, $fromday, $fromhour, $fromminute);
    } else {
        $datefrom = optional_param('datefrom', 0, PARAM_INT);      // Starting date
    }

    $timetorestrict = optional_param('timetorestrict', 1, PARAM_INT); // Use ending date
    $untilday = optional_param('untilday', 0, PARAM_INT);      // Ending date
    $untilmonth = optional_param('untilmonth', 0, PARAM_INT);      // Ending date
    $untilyear = optional_param('untilyear', 0, PARAM_INT);      // Ending date
    $untilhour = optional_param('untilhour', 0, PARAM_INT);      // Ending date
    $untilminute = optional_param('untilminute', 0, PARAM_INT);      // Ending date
    $CFG->base    = NULL;
    // used above in maxentries.
    if ($timetorestrict) {
       if ($fromday > 0 )  $datefrom = make_timestamp($fromyear, $frommonth, $fromday,1); else $datefrom =mktime(0, 0, 0, date("m")  , date("d")-10, date("Y"));

    if ($untilday > 0 ) $dateuntil = make_timestamp($untilyear, $untilmonth, $untilday,23,59); else $dateuntil =   time()+ (24 * 60 * 60);
	if ( $dateuntil <  $datefrom ) { $mydate =  $datefrom;  $datefrom = $dateuntil ; $dateuntil = $mydate; }
   }

   $mychoice=array('table','ring','bmi','line');
/* 	
    if (! $record = $DB->get_record('baseline_records',array('id'=>$rid))) {
        error('Record ID is incorrect');
    }
*/
    //if (! $baseline = $DB->get_record('baseline',array('id'=>$record->baselineid))) {
    if (! $baseline = $DB->get_record('baseline',array('id'=>$d))) {
        error('Data ID is incorrect');
    }
    if (! $course = $DB->get_record('course',array('id'=>$baseline->course))) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
// fill in missing properties needed for updating of instance
$baseline->course     = $cm->course;
$baseline->cmidnumber = $cm->idnumber;
$baseline->instance   = $cm->instance;
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    echo $OUTPUT->error('invalidcontext', '');
}


    require_login($course->id, false, $cm);
    //$context = get_context_instance(CONTEXT_MODULE, $cm->id);
    // require_capability('mod/baseline:summary', $context);
    $search= false;
    if ($summaryid) {
        if (! $summary = $DB->get_record('baseline_summarys',array('id'=>$summaryid))) {
            error('Summary ID is misconfigured');
        }
        if ($summary->recordid != $record->id) {
            error('Summary ID is misconfigured');
        }
    } else {
        $summary = false;
	$summary =  new object();
	$summary->userid   = $USER->id;
    }
    $PAGE->set_pagelayout('standard');
   $PAGE->set_url($FULLME);

if (!$getpdf) {
	//$thisday  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	//$lastweek  = mktime(0, 0, 0, date("m")  , date("d")-10, date("Y"));
    //param needed to go back to view.php
//    $rid  = required_param('rid', PARAM_INT);   // Record ID
$rid = '';
    $d  = required_param('d', PARAM_INT);   // Record ID
    $page = optional_param('page', 0, PARAM_INT);   // Page ID

   $mform = new mod_baseline_summary_form();
/*
   $format = FORMAT_HTML;
   //$content = format_text($content, $format, $options);
 // $mform->set_data(array('mode'=>$mode, 'page'=>$page, 'rid'=>$record->id, 'summaryid'=>$summaryid));
    if ($summary) {
        // FPS Aug$ format = $summary->format;
        // FPS Aug $content = $summary->content;
        if (can_use_html_editor()) {
            $options = new object();
            $options->smiley = false;
            $options->filter = false;
            $content = format_text($content, $format, $options);
            $format = FORMAT_HTML;
        }
  //    $mform->set_data(array('content'=>$content, 'format'=>$format));
 // `	$PAGE->set_data(array('content'=>$content, 'format'=>$format));
    }
*/


    if ($mform->is_cancelled()) {
        redirect('summary.php?d='.$baseline->id.'&amp;page='.$page);
}
    // $nav = build_navigation('', $cm);
   
   // echo $OUTPUT->header_simple($baseline->name, '', $nav, '', '', true, update_module_button($cm->id, $course->id, get_string('modulename', 'baseline')), navmenu($course, $cm), '', '');
    $PAGE->set_title(format_string($baseline->name));
    $PAGE->set_heading(format_string($course->fullname));
if (!$getpdf)  {
    echo $OUTPUT->header();
     $OUTPUT->heading(format_string($baseline->name));

 	$currenttab = 'sum';
         include('tabs.php');
}
   // FPS Sep 2012  empty fildrid
        if (! isset($fieldrid) ) $fieldrid=''; 
echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('getpdf' => 1,'d' => $d)),"PDF");
//	echo print_r( $mychoice);
 //echo 'the value '.$mode. ' the name' . $mychoice[$mode];
// Select for dates and type:
                echo baseline_print_summary($baseline, $summary, $page);
    switch ($mychoice[$mode]) {
        case 'table2':
	echo sum_date_range($datefrom,$dateuntil);
	echo '<tr><td>';
	echo sum_choice($mychoice,$course,$rid,$mode,$d);
	//echo '</td></tr>';
//for each field 
	// echo print_r($USER);
//	get_table($datefrom,$dateuntil);
		//print(sum_table_graph($search, $fieldrid,$datefrom,$dateuntil));
                print(sum_table3_graph($search, $fieldrid,$datefrom,$dateuntil,'table'));

	
            if (!$formabaseline = $mform->get_data()) {
                break; // something is wrong here, try again
            }

        break;
	 case 'table':
	echo sum_date_range($datefrom,$dateuntil);
        echo '</tr><tr><td>';
        echo sum_choice($mychoice,$course,$rid,$mode,$d);
        echo '</td></tr>';
//for each field
        // get_table($datefrom,$dateuntil);
                //print(sum_table2_graph($search, $fieldrid,$datefrom,$dateuntil));
                print( sum_table_graph($search, $fieldrid,$datefrom,$dateuntil,'table2'));
        break;
	
	 case 'table3':
	echo sum_date_range($datefrom,$dateuntil);
        //sum_date_range($lastweek,$thisday);
        echo '<tr><td>';
        echo sum_choice($mychoice,$course,$rid,$mode,$d);
        echo '</td></tr>';
//for each field
        // get_table($datefrom,$dateuntil);
                print(sum_table3_graph($search, $fieldrid,$datefrom,$dateuntil,'table3'));
        break;
        
        case 'line':    //Line Graphs for all
	echo sum_date_range($datefrom,$dateuntil);
        echo '</tr><tr><td>';
	echo sum_choice($mychoice,$course,$rid,$mode,$d);
        echo '</td></tr>';
	 print(line_display($datefrom,$dateuntil));

        break;


        case 'ring':    //Ring Graphs for all except weight and height
	echo sum_choice($mychoice,$course,$rid,$mode,$d);
	echo ring_display();
		// style=1&title=Activity&missing=80&xvals=5,8,9,10&yvals=36,40,42.2,4I4
            if (!$formabaseline = $mform->get_data()) {
                break; // something is wrong here, try again
            }

        break;

        case 'bmi':    //shows adult  summary from bmi
/*   Get the age,sex and records of height and weight for child and all records, if over 264 months older than dob, show adult only if no dob? */
		echo "<tr><td>";
        echo sum_choice($mychoice,$course,$d,$mode,$d);
		echo "</td></tr></tbody></table><table><tbody><tr><td>";
	//echo '<caption>BMI Graph</caption>';
		echo print_bmi();
        break;
	 case '*':
	echo sum_date_range($datefrom,$dateuntil);
        //sum_date_range($lastweek,$thisday);
        echo '<tr><td>';
        echo sum_choice($mychoice,$course,$d,$mode,$d);
        echo '</td></tr>';
//for each field
        // get_table($datefrom,$dateuntil);
                print(sum_table_graph($search, $fieldrid,$datefrom,$dateuntil,'table'));
        break;


    }
	echo  ShowNotes();
    echo $OUTPUT->footer();
} else  {
    $doc = new testable_pdf();
/*  Change these for plots  FPS */
    $doc->SetTitle('Baseline Summary '.$mychoice[$mode].' display');
    $doc->SetAuthor('MyCf ' . $CFG->release);
    $doc->SetCreator('mod/baseline/summary.php');
    $doc->SetKeywords('MyCf, PDF');
    $doc->SetSubject('This has been generated by '.$USER->name.' as a record');
    $doc->SetMargins(15, 30);

    $doc->setPrintHeader(true);
    $doc->setHeaderMargin(10);
    $doc->setHeaderFont(array($fontfamily, 'b', 10));
    $doc->setHeaderData('pix/moodlelogo-med-white.gif', 40, $SITE->fullname, $CFG->wwwroot);
    $resolution= array(1200, 1800);
    $doc->AddPage('P', $resolution);
   // $doc->AddPage();
     //$doc->begin_page_ext(1000, 1000,'');

    $doc->SetTextColor(255,255,255);
    $doc->SetFillColor(255,203,68);
    $doc->SetFont($fontfamily, 'B', 24);
    $doc->Cell(0, 0, 'Summary Record MyCF', 0, 1, 'C', 1);

    $doc->SetFont($fontfamily, '', 12);
    $doc->Ln(6);
    $doc->SetTextColor(0,0,0);
    //$imagewidth = 2000; 
    //$imageheight =  22000;
    //doc->begin_page_ext($imagewidth, $imageheight, "");

    $c  = '<h3>'.$USER->firstname.' '.$USER->lastname.' '.$USER->email.'</h3>';
/*    $c .= 'Moodle release: '            . $CFG->release . '<br />';
    $c .= 'PDF producer: TCPDF '        . $doc->getTCPDFVersion()  . ' (http://www.tcpdf.org) <br />';
    $c .= 'Font of this test page: '    . $fontfamily   . '<br />';

    $c .= '<h3>Current settings</h3>';

    $c .= '<h3>Available font files</h3>';
 */ 
  switch ($mychoice[$mode]) {

    case 'table': 
    		$c .= sum_table_graph($search, $fieldrid,$datefrom,$dateuntil,'table');
	break;
         case 'ring':
    		$c .= ring_display();
	break;
         case 'line':
	      /*$image = $p->load_image("auto", $imagefile, "");
    		if ($image == 0)
        		throw new Exception("Error: " . $p->get_errmsg());

    		/* Get the width and height of the image */
/*    		$imagewidth = $p->info_image($image, "imagewidth", "");
    		$imageheight = $p->info_image($image, "imageheight", "");
    */

    /* Get the width and height of the image */
    		$c .= line_display($datefrom,$dateuntil);
	break;
         case 'bmi':
    		$c .= print_bmi();
	break;

    }
$c .= ShowNotes();
/*fix relative links FPS*/
/*$CFG->wwwroot.'/mod/baseline/php-growth-charts '
 $newtext = str_ireplace($patterns, $replacements, $baseline->{$mode});
*/
  $newtext = str_ireplace('php-growth-charts', $CFG->wwwroot.'/mod/baseline/php-growth-charts',$c);
    $doc->writeHTML($newtext);
    $doc->Output($mychoice[$mode].'_sum.pdf');
    exit();

}
    //echo $OUTPUT->header();
//    echo $OUTPUT->summarys($baseline, $record, $page, $mform);


?>
