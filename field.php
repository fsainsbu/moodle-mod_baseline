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
 * This file is part of the Baselinebase module for Moodle
 *
 * @copyright 2005 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod-baseline
 */

require_once('../../config.php');
require_once('lib.php');

$id             = optional_param('id', 0, PARAM_INT);            // course module id
$d              = optional_param('d', 0, PARAM_INT);             // baseline id
$fid            = optional_param('fid', 0 , PARAM_INT);          // update field id
$newtype        = optional_param('newtype','',PARAM_ALPHA);      // type of the new field
$mode           = optional_param('mode','',PARAM_ALPHA);
$defaultsort    = optional_param('defaultsort', 0, PARAM_INT);
$defaultsortdir = optional_param('defaultsortdir', 0, PARAM_INT);
$cancel         = optional_param('cancel', 0, PARAM_BOOL);

if ($cancel) {
    $mode = 'list';
}

$url = new moodle_url('/mod/baseline/field.php');
if ($fid !== 0) {
    $url->param('fid', $fid);
}
if ($newtype !== '') {
    $url->param('newtype', $newtype);
}
if ($mode !== '') {
    $url->param('mode', $mode);
}
if ($defaultsort !== 0) {
    $url->param('defaultsort', $defaultsort);
}
if ($defaultsortdir !== 0) {
    $url->param('defaultsortdir', $defaultsortdir);
}
if ($cancel !== 0) {
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
        print_error('invalidcoursemodule');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course->id, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/baseline:managetemplates', $context);

/************************************
 *        Baseline Processing           *
 ***********************************/
switch ($mode) {

    case 'add':    ///add a new field
        if (confirm_sesskey() and $fieldinput = data_submitted()){

            //$fieldinput->name = baseline_clean_field_name($fieldinput->name);

        /// Only store this new field if it doesn't already exist.
            if (($fieldinput->name == '') or baseline_fieldname_exists($fieldinput->name, $baseline->id)) {

                $displaynoticebad = get_string('invalidfieldname','baseline');

            } else {

            /// Check for arrays and convert to a comma-delimited string
                baseline_convert_arrays_to_strings($fieldinput);

            /// Create a field object to collect and store the baseline safely
                $type = required_param('type', PARAM_FILE);
                $field = baseline_get_field_new($type, $baseline);

                $field->define_field($fieldinput);
                $field->insert_field();

            /// Update some templates
                baseline_append_new_field_to_templates($baseline, $fieldinput->name);

                add_to_log($course->id, 'baseline', 'fields add',
                           "field.php?d=$baseline->id&amp;mode=display&amp;fid=$fid", $fid, $cm->id);

                $displaynoticegood = get_string('fieldadded','baseline');
            }
        }
        break;


    case 'update':    ///update a field
        if (confirm_sesskey() and $fieldinput = data_submitted()){

            //$fieldinput->name = baseline_clean_field_name($fieldinput->name);

            if (($fieldinput->name == '') or baseline_fieldname_exists($fieldinput->name, $baseline->id, $fieldinput->fid)) {

                $displaynoticebad = get_string('invalidfieldname','baseline');

            } else {
            /// Check for arrays and convert to a comma-delimited string
                baseline_convert_arrays_to_strings($fieldinput);

            /// Create a field object to collect and store the baseline safely
                $field = baseline_get_field_from_id($fid, $baseline);
                $oldfieldname = $field->field->name;

                $field->field->name = $fieldinput->name;
                $field->field->description = $fieldinput->description;

                for ($i=1; $i<=10; $i++) {
                    if (isset($fieldinput->{'param'.$i})) {
                        $field->field->{'param'.$i} = $fieldinput->{'param'.$i};
                    } else {
                        $field->field->{'param'.$i} = '';
                    }
                }

                $field->update_field();

            /// Update the templates.
                baseline_replace_field_in_templates($baseline, $oldfieldname, $field->field->name);

                add_to_log($course->id, 'baseline', 'fields update',
                           "field.php?d=$baseline->id&amp;mode=display&amp;fid=$fid", $fid, $cm->id);

                $displaynoticegood = get_string('fieldupdated','baseline');
            }
        }
        break;


    case 'delete':    // Delete a field
        if (confirm_sesskey()){

            if ($confirm = optional_param('confirm', 0, PARAM_INT)) {


                // Delete the field completely
                if ($field = baseline_get_field_from_id($fid, $baseline)) {
                    $field->delete_field();

                    // Update the templates.
                    baseline_replace_field_in_templates($baseline, $field->field->name, '');

                    // Update the default sort field
                    if ($fid == $baseline->defaultsort) {
                        $rec = new stdClass();
                        $rec->id = $baseline->id;
                        $rec->defaultsort = 0;
                        $rec->defaultsortdir = 0;
                        $DB->update_record('baseline', $rec);
                    }

                    add_to_log($course->id, 'baseline', 'fields delete',
                               "field.php?d=$baseline->id", $field->field->name, $cm->id);

                    $displaynoticegood = get_string('fielddeleted', 'baseline');
                }

            } else {

                baseline_print_header($course,$cm,$baseline, false);

                // Print confirmation message.
                $field = baseline_get_field_from_id($fid, $baseline);

                echo $OUTPUT->confirm('<strong>'.$field->name().': '.$field->field->name.'</strong><br /><br />'. get_string('confirmdeletefield','baseline'),
                             'field.php?d='.$baseline->id.'&mode=delete&fid='.$fid.'&confirm=1',
                             'field.php?d='.$baseline->id);

                echo $OUTPUT->footer();
                exit;
            }
        }
        break;


    case 'sort':    // Set the default sort parameters
        if (confirm_sesskey()) {
            $rec = new stdClass();
            $rec->id = $baseline->id;
            $rec->defaultsort = $defaultsort;
            $rec->defaultsortdir = $defaultsortdir;

            $DB->update_record('baseline', $rec);
            redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id, get_string('changessaved'), 2);
            exit;
        }
        break;

    default:
        break;
}



