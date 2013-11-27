<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is part of the Database module for Moodle
 *
 * @copyright 2005 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod-baseline
 */

require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/rsslib.php");

$id    = optional_param('id', 0, PARAM_INT);    // course module id
$d     = optional_param('d', 0, PARAM_INT);    // baselinebase id
$rid   = optional_param('rid', 0, PARAM_INT);    //record id
$cancel   = optional_param('cancel', '', PARAM_RAW);    // cancel an add
$mode ='addtemplate';    //define the mode for this page, only 1 mode available
$base   = optional_param('base', 1, PARAM_INT);    //base  not entry
$import   = optional_param('import', 0, PARAM_INT);    // show import form
$user = optional_param('user', '', PARAM_INT);    //user to work on
       // user check with $USER-id in mentors or admins.`
    global $DB,$CFG;
         if ($base) {
        $_SESSION['base']=$base;
        }

$url = new moodle_url('/mod/baseline/bedit.php');
if ($rid !== 0) {
    $url->param('rid', $rid);
}

if ($cancel !== '') {
    $url->param('cancel', $cancel);
}

if ($id) {
    $url->param('id', $id);
    $PAGE->set_url($url);
    if (! $cm = get_coursemodule_from_id('baseline', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $baseline = $DB->get_record('baseline', array('id'=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $url->param('d', $d);
    $PAGE->set_url($url);
    if (! $baseline = $DB->get_record('baseline', array('id'=>$d))) {
        print_error('invalidid', 'baseline');
    }
    if (! $course = $DB->get_record('course', array('id'=>$baseline->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course->id, false, $cm);
	$choice = false;
	$choice = true;
	$graph = true;
	$graph = false;
	if ( $choice ) {
     $choisesql = "select d.id, f.name from {baseline_field2user} d,{baseline_fields} f where d.userid = ". $USER->id." AND f.baselineid = ".$baseline->id ." and f.id = d.field_id";
    if (!$chosen = $DB->get_records_sql($choisesql)){
         redirect('setting.php?d='.$baseline->id.'&mode=choice');
    }
}

if (!isloggedin() or isguestuser()) {
    redirect('summary.php?d='.$baseline->id);
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

	 if ((!$rid) && ($base)) {    //  Norid, is there one already for today and So do you have access?
        //get record created TODAY for this DIARY
        $sql = "select id from {baseline_base_records} where baselineid = ".$baseline->id." and  FROM_UNIXTIME(timecreated) > curdate() and userid = ". $USER->id;
              if ($contents = $DB->get_records_sql($sql)) {
        foreach($contents as $content) {
            $field = $DB->get_record('baseline_base_records',array('id'=>$content->id));
           redirect($CFG->wwwroot.'/mod/baseline/bedit.php?d='.$baseline->id.'&amp;rid='.$field->id.'&amp;base='.$base.'&amp;sesskey='.sesskey());
		}
         }
    } else {
        if (!(has_capability('mod/baseline:manageentries', $context) or baseline_isowner($rid))  ) {
            print_error('noaccess','baseline');
    }
    }
/// If it's hidden then it doesn't show anything.  :)
if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    $strbaselinebases = get_string("modulenameplural", "baseline");

    $PAGE->set_title(format_string($baseline->name));
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
}

/// Can't use this if there are no fields
if (has_capability('mod/baseline:managetemplates', $context)) {
    if (!$DB->record_exists('baseline_fields', array('baselineid'=>$baseline->id))) {      // Brand new baselinebase!
        redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);  // Redirect to field entry
    }
}
	 $FirstEntry = false;	
            if (! $mybase = $DB->get_records('baseline_base_records',array('userid'=>$USER->id))) {
			$FirstEntry = true;
			///fps switch for baseline, or bedit.php
             //redirect($CFG->wwwroot.'/mod/baseline/bedit.php?d='.$baseline->id.'&amp;base='.$base);  // Redirect to base entry
		 $isEntry=1;
              }
	      else  $isEntry=0;
// fix wether base or entry by setting filename
             $CFG->base    = $base;
            $CFG->user    = $USER->id;
 if ($base) {
        $my_base_file = 'baseline_base_content';
 		    $my_record_table = 'baseline_base_records';
            } else  {
                    $my_base_file = 'baseline_content' ;
 		    $my_record_table = 'baseline_records';
            }

if ($rid) {    // So do you have access?
    if (!(has_capability('mod/baseline:manageentries', $context) or baseline_isowner($rid)) or !confirm_sesskey() ) {
        print_error('noaccess','baseline');
    }
}

if ($cancel) {
    redirect('summary.php?d='.$baseline->id);
}


/// RSS and CSS and JS meta
if (!empty($CFG->enablerssfeeds) && !empty($CFG->baseline_enablerssfeeds) && $baseline->rssarticles > 0) {
    $rsspath = rss_get_url($context->id, $USER->id, 'mod_baseline', $baseline->id);
    $courseshortname = format_string($course->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
    $PAGE->add_alternate_version($courseshortname . ': %fullname%', $rsspath, 'application/rss+xml');
}
if ($baseline->csstemplate) {
    $PAGE->requires->css('/mod/baseline/css.php?d='.$baseline->id);
}
if ($baseline->jstemplate) {
    $PAGE->requires->js('/mod/baseline/js.php?d='.$baseline->id, true);
}

$possiblefields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id), 'id');

foreach ($possiblefields as $field) {
    if ($field->type == 'file' || $field->type == 'picture') {
        require_once($CFG->dirroot.'/repository/lib.php');
        break;
    }
}

/// Define page variables
$strbaseline = get_string('modulenameplural','baseline');

if ($rid) {
    $PAGE->navbar->add(get_string('editbase', 'baseline'));
}

$PAGE->set_title($baseline->name);
$PAGE->set_heading($course->fullname);

/// Check to see if groups are being used here
$currentgroup = groups_get_activity_group($cm);
$groupmode = groups_get_activity_groupmode($cm);

if ($currentgroup) {
    $groupselect = " AND groupid = '$currentgroup'";
    $groupparam = "&amp;groupid=$currentgroup";
} else {
    $groupselect = "";
    $groupparam = "";
    $currentgroup = 0;
}


/// Process incoming baseline for adding/updating records

if ($baselinerecord = data_submitted() and confirm_sesskey()) {

    $ignorenames = array('MAX_FILE_SIZE','sesskey','d','rid','saveandview','cancel');  // strings to be ignored in input baseline

    if ($rid) {                                          /// Update some records

        /// All student edits are marked unapproved by default
        $record = $DB->get_record('baseline_records', array('id'=>$rid));

        /// reset approved flag after student edit
        if (!has_capability('mod/baseline:approve', $context)) {
            $record->approved = 0;
        }

        $record->groupid = $currentgroup;
        $record->timemodified = time();
        $DB->update_record('baseline_records', $record);

        /// Update all content
        $field = NULL;
        foreach ($baselinerecord as $name => $value) {
            if (!in_array($name, $ignorenames)) {
                $namearr = explode('_',$name);  // Second one is the field id
                if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                    $field = baseline_get_field_from_id($namearr[1], $baseline);
                }
                if ($field) {
		     $field->which_set($base);
                    $field->update_content($rid, $value, $name);
                }
            }
        }

        add_to_log($course->id, 'baseline', 'update', "view.php?d=$baseline->id.'&amp;base='.$base.&amp;rid=$rid", $baseline->id, $cm->id);

        redirect($CFG->wwwroot.'/mod/baseline/view.php?d='.$baseline->id.'&rid='.$rid.'&amp;base='.$base);

    } else { /// Add some new records

        if (!baseline_user_can_add_entry($baseline, $currentgroup, $groupmode, $context)) {
            print_error('cannotadd', 'baseline');
        }

    /// Check if maximum number of entry as specified by this baselinebase is reached
    /// Of course, you can't be stopped if you are an editting teacher! =)

        if (baseline_atmaxentries($baseline) and !has_capability('mod/baseline:manageentries',$context)){
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('atmaxentry','baseline'));
            echo $OUTPUT->footer();
            exit;
        }

        ///Empty form checking - you can't submit an empty form!

        $emptyform = true;      // assume the worst

        foreach ($baselinerecord as $name => $value) {
            if (!in_array($name, $ignorenames)) {
                $namearr = explode('_', $name);  // Second one is the field id
                if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                    $field = baseline_get_field_from_id($namearr[1], $baseline);
                }
                if ($field->notemptyfield($value, $name)) {
                    $emptyform = false;
                    break;             // if anything has content, this form is not empty, so stop now!
                }
            }
        }

        if ($emptyform){    //nothing gets written to baselinebase
            echo $OUTPUT->notification(get_string('emptyaddform','baseline'));
        }

        if (!$emptyform && $recordid = baseline_add_record($baseline, $currentgroup)) {    //add instance to baseline_record

            /// Insert a whole lot of empty records to make sure we have them
            $fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id));
            foreach ($fields as $field) {
                $content->recordid = $recordid;
                $content->fieldid = $field->id;
                $DB->insert_record('baseline_content',$content);
            }

            /// For each field in the add form, add it to the baseline_content.
            foreach ($baselinerecord as $name => $value){
                if (!in_array($name, $ignorenames)) {
                    $namearr = explode('_', $name);  // Second one is the field id
                    if (empty($field->field) || ($namearr[1] != $field->field->id)) {  // Try to reuse classes
                        $field = baseline_get_field_from_id($namearr[1], $baseline);
                    }
                    if ($field) {
			$field->which_set($base);
                        $field->update_content($recordid, $value, $name);
                    }
                }
            }

            add_to_log($course->id, 'baseline', 'add', "view.php?d=$baseline->id.'&amp;base='.$base.&amp;rid=$recordid", $baseline->id, $cm->id);

            if (!empty($baselinerecord->saveandview)) {
                redirect($CFG->wwwroot.'/mod/baseline/view.php?d='.$baseline->id.'&amp;base='.$base.'&rid='.$recordid);
            }
        }
    }
}  // End of form processing


