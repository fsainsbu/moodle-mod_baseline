<?php  // $Id: tabs.php,v 1.28.2.3 2008/06/12 13:49:40 robertall Exp $
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Martin Dougiamas  http://dougiamas.com             //
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

// This file to be included so we can assume config.php has already been included.
// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab) or empty($baseline) or empty($course)) {
        error('You cannot call this script in that way');
    }
	 $choice = true;

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $inactive = NULL;
    $activetwo = NULL;
    $tabs = array();
    $row = array();
    
    
    // Add an advanced search tab.
// Hidden, but should be for mentor?     $row[] = new tabobject('asearch', $CFG->wwwroot.'/mod/baseline/view.php?d='.$baseline->id.'&amp;mode=asearch', get_string('search', 'baseline'));

    if (isloggedin()) {
        if (baseline_user_can_add_entry($baseline, $currentgroup, $groupmode)) { // took out participation list here!
//add baseline
            $addstring = empty($editentry) ? get_string('base', 'baseline') : get_string('editbase', 'baseline');
            $row[] = new tabobject('base', $CFG->wwwroot.'/mod/baseline/bedit.php?d='.$baseline->id.'&amp;base=1', $addstring);
	//show edit when there is a baseline younger than 90 days.
            if ($true =  1) {
		$addstring = empty($editentry) ? get_string('add', 'baseline') : get_string('editentry', 'baseline');
		$row[] = new tabobject('add', $CFG->wwwroot.'/mod/baseline/edit.php?d='.$baseline->id, $addstring);
		}
        }
    // remove if (isset($record)) {
    	$row[] = new tabobject('sum', $CFG->wwwroot.'/mod/baseline/summary.php?d='.$baseline->id, get_string('summary','baseline'));
    // remove } else { /list and dlist would be here.
        $row[] = new tabobject('vbase', $CFG->wwwroot.'/mod/baseline/view.php?d='.$baseline->id.'&amp;base=1', get_string('vbase','baseline'));
    // remove }
		$row[] = new tabobject('reminder', $CFG->wwwroot.'/mod/baseline/reminder.php?d='.$baseline->id.'&amp;mode=reminder', get_string('reminder','baseline'));
        if ( $choice ) {
		$row[] = new tabobject('choice', $CFG->wwwroot.'/mod/baseline/setting.php?d='.$baseline->id.'&amp;mode=choice', get_string('setting','baseline'));
		}
        if (has_capability(BASELINE_CAP_EXPORT, $context)) {
            // The capability required to Export baselinebase records is centrally defined in 'lib.php'
  // and should be weaker than those required to edit Templates, Fields and Presets. 
           //  $row[] = new tabobject('export', $CFG->wwwroot.'/mod/baseline/export.php?d='.$baseline->id,
              //           get_string('export', 'baseline'));
        }
        if (has_capability('mod/baseline:managetemplates', $context)) {
            if (($currenttab == 'list') ||  ($currenttab == 'dlist')){
                $defaultemplate = 'listtemplate';
            } else if ($currenttab == 'add') {
                $defaultemplate = 'addtemplate';
            } else if ($currenttab == 'asearch') {
                $defaultemplate = 'asearchtemplate';
            } else {
                $defaultemplate = 'singletemplate';
            }

            $row[] = new tabobject('templates', $CFG->wwwroot.'/mod/baseline/templates.php?d='.$baseline->id.'&amp;mode='.$defaultemplate,
                         get_string('templates','baseline'));
            $row[] = new tabobject('fields', $CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id,
                         get_string('fields','baseline'));
            $row[] = new tabobject('presets', $CFG->wwwroot.'/mod/baseline/preset.php?d='.$baseline->id,
                         get_string('presets', 'baseline'));
        }
    }
    $tabs[] = $row;

    if ($currenttab == 'templates' and isset($mode)) {

        $inactive = array();
        $inactive[] = 'templates';
        $templatelist = array ('listtemplate', 'singletemplate', 'asearchtemplate', 'addtemplate', 'rsstemplate', 'csstemplate', 'jstemplate');

        $row  = array();
        $currenttab ='';
        foreach ($templatelist as $template) {
            $row[] = new tabobject($template, "templates.php?d=$baseline->id&amp;mode=$template", get_string($template, 'baseline'));
            if ($template == $mode) {
                $currenttab = $template;
            }
        }
        if ($currenttab == '') {
            $currenttab = $mode = 'singletemplate';
        }
        $tabs[] = $row;
        $activetwo = array('templates');
    }

// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);
         $addstring =$OUTPUT->help_icon("summary", 'baseline', get_string('summary', 'baseline'));
 switch ($currenttab ) {
 case 'base':
         $addstring =$OUTPUT->help_icon("base", 'baseline', get_string('base', 'baseline'));
        break;
 case 'add':
         $addstring =$OUTPUT->help_icon("diary", 'baseline', get_string('diary', 'baseline'));
        break;
 case 'sum':
         $addstring =$OUTPUT->help_icon("summary", 'baseline', get_string('summary', 'baseline'));
        break;
 case 'choice':
         $addstring =$OUTPUT->help_icon("choice", 'baseline', get_string('choice', 'baseline'));
        break;
 case 'reminder':
         $addstring =$OUTPUT->help_icon("reminder", 'baseline', get_string('reminder', 'baseline'));
        break;
 case 'single':
         $addstring =$OUTPUT->help_icon("svbase", 'baseline', get_string('svbase', 'baseline'));
        break;
 case 'vbase':
         $addstring =$OUTPUT->help_icon("vbase", 'baseline', get_string('nvbase', 'baseline'));
        break;
 case '*':
         $addstring =$OUTPUT->help_icon("summary", 'baseline', get_string('summary', 'baseline'));
        break;
}
// echo $currenttab;
echo  $addstring ;
?>
