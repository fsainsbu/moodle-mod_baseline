<?php
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

    require_once(dirname(__FILE__) . '/../../config.php');
    require_once($CFG->dirroot . '/mod/baseline/lib.php');
    require_once($CFG->libdir . '/rsslib.php');
    require_once($CFG->libdir . '/completionlib.php');

/// One of these is necessary!
    $id = optional_param('id', 0, PARAM_INT);  // course module id
    $d = optional_param('d', 0, PARAM_INT);   // baseline id
    $rid = optional_param('rid', 0, PARAM_INT);    //record id
    $mode = optional_param('mode', 'single', PARAM_ALPHA);    // Force the browse mode  ('single')
    $filter = optional_param('filter', 0, PARAM_BOOL);
    $base = optional_param('base', 0, PARAM_INT);    //record id
    // search filter will only be applied when $filter is true

    $edit = optional_param('edit', -1, PARAM_BOOL);
    $page = optional_param('page', 0, PARAM_INT);
/// These can be added to perform an action on a record
    $approve = optional_param('approve', 0, PARAM_INT);    //approval recordid
    $delete = optional_param('delete', 0, PARAM_INT);    //delete recordid
    $base = optional_param('base', 0, PARAM_INT);    //record id  FPS
    $user = optional_param('user', '', PARAM_INT);    //User recordid  FPS
    $_SESSION['base']=$base;  
    $CFG->base    = $base;
    $CFG->summary    = False;
    if ($base) {
               $my_base_file = 'baseline_base_content';
               $my_record_table = 'baseline_base_records';
       } else  {
               $my_base_file = 'baseline_content';
               $my_record_table = 'baseline_records';
       }

    if ($id) {
        if (! $cm = get_coursemodule_from_id('baseline', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
            print_error('coursemisconf');
        }
        if (! $baseline = $DB->get_record('baseline', array('id'=>$cm->instance))) {
            print_error('invalidcoursemodule');
        }
        $record = NULL;

    } else if ($rid) {
        if (! $record = $DB->get_record($my_record_table, array('id'=>$rid))) {
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
        $record = NULL;
    }

    require_course_login($course, true, $cm);

    require_once($CFG->dirroot . '/comment/lib.php');
    comment::init();

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/baseline:viewentry', $context);

/// If we have an empty Baselinebase then redirect because this page is useless without baseline
    if (has_capability('mod/baseline:managetemplates', $context)) {
        if (!$DB->record_exists('baseline_fields', array('baselineid'=>$baseline->id))) {      // Brand new baseline!
            redirect($CFG->wwwroot.'/mod/baseline/field.php?d='.$baseline->id);  // Redirect to field entry
        }
    }


/// Check further parameters that set browsing preferences
    if (!isset($SESSION->baselineprefs)) {
        $SESSION->baselineprefs = array();
    }
    if (!isset($SESSION->baselineprefs[$baseline->id])) {
        $SESSION->baselineprefs[$baseline->id] = array();
        $SESSION->baselineprefs[$baseline->id]['search'] = '';
        $SESSION->baselineprefs[$baseline->id]['search_array'] = array();
        $SESSION->baselineprefs[$baseline->id]['sort'] = $baseline->defaultsort;
        $SESSION->baselineprefs[$baseline->id]['advanced'] = 0;
        $SESSION->baselineprefs[$baseline->id]['order'] = ($baseline->defaultsortdir == 0) ? 'ASC' : 'DESC';
    }

    // reset advanced form
    if (!is_null(optional_param('resetadv', null, PARAM_RAW))) {
        $SESSION->baselineprefs[$baseline->id]['search_array'] = array();
        // we need the redirect to cleanup the form state properly
        redirect("view.php?id=$cm->id&amp;mode=$mode&amp;search=&amp;advanced=1");
    }

    $advanced = optional_param('advanced', -1, PARAM_INT);
    if ($advanced == -1) {
        $advanced = $SESSION->baselineprefs[$baseline->id]['advanced'];
    } else {
        if (!$advanced) {
            // explicitly switched to normal mode - discard all advanced search settings
            $SESSION->baselineprefs[$baseline->id]['search_array'] = array();
        }
        $SESSION->baselineprefs[$baseline->id]['advanced'] = $advanced;
    }

    $search_array = $SESSION->baselineprefs[$baseline->id]['search_array'];

    if (!empty($advanced)) {
        $search = '';
        $vals = array();
        $fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id));

        //Added to ammend paging error. This error would occur when attempting to go from one page of advanced
        //search results to another.  All fields were reset in the page transfer, and there was no way of determining
        //whether or not the user reset them.  This would cause a blank search to execute whenever the user attempted
        //to see any page of results past the first.
        //This fix works as follows:
        //$paging flag is set to false when page 0 of the advanced search results is viewed for the first time.
        //Viewing any page of results after page 0 passes the false $paging flag though the URL (see line 523) and the
        //execution falls through to the second condition below, allowing paging to be set to true.
        //Paging remains true and keeps getting passed though the URL until a new search is performed
        //(even if page 0 is revisited).
        //A false $paging flag generates advanced search results based on the fields input by the user.
        //A true $paging flag generates davanced search results from the $SESSION global.

        $paging = optional_param('paging', NULL, PARAM_BOOL);
        if($page == 0 && !isset($paging)) {
            $paging = false;
        }
        else {
            $paging = true;
        }
        if (!empty($fields)) {
            foreach($fields as $field) {
                $searchfield = baseline_get_field_from_id($field->id, $baseline);
                //Get field baseline to build search sql with.  If paging is false, get from user.
                //If paging is true, get baseline from $search_array which is obtained from the $SESSION (see line 116).
                if(!$paging) {
                    $val = $searchfield->parse_search_field();
                } else {
                    //Set value from session if there is a value @ the required index.
                    if (isset($search_array[$field->id])) {
                        $val = $search_array[$field->id]->baseline;
                    } else {             //If there is not an entry @ the required index, set value to blank.
                        $val = '';
                    }
                }
                if (!empty($val)) {
                    $search_array[$field->id] = new stdClass();
                    list($search_array[$field->id]->sql, $search_array[$field->id]->params) = $searchfield->generate_sql('c'.$field->id, $val);
                    $search_array[$field->id]->baseline = $val;
                    $vals[] = $val;
                } else {
                    // clear it out
                    unset($search_array[$field->id]);
                }
            }
        }

        if (!$paging) {
            // name searching
            $fn = optional_param('u_fn', '', PARAM_NOTAGS);
            $ln = optional_param('u_ln', '', PARAM_NOTAGS);
        } else {
            $fn = isset($search_array[BASELINE_FIRSTNAME]) ? $search_array[BASELINE_FIRSTNAME]->baseline : '';
            $ln = isset($search_array[BASELINE_LASTNAME]) ? $search_array[BASELINE_LASTNAME]->baseline : '';
        }
        if (!empty($fn)) {
            $search_array[BASELINE_FIRSTNAME] = new stdClass();
            $search_array[BASELINE_FIRSTNAME]->sql    = '';
            $search_array[BASELINE_FIRSTNAME]->params = array();
            $search_array[BASELINE_FIRSTNAME]->field  = 'u.firstname';
            $search_array[BASELINE_FIRSTNAME]->baseline   = $fn;
            $vals[] = $fn;
        } else {
            unset($search_array[BASELINE_FIRSTNAME]);
        }
        if (!empty($ln)) {
            $search_array[BASELINE_LASTNAME] = new stdClass();
            $search_array[BASELINE_LASTNAME]->sql     = '';
            $search_array[BASELINE_LASTNAME]->params = array();
            $search_array[BASELINE_LASTNAME]->field   = 'u.lastname';
            $search_array[BASELINE_LASTNAME]->baseline    = $ln;
            $vals[] = $ln;
        } else {
            unset($search_array[BASELINE_LASTNAME]);
        }

        $SESSION->baselineprefs[$baseline->id]['search_array'] = $search_array;     // Make it sticky

        // in case we want to switch to simple search later - there might be multiple values there ;-)
        if ($vals) {
            $val = reset($vals);
            if (is_string($val)) {
                $search = $val;
            }
        }

    } else {
        $search = optional_param('search', $SESSION->baselineprefs[$baseline->id]['search'], PARAM_NOTAGS);
        //Paging variable not used for standard search. Set it to null.
        $paging = NULL;
    }

    // Disable search filters if $filter is not true:
    if (! $filter) {
        $search = '';
    }

    $textlib = textlib_get_instance();
    if ($textlib->strlen($search) < 2) {
        $search = '';
    }
    $SESSION->baselineprefs[$baseline->id]['search'] = $search;   // Make it sticky

    $sort = optional_param('sort', $SESSION->baselineprefs[$baseline->id]['sort'], PARAM_INT);
    $SESSION->baselineprefs[$baseline->id]['sort'] = $sort;       // Make it sticky

    $order = (optional_param('order', $SESSION->baselineprefs[$baseline->id]['order'], PARAM_ALPHA) == 'ASC') ? 'ASC': 'DESC';
    $SESSION->baselineprefs[$baseline->id]['order'] = $order;     // Make it sticky


    $oldperpage = get_user_preferences('baseline_perpage_'.$baseline->id, 10);
    $perpage = optional_param('perpage', $oldperpage, PARAM_INT);

    if ($perpage < 2) {
        $perpage = 2;
    }
    if ($perpage != $oldperpage) {
        set_user_preference('baseline_perpage_'.$baseline->id, $perpage);
    }

    add_to_log($course->id, 'baseline', 'view', "view.php?id=$cm->id", $baseline->id, $cm->id);


    $urlparams = array('d' => $baseline->id);
    if ($record) {
        $urlparams['rid'] = $record->id;
    }
    if ($page) {
        $urlparams['page'] = $page;
    }
    if ($mode) {
        $urlparams['mode'] = $mode;
    }
    if ($filter) {
        $urlparams['filter'] = $filter;
    }