/// Print the browsing interface

///get the list of possible fields (plugins)
$directories = get_list_of_plugins('mod/baseline/field/');
$menufield = array();
foreach ($directories as $directory){
    $menufield[$directory] = get_string($directory,'baseline');    //get from language files
}
asort($menufield);    //sort in alphabetical order
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);

$PAGE->set_pagetype('mod-baseline-field-' . $newtype);
if (($mode == 'new') && (!empty($newtype)) && confirm_sesskey()) {          ///  Adding a new field
    baseline_print_header($course, $cm, $baseline,'fields');

    $field = baseline_get_field_new($newtype, $baseline);
    $field->display_edit_field();

} else if ($mode == 'display' && confirm_sesskey()) { /// Display/edit existing field
    baseline_print_header($course, $cm, $baseline,'fields');

    $field = baseline_get_field_from_id($fid, $baseline);
    $field->display_edit_field();

} else {                                              /// Display the main listing of all fields
    baseline_print_header($course, $cm, $baseline,'fields');

    if (!$DB->record_exists('baseline_fields', array('baselineid'=>$baseline->id))) {
        echo $OUTPUT->notification(get_string('nofieldinbaseline','baseline'));  // nothing in baseline
        echo $OUTPUT->notification(get_string('pleaseaddsome','baseline', 'preset.php?id='.$cm->id));      // link to presets

    } else {    //else print quiz style list of fields

        $table = new html_table();
        $table->head = array(get_string('fieldname','baseline'), get_string('type','baseline'), get_string('fielddescription', 'baseline'), get_string('action','baseline'));
        $table->align = array('left','left','left', 'center');
        $table->wrap = array(false,false,false,false);

        if ($fff = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id),'id')){
            foreach ($fff as $ff) {

                $field = baseline_get_field($ff, $baseline);
                $table->data[] = array(

                '<a href="field.php?mode=display&amp;d='.$baseline->id.
                '&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.$field->field->name.'</a>',

                $field->image().'&nbsp;'.get_string($field->type, 'baseline'),

                shorten_text($field->field->description, 30),

                '<a href="field.php?d='.$baseline->id.'&amp;mode=display&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.
                '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="'.get_string('edit').'" title="'.get_string('edit').'" /></a>'.
                '&nbsp;'.
                '<a href="field.php?d='.$baseline->id.'&amp;mode=delete&amp;fid='.$field->field->id.'&amp;sesskey='.sesskey().'">'.
                '<img src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="'.get_string('delete').'" title="'.get_string('delete').'" /></a>'

                );
            }
        }
        echo html_writer::table($table);
    }


    echo '<div class="fieldadd">';
    echo '<label for="fieldform_jump">'.get_string('newfield','baseline').'</label>';
    $popupurl = $CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id.'&mode=new&sesskey='.  sesskey();
    echo $OUTPUT->single_select(new moodle_url($popupurl), 'newtype', $menufield, null, array(''=>'choosedots'), 'fieldform');
    echo $OUTPUT->help_icon('newfield', 'baseline');
    echo '</div>';

    echo '<div class="sortdefault">';
    echo '<form id="sortdefault" action="'.$CFG->wwwroot.'/mod/baseline/field.php" method="get">';
    echo '<div>';
    echo '<input type="hidden" name="d" value="'.$baseline->id.'" />';
    echo '<input type="hidden" name="mode" value="sort" />';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<label for="defaultsort">'.get_string('defaultsortfield','baseline').'</label>';
    echo '<select id="defaultsort" name="defaultsort">';
    if ($fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id))) {
        echo '<optgroup label="'.get_string('fields', 'baseline').'">';
        foreach ($fields as $field) {
            if ($baseline->defaultsort == $field->id) {
                echo '<option value="'.$field->id.'" selected="selected">'.$field->name.'</option>';
            } else {
                echo '<option value="'.$field->id.'">'.$field->name.'</option>';
            }
        }
        echo '</optgroup>';
    }
    $options = array();
    $options[BASELINE_TIMEADDED]    = get_string('timeadded', 'baseline');
// TODO: we will need to change defaultsort db to unsinged to make these work in 2.0
/*        $options[BASELINE_TIMEMODIFIED] = get_string('timemodified', 'baseline');
    $options[BASELINE_FIRSTNAME]    = get_string('authorfirstname', 'baseline');
    $options[BASELINE_LASTNAME]     = get_string('authorlastname', 'baseline');
    if ($baseline->approval and has_capability('mod/baseline:approve', $context)) {
        $options[BASELINE_APPROVED] = get_string('approved', 'baseline');
    }*/
    echo '<optgroup label="'.get_string('other', 'baseline').'">';
    foreach ($options as $key => $name) {
        if ($baseline->defaultsort == $key) {
            echo '<option value="'.$key.'" selected="selected">'.$name.'</option>';
        } else {
            echo '<option value="'.$key.'">'.$name.'</option>';
        }
    }
    echo '</optgroup>';
    echo '</select>';

    $options = array(0 => get_string('ascending', 'baseline'),
                     1 => get_string('descending', 'baseline'));
    echo html_writer::select($options, 'defaultsortdir', $baseline->defaultsortdir, false);
    echo '<input type="submit" value="'.get_string('save', 'baseline').'" />';
    echo '</div>';
    echo '</form>';
    echo '</div>';

}

/// Finish the page
echo $OUTPUT->footer();

