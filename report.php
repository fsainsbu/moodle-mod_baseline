<?php // $Id: report.php,v 1.9 2007/06/03 16:17:39 skodak Exp $

//  For a given post, shows a report of all the ratings it has

    require_once("../../config.php");
    require_once("lib.php");

    $id   = required_param('id', PARAM_INT);
    $sort = optional_param('sort', '', PARAM_ALPHA);

    if (!$record = $DB->get_record('baseline_records', 'id', $id)) {
        error("Record ID is incorrect");
    }

    if (!$baseline = $DB->get_record('baseline', 'id', $record->baselineid)) {
        error("Data ID is incorrect");
    }

    if (!$course = $DB->get_record('course', 'id', $baseline->course)) {
        error("Course is misconfigured");
    }

    if (!$cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        error("Course Module ID was incorrect");
    }

    require_login($course->id, false, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if (!$baseline->assessed) {
        error("This activity does not use ratings");
    }

    if (!baseline_isowner($record->id) and !has_capability('mod/baseline:viewrating', $context) and !has_capability('mod/baseline:rate', $context)) {
        error("You can not view ratings");
    }

    switch ($sort) {
        case 'firstname': $sqlsort = "u.firstname ASC"; break;
        case 'rating':    $sqlsort = "r.rating ASC"; break;
        default:          $sqlsort = "r.id ASC";
    }

    $scalemenu = make_grades_menu($baseline->scale);

    $strratings = get_string('ratings', 'baseline');
    $strrating  = get_string('rating', 'baseline');
    $strname    = get_string('name');

    print_header($strratings);

    if (!$ratings = baseline_get_ratings($record->id, $sqlsort)) {
        error("No ratings for this record!");

    } else {
        echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"3\" class=\"generalbox\" style=\"width:100%\">";
        echo "<tr>";
        echo "<th class=\"header\" scope=\"col\">&nbsp;</th>";
        echo "<th class=\"header\" scope=\"col\"><a href=\"report.php?id=$record->id&amp;sort=firstname\">$strname</a></th>";
        echo "<th class=\"header\" scope=\"col\" style=\"width:100%\"><a href=\"report.php?id=$id&amp;sort=rating\">$strrating</a></th>";
        echo "</tr>";
        foreach ($ratings as $rating) {
            if (has_capability('mod/baseline:manageentries', $context)) {
                echo '<tr class="forumpostheadertopic">';
            } else {
                echo '<tr class="forumpostheader">';
            }
            echo '<td class="picture">';
            print_user_picture($rating->id, $baseline->course, $rating->picture, false, false, true);
            echo '</td>';
            echo '<td class="author"><a href="'.$CFG->wwwroot.'/user/view.php?id='.$rating->id.'&amp;course='.$baseline->course.'">'.fullname($rating).'</a></td>';
            echo '<td style="white-space:nowrap" align="center" class="rating">'.$scalemenu[$rating->rating].'</td>';
            echo "</tr>\n";
        }
        echo "</table>";
        echo "<br />";
    }

    close_window_button();
     $OUTPUT->footer('none');
?>