// Initialize $PAGE, compute blocks
    $PAGE->set_url('/mod/baseline/view.php', $urlparams);

    if (($edit != -1) and $PAGE->user_allowed_editing()) {
        $USER->editing = $edit;
    }

    $courseshortname = format_string($course->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));

/// RSS and CSS and JS meta
    $meta = '';
    if (!empty($CFG->enablerssfeeds) && !empty($CFG->baseline_enablerssfeeds) && $baseline->rssarticles > 0) {
        $rsstitle = $courseshortname . ': %fullname%';
        rss_add_http_header($context, 'mod_baseline', $baseline, $rsstitle);
    }
    if ($baseline->csstemplate) {
        $PAGE->requires->css('/mod/baseline/css.php?d='.$baseline->id);
    }
    if ($baseline->jstemplate) {
        $PAGE->requires->js('/mod/baseline/js.php?d='.$baseline->id, true);
    }

    // Mark as viewed
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

/// Print the page header
    // Note: MDL-19010 there will be further changes to printing header and blocks.
    // The code will be much nicer than this eventually.
    $title = $courseshortname.': ' . format_string($baseline->name);

    if ($PAGE->user_allowed_editing()) {
        $buttons = '<table><tr><td><form method="get" action="view.php"><div>'.
            '<input type="hidden" name="id" value="'.$cm->id.'" />'.
            '<input type="hidden" name="edit" value="'.($PAGE->user_is_editing()?'off':'on').'" />'.
            '<input type="submit" value="'.get_string($PAGE->user_is_editing()?'blockseditoff':'blocksediton').'" /></div></form></td></tr></table>';
        $PAGE->set_button($buttons);
    }

    if ($mode == 'asearch') {
        $PAGE->navbar->add(get_string('search'));
    }

    $PAGE->set_title($title);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();

