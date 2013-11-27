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

/* Skeletal to replace setting and reminder
 needs userid effective.
formslib be used?
*/
//define('CLI_SCRIPT', true);

    require_once('../../config.php');
    require_once('lib.php');
    require_once($CFG->libdir . '/pdflib.php');
    require_once($CFG->libdir . '/csvlib.class.php');
    require_once('reminder_form.php');
    // global $USER,$CFG;
    $CFG->base    = false;

    $getpdf     = optional_param('getpdf', 0, PARAM_INT);
    $fontfamily = optional_param('fontfamily', PDF_DEFAULT_FONT, PARAM_ALPHA);  // to be configurable
    $d  = required_param('d', PARAM_INT);   // Record ID
    $page = optional_param('page', 0, PARAM_INT);   // Page ID
    $mode = optional_param('mode', '', PARAM_ALPHA);    // Display or choose


/**
 * Extend the standard PDF class to get access to some protected values we want to display
 * at the test page.
 *
 * @copyright 2009 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
if( $mode=='reminder'){
// on logout id still set  FPS
require_login($course->id, false, $cm);
}
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

   if (! $context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
       echo $OUTPUT->error('invalidcontext', '');
   }



   $PAGE->set_pagelayout('standard');
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
//   $fieldrecords =  $DB->get_records('baseline_fields',array('baselineid'=>$baseline->id));
   $fields = array();
   foreach ($fieldrecords as $fieldrecord) {
      $fields[]= $fieldrecord;
   }

   if( $mode=='reminder'){
   // on logout id still set  FPS
   require_login($course->id, false, $cm);
   $PAGE->set_context($context);
   }
   $mform = new mod_data_reminder('reminder.php?d='.$baseline->id.'&mode=reminder', $fields,$USER->id,$mode);
   $PAGE->set_title(format_string($baseline->name));
   $PAGE->set_heading(format_string($course->fullname));

// get fields for this baselinebase

if(empty($fieldrecords)) {
   $context = get_context_instance(CONTEXT_MODULE, $cm->id);
   if (has_capability('mod/baseline:managetemplates', $context)) {
        redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);
   } else {
        print_error('nofieldinbaselinebase', 'baseline');
   }
}



if($mform->is_cancelled()) {
   redirect('edit.php?d='.$baseline->id);
} elseif (!$formbaseline = (array) $mform->get_data()) {
    // build header to match the rest of the UI
    $PAGE->set_title(format_string($baseline->name));
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();
    $url = new moodle_url('/mod/baseline/reminder.php', array('d' => $d,'mode' => 'reminder'));
    groups_print_activity_menu($cm, $url);
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
    include('tabs.php');
    $CFG->mymode=$mode;
    $mform->display();
    echo $OUTPUT->footer();
} else {

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

}
//   redirect('edit.php?d='.$baseline->id);
}
?>
