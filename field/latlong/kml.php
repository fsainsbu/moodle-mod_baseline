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

// A lot of this initial stuff is copied from mod/baseline/view.php

require_once('../../../../config.php');
require_once('../../lib.php');

// Optional params: row id "rid" - if set then export just one, otherwise export all

$d       = required_param('d', PARAM_INT);   // baseline id
$fieldid = required_param('fieldid', PARAM_INT);   // field id
$rid     = optional_param('rid', 0, PARAM_INT);    //record id

$url = new moodle_url('/mod/baseline/field/latlong/kml.php', array('d'=>$d, 'fieldid'=>$fieldid));
if ($rid !== 0) {
    $url->param('rid', $rid);
}
$PAGE->set_url($url);

if ($rid) {
    if (! $record = $DB->get_record('baseline_records', array('id'=>$rid))) {
        print_error('invalidrecord', 'baseline');
    }
    if (! $baseline = $DB->get_record('baseline', array('id'=>$record->baselineid))) {
        print_error('invalidid', 'baseline');
    }
    if (! $course = $DB->get_record('course', array('id'=>$baseline->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    if (! $field = $DB->get_record('baseline_fields', array('id'=>$fieldid))) {
        print_error('invalidfieldid', 'baseline');
    }
    if (! $field->type == 'latlong') { // Make sure we're looking at a latlong baseline type!
        print_error('invalidfieldtype', 'baseline');
    }
    if (! $content = $DB->get_record('baseline_content', array('fieldid'=>$fieldid, 'recordid'=>$rid))) {
        print_error('nofieldcontent', 'baseline');
    }
} else {   // We must have $d
    if (! $baseline = $DB->get_record('baseline', array('id'=>$d))) {
        print_error('invalidid', 'baseline');
    }
    if (! $course = $DB->get_record('course', array('id'=>$baseline->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    if (! $field = $DB->get_record('baseline_fields', array('id'=>$fieldid))) {
        print_error('invalidfieldid', 'baseline');
    }
    if (! $field->type == 'latlong') { // Make sure we're looking at a latlong baseline type!
        print_error('invalidfieldtype', 'baseline');
    }
    $record = NULL;
}

require_course_login($course, true, $cm);

/// If it's hidden then it's don't show anything.  :)
if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities',get_context_instance(CONTEXT_MODULE, $cm->id))) {
    $PAGE->set_title($baseline->name);
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
}

/// If we have an empty Baselinebase then redirect because this page is useless without baseline
if (has_capability('mod/baseline:managetemplates', $context)) {
    if (!$DB->record_exists('baseline_fields', array('baselineid'=>$baseline->id))) {      // Brand new baseline!
        redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);  // Redirect to field entry
    }
}




//header('Content-type: text/plain'); // This is handy for debug purposes to look at the KML in the browser
header('Content-type: application/vnd.google-earth.kml+xml kml');
header('Content-Disposition: attachment; filename="moodleearth-'.$d.'-'.$rid.'-'.$fieldid.'.kml"');


echo baseline_latlong_kml_top();

if($rid) { // List one single item
    $pm = new stdClass();
    $pm->name = baseline_latlong_kml_get_item_name($content, $field);
    $pm->description = "&lt;a href='$CFG->wwwroot/mod/baseline/view.php?d=$d&amp;rid=$rid'&gt;Item #$rid&lt;/a&gt; in Moodle baseline activity";
    $pm->long = $content->content1;
    $pm->lat = $content->content;
    echo baseline_latlong_kml_placemark($pm);
} else {   // List all items in turn

    $contents = $DB->get_records('baseline_content', array('fieldid'=>$fieldid));

    echo '<Document>';

    foreach($contents as $content) {
        $pm->name = baseline_latlong_kml_get_item_name($content, $field);
        $pm->description = "&lt;a href='$CFG->wwwroot/mod/baseline/view.php?d=$d&amp;rid=$content->recordid'&gt;Item #$content->recordid&lt;/a&gt; in Moodle baseline activity";
        $pm->long = $content->content1;
        $pm->lat = $content->content;
        echo baseline_latlong_kml_placemark($pm);
    }

    echo '</Document>';

}

echo baseline_latlong_kml_bottom();




function baseline_latlong_kml_top() {
    return '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">

';
}

function baseline_latlong_kml_placemark($pm) {
    return '<Placemark>
  <description>'.$pm->description.'</description>
  <name>'.$pm->name.'</name>
  <LookAt>
    <longitude>'.$pm->long.'</longitude>
    <latitude>'.$pm->lat.'</latitude>
    <range>30500.8880792294568</range>
    <tilt>46.72425699662645</tilt>
    <heading>0.0</heading>
  </LookAt>
  <visibility>0</visibility>
  <Point>
    <extrude>1</extrude>
    <altitudeMode>relativeToGround</altitudeMode>
    <coordinates>'.$pm->long.','.$pm->lat.',50</coordinates>
  </Point>
</Placemark>
';
}

function baseline_latlong_kml_bottom() {
    return '</kml>';
}

function baseline_latlong_kml_get_item_name($content, $field) {
    global $DB;

    // $field->param2 contains the user-specified labelling method

    $name = '';

    if($field->param2 > 0) {
        $name = htmlspecialchars($DB->get_field('baseline_content', 'content', array('fieldid'=>$field->param2, 'recordid'=>$content->recordid)));
    }elseif($field->param2 == -2) {
        $name = $content->content . ', ' . $content->content1;
    }
    if($name=='') { // Done this way so that "item #" is the default that catches any problems
        $name = get_string('entry', 'baseline') . " #$content->recordid";
    }


    return $name;
}