/// Check to see if groups are being used here
    $returnurl = $CFG->wwwroot . '/mod/baseline/view.php?d='.$baseline->id.'&amp;search='.s($search).'&amp;sort='.s($sort).'&amp;order='.s($order).'&amp;';
    groups_print_activity_menu($cm, $returnurl);
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

    // detect entries not approved yet and show hint instead of not found error
    if ($record and $baseline->approval and !$record->approved and $record->userid != $USER->id and !has_capability('mod/baseline:manageentries', $context)) {
        if (!$currentgroup or $record->groupid == $currentgroup or $record->groupid == 0) {
            print_error('notapproved', 'baseline');
        }
    }

    echo $OUTPUT->heading(format_string($baseline->name));

    // Do we need to show a link to the RSS feed for the records?
    //this links has been Settings (baseline activity administration) block
    /*if (!empty($CFG->enablerssfeeds) && !empty($CFG->baseline_enablerssfeeds) && $baseline->rssarticles > 0) {
        echo '<div style="float:right;">';
        rss_print_link($context->id, $USER->id, 'mod_baseline', $baseline->id, get_string('rsstype'));
        echo '</div>';
        echo '<div style="clear:both;"></div>';
    }*/

    if ($baseline->intro and empty($page) and empty($record) and $mode != 'single') {
        $options = new stdClass();
        $options->noclean = true;
        echo $OUTPUT->box(format_module_intro('baseline', $baseline, $cm->id), 'generalbox', 'intro');
    }

