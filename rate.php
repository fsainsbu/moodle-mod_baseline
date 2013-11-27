<?php  // $Id: rate.php,v 1.11.2.1 2009/05/06 10:16:54 stronk7 Exp $
    require_once('../../config.php');
    require_once('lib.php');

    $baselineid = required_param('baselineid', PARAM_INT); // The forum the rated posts are from

    if (!$baseline = $DB->get_record('baseline', 'id', $baselineid)) {
        error("Incorrect baseline id");
    }

    if (!$course = $DB->get_record('course', 'id', $baseline->course)) {
        error("Course ID was incorrect");
    }

    if (!$cm = get_coursemodule_from_instance('baseline', $baseline->id)) {
        error("Course Module ID was incorrect");
    }

    require_login($course, false, $cm);

    if (isguestuser()) {
        error("Guests are not allowed to rate entries.");
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/baseline:rate', $context);

    if (!$baseline->assessed) {
        error("Rating of items not allowed!");
    }

    if (!$frmbaseline = data_submitted() or !confirm_sesskey()) {
        error("This page was not accessed correctly");
    }

/// Calculate scale values
    $scale_values = make_grades_menu($baseline->scale);

    $count = 0;

    foreach ((array)$frmbaseline as $recordid => $rating) {
        if (!is_numeric($recordid)) {
            continue;
        }

        if (!$record = $DB->get_record('baseline_records', 'id', $recordid)) {
            error("Record ID is incorrect");
        }

        if ($baseline->id != $record->baselineid) {
            error("Incorrect record.");
        }

        if ($record->userid == $USER->id) {
            continue;
        }

    /// Check rate is valid for that baselinebase scale values
        if (!array_key_exists($rating, $scale_values) && $rating != -999) {
            print_error('invalidrate', 'baseline', '', $rating);
        }

        // input validation ok

        $count++;

        if ($oldrating = $DB->get_record('baseline_ratings', 'userid', $USER->id, 'recordid', $record->id)) {
            if ($rating == -999) {
                $DB->delete_records('baseline_ratings', 'userid', $oldrating->userid, 'recordid', $oldrating->recordid);
                baseline_update_grades($baseline, $record->userid);

            } else if ($rating != $oldrating->rating) {
                $oldrating->rating = $rating;
                if (! $DB->update_record('baseline_ratings', $oldrating)) {
                    error("Could not update an old rating ($record->id = $rating)");
                }
                baseline_update_grades($baseline, $record->userid);

            }

        } else if ($rating) {
            $newrating = new object();
            $newrating->userid   = $USER->id;
            $newrating->recordid = $record->id;
            $newrating->rating   = $rating;
            if (! $DB->insert_record('baseline_ratings', $newrating)) {
                error("Could not insert a new rating ($record->id = $rating)");
            }
            baseline_update_grades($baseline, $record->userid);
        }
    }

    if ($count == 0) {
        error("Incorrect submitted ratings baseline");
    }

    if (!empty($_SERVER['HTTP_REFERER'])) {
        redirect($_SERVER['HTTP_REFERER'], get_string('ratingssaved', 'baseline'));
    } else {
        // try to guess where to return
        if ($count == 1) {
            redirect('view.php?mode=single&amp;rid='.$record->id, get_string('ratingssaved', 'baseline'));
        } else {
            redirect('view.php?d='.$baseline->id, get_string('ratingssaved', 'baseline'));
        }
    }

?>