/// Print the page header

echo $OUTPUT->header();
groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/baseline/bedit.php?d='.$baseline->id.'&amp;base='.$base);
echo $OUTPUT->heading(format_string($baseline->name));

/// Print the tabs
///fps switch for baseline, or bedit.php
   if ($base) {
    $currenttab = 'base';
   } else {
    $currenttab = 'add';
    }

if ($rid) {
    $editentry = true;  //used in tabs
}
include('tabs.php');


/// Print the browsing interface

$patterns = array();    //tags to replace
$replacement = array();    //html to replace those yucky tags

//form goes here first in case add template is empty
echo '<form enctype="multipart/form-baseline" action="bedit.php" method="post">';
echo '<div>';
echo '<input name="d" value="'.$baseline->id.'" type="hidden" />';
echo '<input name="rid" value="'.$rid.'" type="hidden" />';
echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />';
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

if (!$rid){
    echo $OUTPUT->heading(get_string('editbase','baseline'), 2);
}
if ($FirstEntry) $OUTPUT->heading(get_string('firstbaseline','baseline'));

/******************************************
 * Regular expression replacement section *
 ******************************************/
if ($baseline->addtemplate){
    $possiblefields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id), 'id');
    $patterns = array();
    $replacements = array();

    ///then we generate strings to replace
    foreach ($possiblefields as $eachfield){
        $field = baseline_get_field($eachfield, $baseline);
        $field->which_set($base);
        $unused = $field->get_bases($rid);
        $answer2 =  explode("?",$field->field->description);
        $mydescription =  $answer2[0]."?";
         $patterns[]=$field->field->name.":";
        $patterns[]="[[".$field->field->name."]]";
	if (($eachfield->param4[date('w')] == '1') && ($myff= $DB->get_records_select('baseline_field2user', 'userid = '. $USER->id. ' AND field_id = '. $field->field->id,array('id DESC'))|| (! $choice) ))
	{
//            $replacements[] = '<tr class="question '.$field->field->param2.'"> <td class="title"> <h2> '.ucfirst($field->field->name).'</h2><p>' .$mydescription.'</p></td></tr>';
/** need to change field name to some other thing.   FPS Oct 2010  was name now param5 change in DB*/
            $replacements[] = '<tr class="question '.$field->field->param2.'"> <td class="title"> <h2> '.ucfirst($field->field->param5).'</h2><p>' .$mydescription.'</p></td></tr>';

            // remove previous $replacements[] =$field->get_bases($rid).if ($graph) { $field->display_graph($rid).}$field->display_add_field($rid).$field->display_icns($rid);
            //$replacements[] =$field->get_bases($rid). $field->display_graph($rid).$field->display_add_field($rid);
            if ($graph) { $replacements[] = $field->display_graph($rid).$field->display_add_field($rid);
} else {
            if (! $base ) $replacements[] = $field->display_icn($rid).$field->display_add_field($rid); else $replacements[] = $field->display_add_field($rid);
}
//Frank removed        $patterns[]="[[".$field->field->name."#id]]";
        //Frank removed $replacements[] = 'field_'.$field->field->id;
    }
    }
    $newtext = str_ireplace($patterns, $replacements, $baseline->{$mode});

} else {    //if the add template is not yet defined, print the default form!
    echo baseline_generate_default_template($baseline, 'addtemplate', $rid, true, false);
    $newtext = '';
}

echo $newtext;

echo '<div class="mdl-align"><input type="submit" name="saveandview" value="'.get_string('saveandview','baseline').'" />';
if ($rid) {
    echo '&nbsp;<input type="submit" name="cancel" value="'.get_string('cancel').'" onclick="javascript:history.go(-1)" />';
} else {
   // if ((!$baseline->maxentries) || has_capability('mod/baseline:manageentries', $context) || (baseline_numentries($baseline) < ($baseline->maxentries - 1))) {
        //echo '&nbsp;<input type="submit" value="'.get_string('saveandadd','baseline').'" />';
    //}
}
echo '</div>';
echo $OUTPUT->box_end();
echo '</div></form>';


/// Finish the page

// Print the stuff that need to come after the form fields.
if (!$fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id))) {
    print_error('nofieldinbaselinebase', 'baseline');
}
foreach ($fields as $eachfield) {
    $field = baseline_get_field($eachfield, $baseline);
    $field->print_after_form();
}

echo $OUTPUT->footer();