/// Delete any requested records

    if ($delete && confirm_sesskey() && (has_capability('mod/baseline:manageentries', $context) or baseline_isowner($delete))) {
        if ($confirm = optional_param('confirm',0,PARAM_INT)) {
            if ($deleterecord = $DB->get_record($my_record_table, array('id'=>$delete))) {   // Need to check this is valid
                if ($deleterecord->baselineid == $baseline->id) {                       // Must be from this baseline
                    if ($contents = $DB->get_records($my_base_file, array('recordid'=>$deleterecord->id))) {
                        foreach ($contents as $content) {  // Delete files or whatever else this field allows
                            if ($field = baseline_get_field_from_id($content->fieldid, $baseline)) { // Might not be there
                                $field->delete_content($content->recordid);
                            }
                        }
                    }
                    $DB->delete_records($my_base_file, array('recordid'=>$deleterecord->id));
                    $DB->delete_records($my_record_table, array('id'=>$deleterecord->id));

                    add_to_log($course->id, 'baseline', 'record delete', "view.php?id=$cm->id", $baseline->id, $cm->id);

                    echo $OUTPUT->notification(get_string('recorddeleted','baseline'), 'notifysuccess');
                }
            }

        } else {   // Print a confirmation page
            if ($deleterecord = $DB->get_record($my_record_table, array('id'=>$delete))) {   // Need to check this is valid
                if ($deleterecord->baselineid == $baseline->id) {                       // Must be from this baseline
                    $deletebutton = new single_button(new moodle_url('/mod/baseline/view.php?d='.$baseline->id.'&delete='.$delete.'&confirm=1'), get_string('delete'), 'post');
                    echo $OUTPUT->confirm(get_string('confirmdeleterecord','baseline'),
                            $deletebutton, 'view.php?d='.$baseline->id);

                    $records[] = $deleterecord;
                    echo baseline_print_template('singletemplate', $records, $baseline, '', 0, true);

                    echo $OUTPUT->footer();
                    exit;
                }
            }
        }
    }


