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
require_once('export_form.php');

// baseline ID
$d = required_param('d', PARAM_INT);

$PAGE->set_url('/mod/baseline/export.php', array('d'=>$d));

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

require_login($course->id, false, $cm);
require_capability(BASELINE_CAP_EXPORT, $context);

// get fields for this baseline
$fieldrecords = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id), 'id');

if(empty($fieldrecords)) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (has_capability('mod/baseline:managetemplates', $context)) {
        redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);
    } else {
        print_error('nofieldinbaseline', 'baseline');
    }
}

// populate objets for this baselines fields
$fields = array();
foreach ($fieldrecords as $fieldrecord) {
    $fields[]= baseline_get_field($fieldrecord, $baseline);
}


$mform = new mod_baseline_export_form('export.php?d='.$baseline->id, $fields, $cm);

if($mform->is_cancelled()) {
    redirect('view.php?d='.$baseline->id);
} elseif (!$formbaseline = (array) $mform->get_baseline()) {
    // build header to match the rest of the UI
    $PAGE->set_title($baseline->name);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $url = new moodle_url('/mod/baseline/export.php', array('d' => $d));
    groups_print_activity_menu($cm, $url);
    echo $OUTPUT->heading(format_string($baseline->name));

    // these are for the tab display
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);
    $currenttab = 'export';
    include('tabs.php');
    $mform->display();
    echo $OUTPUT->footer();
    die;
}

$selectedfields = array();
foreach ($formbaseline as $key => $value) {
    //field form elements are field_1 field_2 etc. 0 if not selected. 1 if selected.
    if (strpos($key, 'field_')===0 && !empty($value)) {
        $selectedfields[] = substr($key, 6);
    }
}

$currentgroup = groups_get_activity_group($cm);

$exportbaseline = baseline_get_exportbaseline($baseline->id, $fields, $selectedfields, $currentgroup);
$count = count($exportbaseline);
switch ($formbaseline['exporttype']) {
    case 'csv':
        baseline_export_csv($exportbaseline, $formbaseline['delimiter_name'], $baseline->name, $count);
        break;
    case 'xls':
        baseline_export_xls($exportbaseline, $baseline->name, $count);
        break;
    case 'ods':
        baseline_export_ods($exportbaseline, $baseline->name, $count);
        break;
}

die();
