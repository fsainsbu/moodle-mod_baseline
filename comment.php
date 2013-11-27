<?php  // $Id: comment.php,v 1.19.4.1 2009/02/23 07:24:04 dongsheng Exp $

    require_once('../../config.php');
    require_once('lib.php');
    require_once('comment_form.php');

    //param needed to go back to view.php
    $rid  = required_param('rid', PARAM_INT);   // Record ID
    $page = optional_param('page', 0, PARAM_INT);   // Page ID

    //param needed for comment operations
    $mode = optional_param('mode','add',PARAM_ALPHA);
    $commentid = optional_param('commentid','',PARAM_INT);
    $confirm = optional_param('confirm','',PARAM_INT);


    if (! $record = $DB->get_record('baseline_records', 'id', $rid)) {
        error('Record ID is incorrect');
    }
    if (! $baseline = $DB->get_record('baseline', 'id', $record->baselineid)) {
        error('Data ID is incorrect');
    }
    if (! $course = $DB->get_record('course', 'id', $baseline->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    require_login($course->id, false, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/baseline:comment', $context);

    if ($commentid) {
        if (! $comment = $DB->get_record('baseline_comments', 'id', $commentid)) {
            error('Comment ID is misconfigured');
        }
        if ($comment->recordid != $record->id) {
            error('Comment ID is misconfigured');
        }
        if (!has_capability('mod/baseline:managecomments', $context) && $comment->userid != $USER->id) {
            error('Comment is not yours to edit!');
        }
    } else {
        $comment = false;
    }


    $mform = new mod_baseline_comment_form();
    $mform->set_baseline(array('mode'=>$mode, 'page'=>$page, 'rid'=>$record->id, 'commentid'=>$commentid));
    if ($comment) {
        $format = $comment->format;
        $content = $comment->content;
        if (can_use_html_editor()) {
            $options = new object();
            $options->smiley = false;
            $options->filter = false;
            $content = format_text($content, $format, $options);
            $format = FORMAT_HTML;
        }
        $mform->set_baseline(array('content'=>$content, 'format'=>$format));
    }


    if ($mform->is_cancelled()) {
        redirect('view.php?rid='.$record->id.'&amp;page='.$page);
    }

    switch ($mode) {
        case 'add':
            if (!$formabaseline = $mform->get_baseline()) {
                break; // something is wrong here, try again
            }

            $newcomment = new object();
            $newcomment->userid   = $USER->id;
            $newcomment->created  = time();
            $newcomment->modified = time();
            $newcomment->content  = $formabaseline->content;
            $newcomment->recordid = $formabaseline->rid;
            if ($DB->insert_record('baseline_comments',$newcomment)) {
                redirect('view.php?rid='.$record->id.'&amp;page='.$page);
            } else {
                error('Error while saving comment.');
            }

        break;

        case 'edit':    //print edit form
            if (!$formabaseline = $mform->get_baseline()) {
                break; // something is wrong here, try again
            }

            $updatedcomment = new object();
            $updatedcomment->id       = $formabaseline->commentid;
            $updatedcomment->content  = $formabaseline->content;
            $updatedcomment->format   = $formabaseline->format;
            $updatedcomment->modified = time();

            if ($DB->update_record('baseline_comments',$updatedcomment)) {
                redirect('view.php?rid='.$record->id.'&amp;page='.$page);
            } else {
                error('Error while saving comment.');
            }
        break;

        case 'delete':    //deletes single comment from db
            if ($confirm and confirm_sesskey() and $comment) {
                $DB->delete_records('baseline_comments','id',$comment->id);
                redirect('view.php?rid='.$record->id.'&amp;page='.$page, get_string('commentdeleted', 'baseline'));

            } else {    //print confirm delete form
                print_header();
                baseline_print_comment($baseline, $comment, $page);

                notice_yesno(get_string('deletecomment','baseline'),
                  'comment.php?rid='.$record->id.'&amp;commentid='.$comment->id.'&amp;page='.$page.
                              '&amp;sesskey='.sesskey().'&amp;mode=delete&amp;confirm=1',
                  'view.php?rid='.$record->id.'&amp;page='.$page);
                 $OUTPUT->footer();
            }
            die;
        break;

    }

    print_header();
    baseline_print_comments($baseline, $record, $page, $mform);
     $OUTPUT->footer();


?>