//if baseline activity closed dont let students in
$showactivity = true;
if (!has_capability('mod/baseline:manageentries', $context)) {
    $timenow = time();
    if (!empty($baseline->timeavailablefrom) && $baseline->timeavailablefrom > $timenow) {
        echo $OUTPUT->notification(get_string('notopenyet', 'baseline', userdate($baseline->timeavailablefrom)));
        $showactivity = false;
    } else if (!empty($baseline->timeavailableto) && $timenow > $baseline->timeavailableto) {
        echo $OUTPUT->notification(get_string('expired', 'baseline', userdate($baseline->timeavailableto)));
        $showactivity = false;
    }
}

if ($showactivity) {
    // Print the tabs
    if ($record or $mode == 'single') {
        $currenttab = 'single';
    } elseif($mode == 'asearch') {
        $currenttab = 'asearch';
    }
    else {
   if ($base) {
    $currenttab = 'list';
   } else {
    $currenttab = 'dlist';
    }
    }

    include('tabs.php');

    if ($mode == 'asearch') {
        $maxcount = 0;

    } else {
    /// Approve any requested records
        $params = array(); // named params array

        $approvecap = has_capability('mod/baseline:approve', $context);

        if ($approve && confirm_sesskey() && $approvecap) {
            if ($approverecord = $DB->get_record($my_record_table, array('id'=>$approve))) {   // Need to check this is valid
                if ($approverecord->baselineid == $baseline->id) {                       // Must be from this baseline
                    $newrecord = new stdClass();
                    $newrecord->id = $approverecord->id;
                    $newrecord->approved = 1;
                    $DB->update_record($my_record_table, $newrecord);
                    echo $OUTPUT->notification(get_string('recordapproved','baseline'), 'notifysuccess');
                }
            }
        }

         $numentries = baseline_numentries($baseline);
    /// Check the number of entries required against the number of entries already made (doesn't apply to teachers)
        if ($baseline->requiredentries > 0 && $numentries < $baseline->requiredentries && !has_capability('mod/baseline:manageentries', $context)) {
            $baseline->entriesleft = $baseline->requiredentries - $numentries;
            $strentrieslefttoadd = get_string('entrieslefttoadd', 'baseline', $baseline);
            echo $OUTPUT->notification($strentrieslefttoadd);
        }

    /// Check the number of entries required before to view other participant's entries against the number of entries already made (doesn't apply to teachers)
        $requiredentries_allowed = true;
        if ($baseline->requiredentriestoview > 0 && $numentries < $baseline->requiredentriestoview && !has_capability('mod/baseline:manageentries', $context)) {
            $baseline->entrieslefttoview = $baseline->requiredentriestoview - $numentries;
            $strentrieslefttoaddtoview = get_string('entrieslefttoaddtoview', 'baseline', $baseline);
            echo $OUTPUT->notification($strentrieslefttoaddtoview);
            $requiredentries_allowed = false;
        }

    /// setup group and approve restrictions
        if (!$approvecap && $baseline->approval) {
            if (isloggedin()) {
                $approveselect = ' AND (r.approved=1 OR r.userid=:myid1) ';
                $params['myid1'] = $USER->id;
            } else {
                $approveselect = ' AND r.approved=1 ';
            }
        } else {
            $approveselect = ' ';
        }

        if ($currentgroup) {
            $groupselect = " AND (r.groupid = :currentgroup OR r.groupid = 0)";
            $params['currentgroup'] = $currentgroup;
        } else {
            $groupselect = ' ';
        }

        // Init some variables to be used by advanced search
        $advsearchselect = '';
        $advwhere        = '';
        $advtables       = '';
        $advparams       = array();

    /// Find the field we are sorting on
        if ($sort <= 0 or !$sortfield = baseline_get_field_from_id($sort, $baseline)) {

            switch ($sort) {
                case BASELINE_LASTNAME:
                    $ordering = "u.lastname $order, u.firstname $order";
                    break;
                case BASELINE_FIRSTNAME:
                    $ordering = "u.firstname $order, u.lastname $order";
                    break;
                case BASELINE_APPROVED:
                    $ordering = "r.approved $order, r.timecreated $order";
                    break;
                case BASELINE_TIMEMODIFIED:
                    $ordering = "r.timemodified $order";
                    break;
                case BASELINE_TIMEADDED:
                default:
                    $sort     = 0;
                    $ordering = "r.timecreated $order";
            }

            $what = ' DISTINCT r.id, r.approved, r.timecreated, r.timemodified, r.userid, u.firstname, u.lastname';
            $count = ' COUNT(DISTINCT c.recordid) ';
	     if ($base) {
            $tables = '{baseline_base_content} c,{baseline_base_records} r, {baseline_base_content} cs, {user} u ';
            } else {
            $tables = '{baseline_content} c,{baseline_records} r, {baseline_content} cs, {user} u ';
            }
            $where =  'WHERE c.recordid = r.id
                         AND r.baselineid = :baselineid
                         AND r.userid = u.id
                         AND cs.recordid = r.id ';
            $params['baselineid'] = $baseline->id;
            $sortorder = ' ORDER BY '.$ordering.', r.id ASC ';
            $searchselect = '';

            // If requiredentries is not reached, only show current user's entries
    // FPS hide other users  data if not assigned. 
//  ie in mentor to user table or is user if (!$requiredentries_allowed) {
            //if (!$requiredentries_allowed) {
                $where .= ' AND u.id = :myid2 ';
                $params['myid2'] = $USER->id;
            //}

            if (!empty($advanced)) {                                                  //If advanced box is checked.
                $i = 0;
                foreach($search_array as $key => $val) {                              //what does $search_array hold?
                    if ($key == BASELINE_FIRSTNAME or $key == BASELINE_LASTNAME) {
                        $i++;
                        $searchselect .= " AND ".$DB->sql_like($val->field, ":search_flname_$i", false);
                        $params['search_flname_'.$i] = "%$val->baseline%";
                        continue;
                    }
                    $advtables .= ', {baseline_content} c'.$key.' ';
                    $advwhere .= ' AND c'.$key.'.recordid = r.id';
                    $advsearchselect .= ' AND ('.$val->sql.') ';
                    $advparams = array_merge($advparams, $val->params);
                }
            } else if ($search) {
                $searchselect = " AND (".$DB->sql_like('cs.content', ':search1', false)." OR ".$DB->sql_like('u.firstname', ':search2', false)." OR ".$DB->sql_like('u.lastname', ':search3', false)." ) ";
                $params['search1'] = "%$search%";
                $params['search2'] = "%$search%";
                $params['search3'] = "%$search%";
            } else {
                $searchselect = ' ';
            }

        } else {

            $sortcontent = $DB->sql_compare_text('c.' . $sortfield->get_sort_field());
            $sortcontentfull = $sortfield->get_sort_sql($sortcontent);

            $what = ' DISTINCT r.id, r.approved, r.timecreated, r.timemodified, r.userid, u.firstname, u.lastname, ' . $sortcontentfull . ' AS _order ';
            $count = ' COUNT(DISTINCT c.recordid) ';
	    if ($base) {
            $tables = '{baseline_base_content} c,{baseline_base_records} r, {baseline_base_content} cs, {user} u ';
            } else {
            $tables = '{baseline_content} c,{baseline_records} r, {baseline_content} cs, {user} u ';
            }
            $where =  'WHERE c.recordid = r.id
                         AND c.fieldid = :sort
                         AND r.baselineid = :baselineid
                         AND r.userid = u.id
                         AND cs.recordid = r.id ';
            $params['baselineid'] = $baseline->id;
            $params['sort'] = $sort;
            $sortorder = ' ORDER BY _order '.$order.' , r.id ASC ';
            $searchselect = '';

            // If requiredentries is not reached, only show current user's entries
            // FPS if (!$requiredentries_allowed) {
                $where .= ' AND u.id = ' . $USER->id;
                $params['myid2'] = $USER->id;
            // FPS }

            if (!empty($advanced)) {                                                  //If advanced box is checked.
                foreach($search_array as $key => $val) {                              //what does $search_array hold?
                    if ($key == BASELINE_FIRSTNAME or $key == BASELINE_LASTNAME) {
                        $i++;
                        $searchselect .= " AND ".$DB->sql_like($val->field, ":search_flname_$i", false);
                        $params['search_flname_'.$i] = "%$val->baseline%";
                        continue;
                    }
                    $advtables .= ', {baseline_content} c'.$key.' ';
                    $advwhere .= ' AND c'.$key.'.recordid = r.id AND c'.$key.'.fieldid = '.$key;
                    $advsearchselect .= ' AND ('.$val->sql.') ';
                    $advparams = array_merge($advparams, $val->params);
                }
            } else if ($search) {
                $searchselect = " AND (".$DB->sql_like('cs.content', ':search1', false)." OR ".$DB->sql_like('u.firstname', ':search2', false)." OR ".$DB->sql_like('u.lastname', ':search3', false)." ) ";
                $params['search1'] = "%$search%";
                $params['search2'] = "%$search%";
                $params['search3'] = "%$search%";
            } else {
                $searchselect = ' ';
            }
        }

    /// To actually fetch the records

        $fromsql    = "FROM $tables $advtables $where $advwhere $groupselect $approveselect $searchselect $advsearchselect";
        $sqlselect  = "SELECT $what $fromsql $sortorder";
        $sqlcount   = "SELECT $count $fromsql";   // Total number of records when searching
        $sqlmax     = "SELECT $count FROM $tables $where $groupselect $approveselect"; // number of all recoirds user may see
        $allparams  = array_merge($params, $advparams);

    /// Work out the paging numbers and counts

        $totalcount = $DB->count_records_sql($sqlcount, $allparams);
        if (empty($searchselect) && empty($advsearchselect)) {
            $maxcount = $totalcount;
        } else {
            $maxcount = $DB->count_records_sql($sqlmax, $params);
        }

        if ($record) {     // We need to just show one, so where is it in context?
            $nowperpage = 1;
            $mode = 'single';

            $page = 0;
            // TODO: Improve this because we are executing $sqlselect twice (here and some lines below)!
            if ($allrecordids = $DB->get_fieldset_sql($sqlselect, $allparams)) {
                $page = (int)array_search($record->id, $allrecordids);
                unset($allrecordids);
            }

        } else if ($mode == 'single') {  // We rely on ambient $page settings
            $nowperpage = 1;

        } else {
            $nowperpage = $perpage;
        }

    /// Get the actual records

        if (!$records = $DB->get_records_sql($sqlselect, $allparams, $page * $nowperpage, $nowperpage)) {
            // Nothing to show!
            if ($record) {         // Something was requested so try to show that at least (bug 5132)
                if (has_capability('mod/baseline:manageentries', $context) || empty($baseline->approval) ||
                         $record->approved || (isloggedin() && $record->userid == $USER->id)) {
                    if (!$currentgroup || $record->groupid == $currentgroup || $record->groupid == 0) {
                        // OK, we can show this one
                        $records = array($record->id => $record);
                        $totalcount = 1;
                    }
                }
            }
        }

        if (empty($records)) {
            if ($maxcount){
                $a = new stdClass();
                $a->max = $maxcount;
                $a->reseturl = "view.php?id=$cm->id&amp;mode=$mode&amp;search=&amp;advanced=0";
                echo $OUTPUT->notification(get_string('foundnorecords','baseline', $a));
            } else {
                echo $OUTPUT->notification(get_string('norecords','baseline'));
            }

        } else { //  We have some records to print

            if ($maxcount != $totalcount) {
                $a = new stdClass();
                $a->num = $totalcount;
                $a->max = $maxcount;
                $a->reseturl = "view.php?id=$cm->id&amp;mode=$mode&amp;search=&amp;advanced=0";
                echo $OUTPUT->notification(get_string('foundrecords', 'baseline', $a), 'notifysuccess');
            }

            if ($mode == 'single') { // Single template
                $baseurl = 'view.php?d=' . $baseline->id . '&mode=single&';
                if (!empty($search)) {
                    $baseurl .= 'filter=1&';
                }
                if (!empty($page)) {
                    $baseurl .= 'page=' . $page;
                }
                echo $OUTPUT->paging_bar($totalcount, $page, $nowperpage, $baseurl);

                if (empty($baseline->singletemplate)){
                    echo $OUTPUT->notification(get_string('nosingletemplate','baseline'));
                    baseline_generate_default_template($baseline, 'singletemplate', 0, false, false);
                }

                //baseline_print_template() only adds ratings for singletemplate which is why we're attaching them here
                //attach ratings to baseline records
                require_once($CFG->dirroot.'/rating/lib.php');
                if ($baseline->assessed != RATING_AGGREGATE_NONE) {
                    $ratingoptions = new stdClass;
                    $ratingoptions->context = $context;
                    $ratingoptions->component = 'mod_baseline';
                    $ratingoptions->ratingarea = 'entry';
                    $ratingoptions->items = $records;
                    $ratingoptions->aggregate = $baseline->assessed;//the aggregation method
                    $ratingoptions->scaleid = $baseline->scale;
                    $ratingoptions->userid = $USER->id;
                    $ratingoptions->returnurl = $CFG->wwwroot.'/mod/baseline/'.$baseurl;
                    $ratingoptions->assesstimestart = $baseline->assesstimestart;
                    $ratingoptions->assesstimefinish = $baseline->assesstimefinish;

                    $rm = new rating_manager();
                    $records = $rm->get_ratings($ratingoptions);
                }

                baseline_print_template('singletemplate', $records, $baseline, $search, $page);

                echo $OUTPUT->paging_bar($totalcount, $page, $nowperpage, $baseurl);

            } else {                                  // List template
                $baseurl = 'view.php?d='.$baseline->id.'&amp;';
                //send the advanced flag through the URL so it is remembered while paging.
                $baseurl .= 'advanced='.$advanced.'&amp;';
                if (!empty($search)) {
                    $baseurl .= 'filter=1&amp;';
                }
                //pass variable to allow determining whether or not we are paging through results.
                $baseurl .= 'paging='.$paging.'&amp;';

                echo $OUTPUT->paging_bar($totalcount, $page, $nowperpage, $baseurl);

                if (empty($baseline->listtemplate)){
                    echo $OUTPUT->notification(get_string('nolisttemplate','baseline'));
                    baseline_generate_default_template($baseline, 'listtemplate', 0, false, false);
                }
                echo $baseline->listtemplateheader;
                baseline_print_template('listtemplate', $records, $baseline, $search, $page);
                echo $baseline->listtemplatefooter;

                echo $OUTPUT->paging_bar($totalcount, $page, $nowperpage, $baseurl);
            }

        }
    }

    $search = trim($search);
    if (empty($records)) {
        $records = array();
    }

    if ($mode == '' && !empty($CFG->enableportfolios)) {
        require_once($CFG->libdir . '/portfoliolib.php');
        $button = new portfolio_add_button();
        $button->set_callback_options('baseline_portfolio_caller', array('id' => $cm->id), '/mod/baseline/locallib.php');
        if (baseline_portfolio_caller::has_files($baseline)) {
            $button->set_formats(array(PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_LEAP2A)); // no plain html for us
        }
        echo $button->to_html(PORTFOLIO_ADD_FULL_FORM);
    }

    //Advanced search form doesn't make sense for single (redirects list view)
    if (($maxcount || $mode == 'asearch') && $mode != 'single') {
        baseline_print_preference_form($baseline, $perpage, $search, $sort, $order, $search_array, $advanced, $mode);
    }
}

echo $OUTPUT->footer();
