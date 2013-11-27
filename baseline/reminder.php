<?php  // $Id: reminder.php,v 1.1.2.2 2008/06/12 13:49:40 robertall Exp $

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('reminder_form.php');
     $CFG->base    = false;

$d = required_param('d', PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHA);    // Display or choose
// baselinebase ID

if (! $baseline = $DB->get_record('baseline', array('id'=>$d))) {
    print_error('wrongbaselineid', 'baseline');
}

if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $baseline->course)) {
    print_error('invalidcoursemodule');
}
if(! $course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('invalidcourseid', '', '', $cm->course);
}
// fill in missing properties needed for updating of instance
$baseline->course     = $cm->course;
$baseline->cmidnumber = $cm->idnumber;
$baseline->instance   = $cm->instance;

if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('invalidcontext', '');
}
if( $mode=='reminder'){
// on logout id still set  FPS
require_login($course->id, false, $cm);
$PAGE->set_context($context);
}
 $mform = new mod_data_reminder('reminder.php?d='.$baseline->id,10, $USER->id,$mode);
// require_capability(BASELINE_CAP_EXPORT, $context);
//Removed above for using choice, then no need to overide in View presets from all users moodle
$PAGE->set_url($FULLME);
// get Reminders
$fieldrecords =  $DB->get_records('local_reminders');
//$DB->get_records('baseline_fields','baselineid', $baseline->id, 'id');

if(empty($fieldrecords)) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (has_capability('mod/baseline:managetemplates', $context)) {
        redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);
    } else {
        print_error('nofieldinbaselinebase', 'baseline');
    }
}

// populate objets for this baselinebases fields
$fields = array();
foreach ($fieldrecords as $fieldrecord) {
    //$fields[]= baseline_get_field($fieldrecord, $baseline);
    $fields[]=$fieldrecord;
}

 $mform = new mod_data_reminder('reminder.php?d='.$baseline->id, $fields,$USER->id,$mode);

if($mform->is_cancelled()) {
   redirect('edit.php?d='.$baseline->id);
} elseif (!$formbaseline = (array) $mform->get_data()) {
    // build header to match the rest of the UI

    //  $nav = build_navigation('', $cm);
    //  print_header_simple($baseline->name, '', $nav,
      //   '', '', true, update_module_button($cm->id, $course->id, get_string('modulename', 'baseline')),
        // navmenu($course, $cm), '', '');
 $PAGE->set_title(format_string($baseline->name));
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();
 
     $OUTPUT->heading(format_string($baseline->name));

    // these are for the tab display
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);
    $currenttab = 'reminder';
//    $isEntry = true;
  if (! $mybase = $DB->get_records('baseline_base_records', array('userid'=>$USER->id))) {
                        $FirstEntry = true;
                        ///fps switch for baseline, or bedit.php
             //redirect($CFG->wwwroot.'/mod/baseline/bedit.php?d='.$baseline->id.'&amp;base='.$base);  // Redirect to base entry
                 $isEntry=1;
              }
              else  $isEntry=0;
  $PAGE->set_title(format_string($baseline->name));
    $PAGE->set_heading(format_string($course->fullname));
     $OUTPUT->heading(format_string($baseline->name));

    include('tabs.php');
	$CFG->mymode=$mode;
    $mform->display();
     $OUTPUT->footer();
    die;
 }

$exportbaseline = array();

       $myrec = new object();
         $myrec->id = $USER->id;
         $myrec->userid = $USER->id;
// populate the header in first row of export
if( $mode=='reminder'){
foreach($fields as $key => $field) {
                $DB->delete_records('baseline_reminder2user', array('userid'=> $USER->id, 'field_id'=> $field->id));
    if(empty($formbaseline['field_'.$field->id])) {
                $DB->delete_records('baseline_reminder2user', array('userid'=> $USER->id, 'field_id'=> $field->id));
    } else {
              $myrec->field_id = $field->id;
	     if (! $DB->insert_record('baseline_reminder2user', $myrec)) {
                error("Could not insert a new reminder ");//($myrec->id = $content[$field->id]))");
        }
    }
    } //choose

//$baselinerecords = $DB->get_records('baseline_records', 'baselineid', $baseline->id);
//ksort($baselinerecords);
//$line = 1;
//foreach($baselinerecords as $record) {
    // get content indexed by fieldid
 //   if( $content = $DB->get_records('baseline_content', 'recordid', $record->id, 'fieldid', 'fieldid, content, content1, content2, content3, content4') ) {
       /* $myrec = new object();
         $myrec->id = $USER->id;
         $myrec->userid = $USER->id;
        foreach($fields as $field) {
        // delete old ones  if exist userid fieldid
 if ($oldchoice = $DB->get_record('baseline_reminder2user', 'userid', $USER->id, 'field_id', $field->id)) {
                $DB->delete_records('baseline_reminder2user', 'userid', $USER->id, 'field_id', $field->id);
              }
         
            if(isset($formbaseline['field_'.$field->id])) {
            // insert with write here.
              $myrec->field_id = $field->id;
             print_r($myrec);
	     if (! $DB->insert_record('baseline_reminder2user', $myrec)) {
                error("Could not insert a new choice ");//($myrec->id = $content[$field->id]))");
        }
    }
*/
}
   redirect('edit.php?d='.$baseline->id);
//$line--;

/*switch ($formbaseline['exporttype']) {
    case 'csv':
        baseline_export_csv($exportbaseline, $formbaseline['delimiter_name'], $baseline->name, $line);
        break;
    case 'xls':
        baseline_export_xls($exportbaseline, $baseline->name, $line);
        break;
    case 'ods':
        baseline_export_ods($exportbaseline, $baseline->name, $line);
        break;
}
*/


function mine_baseline_export_csv($export, $delimiter_name, $baselinename, $count) {
    $delimiter = csv_import_reader::get_delimiter($delimiter_name);
    $filename = clean_filename("${baselinename}-${count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= clean_filename("-${delimiter_name}_separated");
    $filename .= '.csv';
    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=$filename");
    header('Expires: 0');
    header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
    header('Pragma: public');
    $encdelim = '&#' . ord($delimiter) . ';';
    foreach($export as $row) {
        foreach($row as $key => $column) {
            $row[$key] = str_replace($delimiter, $encdelim, $column);
        }
        echo implode($delimiter, $row) . "\n";
    }
    die;
}


function mine_baseline_export_xls($export, $baselinename, $count) {
    global $DB,$CFG;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename("${baselinename}-${count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.xls';
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] =& $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    die;
}


function mine_baseline_export_ods($export, $baselinename, $count) {
    global $DB,$CFG;
    require_once("$CFG->libdir/odslib.class.php");
    $filename = clean_filename("${baselinename}-${count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.ods';
    $workbook = new MoodleODSWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] =& $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    die;
}
