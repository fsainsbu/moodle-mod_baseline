<?php //Id:$

//This php script contains all the stuff to backup/restore baseline mod

    //This is the "graphical" structure of the baseline mod:
    //
    //                     baseline
    //                    (CL,pk->id)
    //                        |
    //                        |
    //                        |
    //      ---------------------------------------------------------------------------------
    //      |                                                                               |
    //baseline_records (UL,pk->id, fk->baseline)                                      baseline_fields (pk->id, fk->baseline)
    //               |                                                                      |
    //               |                                                                      |
    //     -----------------------------------------------------------------------------    |
    //     |                                  |                                        |    |
    //baseline_ratings(fk->recordid, pk->id) baseline_comments (fk->recordid, pk->id)          |    |
    //                                                                  baseline_content(pk->id, fk->recordid, fk->fieldid)
    //
    //
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive baseline)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //Backup baseline files because we've selected to backup user info
    //and files are user info's level


    //Return a content encoded to support interactivities linking. Every module

function baseline_backup_mods($bf,$preferences) {
    global $DB,$CFG;

    $status = true;

    // iterate
    if ($baselines = $DB->get_records('baseline','course',$preferences->backup_course,"id")) {
        foreach ($baselines as $baseline) {
           if (function_exists('backup_mod_selected')) {
                    // Moodle 1.6
                    $backup_mod_selected = backup_mod_selected($preferences, 'baseline', $baseline->id);
            } else {
                    // Moodle 1.5
                $backup_mod_selected = true;
            }
            if ($backup_mod_selected) {
                $status = baseline_backup_one_mod($bf,$preferences,$baseline);
                // backup files happens in backup_one_mod now too.
            }
        }
    }
    return $status;
}

function baseline_backup_one_mod($bf,$preferences,$baseline) {
    global $DB,$CFG;

    if (is_numeric($baseline)) { // backwards compatibility
        $baseline = $DB->get_record('baseline','id',$baseline);
    }
    $instanceid = $baseline->id;

    $status = true;


    fwrite ($bf,start_tag("MOD",3,true));
    //Print baseline baseline
    fwrite ($bf,full_tag("ID",4,false,$baseline->id));
    fwrite ($bf,full_tag("MODTYPE",4,false,"baseline"));
    fwrite ($bf,full_tag("NAME",4,false,$baseline->name));
    fwrite ($bf,full_tag("INTRO",4,false,$baseline->intro));
    fwrite ($bf,full_tag("COMMENTS",4,false,$baseline->comments));
    fwrite ($bf,full_tag("TIMEAVAILABLEFROM",4,false,$baseline->timeavailablefrom));
    fwrite ($bf,full_tag("TIMEAVAILABLETO",4,false,$baseline->timeavailableto));
    fwrite ($bf,full_tag("TIMEVIEWFROM",4,false,$baseline->timeviewfrom));
    fwrite ($bf,full_tag("TIMEVIEWTO",4,false,$baseline->timeviewto));
    fwrite ($bf,full_tag("REQUIREDENTRIES",4,false,$baseline->requiredentries));
    fwrite ($bf,full_tag("REQUIREDENTRIESTOVIEW",4,false,$baseline->requiredentriestoview));
    fwrite ($bf,full_tag("MAXENTRIES",4,false,$baseline->maxentries));
    fwrite ($bf,full_tag("RSSARTICLES",4,false,$baseline->rssarticles));
    fwrite ($bf,full_tag("SINGLETEMPLATE",4,false,$baseline->singletemplate));
    fwrite ($bf,full_tag("LISTTEMPLATE",4,false,$baseline->listtemplate));
    fwrite ($bf,full_tag("LISTTEMPLATEHEADER",4,false,$baseline->listtemplateheader));
    fwrite ($bf,full_tag("LISTTEMPLATEFOOTER",4,false,$baseline->listtemplatefooter));
    fwrite ($bf,full_tag("ADDTEMPLATE",4,false,$baseline->addtemplate));
    fwrite ($bf,full_tag("RSSTEMPLATE",4,false,$baseline->rsstemplate));
    fwrite ($bf,full_tag("RSSTITLETEMPLATE",4,false,$baseline->rsstitletemplate));
    fwrite ($bf,full_tag("CSSTEMPLATE",4,false,$baseline->csstemplate));
    fwrite ($bf,full_tag("JSTEMPLATE",4,false,$baseline->jstemplate));
    fwrite ($bf,full_tag("APPROVAL",4,false,$baseline->approval));
    fwrite ($bf,full_tag("SCALE",4,false,$baseline->scale));
    fwrite ($bf,full_tag("ASSESSED",4,false,$baseline->assessed));
    fwrite ($bf,full_tag("DEFAULTSORT",4,false,$baseline->defaultsort));
    fwrite ($bf,full_tag("DEFAULTSORTDIR",4,false,$baseline->defaultsortdir));
    fwrite ($bf,full_tag("EDITANY",4,false,$baseline->editany));
    fwrite ($bf,full_tag("NOTIFICATION",4,false,$baseline->notification));

    // if we've selected to backup users info, then call any other functions we need
    // including backing up individual files

    $status = backup_baseline_fields($bf,$preferences,$baseline->id);

    if (backup_userbaseline_selected($preferences,'baseline',$baseline->id)) {
        //$status = backup_someuserbaseline_for_this_instance();
        //$status = backup_somefiles_for_this_instance();
        // ... etc

        $status = backup_baseline_records($bf,$preferences,$baseline->id);
        if ($status) {
            $status = backup_baseline_files_instance($bf,$preferences,$baseline->id);    //recursive copy
        }
    }
    fwrite ($bf,end_tag("MOD",3,true));
    return $status;

}


function backup_baseline_fields($bf,$preferences,$baselineid){
    global $DB,$CFG;
    $status = true;

    $baseline_fields = $DB->get_records("baseline_fields","baselineid",$baselineid);

        //If there is submissions
        if ($baseline_fields) {
            //Write start tag
            $status =fwrite ($bf,start_tag("FIELDS",4,true));
            //Iterate over each submission
            foreach ($baseline_fields as $fie_sub) {
                //Start submission
                $status =fwrite ($bf,start_tag("FIELD",5,true));
                //Print submission contents
                fwrite ($bf,full_tag("ID",6,false,$fie_sub->id));
                fwrite ($bf,full_tag("BASELINEID",6,false,$fie_sub->baselineid));
                fwrite ($bf,full_tag("TYPE",6,false,$fie_sub->type));
                fwrite ($bf,full_tag("NAME",6,false,$fie_sub->name));
                fwrite ($bf,full_tag("DESCRIPTION",6,false,$fie_sub->description));
                fwrite ($bf,full_tag("PARAM1",6,false,$fie_sub->param1));
                fwrite ($bf,full_tag("PARAM2",6,false,$fie_sub->param2));
                fwrite ($bf,full_tag("PARAM3",6,false,$fie_sub->param3));
                fwrite ($bf,full_tag("PARAM4",6,false,$fie_sub->param4));
                fwrite ($bf,full_tag("PARAM5",6,false,$fie_sub->param5));
                fwrite ($bf,full_tag("PARAM6",6,false,$fie_sub->param6));
                fwrite ($bf,full_tag("PARAM7",6,false,$fie_sub->param7));
                fwrite ($bf,full_tag("PARAM8",6,false,$fie_sub->param8));
                fwrite ($bf,full_tag("PARAM9",6,false,$fie_sub->param9));
                fwrite ($bf,full_tag("PARAM10",6,false,$fie_sub->param10));

                //End submission
                $status =fwrite ($bf,end_tag("FIELD",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("FIELDS",4,true));
        }
        return $status;
}

function backup_baseline_content($bf,$preferences,$recordid){
    global $DB,$CFG;
    $status = true;

    $baseline_contents = $DB->get_records("baseline_content","recordid",$recordid);

        //If there is submissions
        if ($baseline_contents) {
            //Write start tag
            $status =fwrite ($bf,start_tag("CONTENTS",6,true));
            //Iterate over each submission
            foreach ($baseline_contents as $cnt_sub) {
                //Start submission
                $status =fwrite ($bf,start_tag("CONTENT",7,true));
                //Print submission contents
                fwrite ($bf,full_tag("ID",8,false,$cnt_sub->id));
                fwrite ($bf,full_tag("RECORDID",8,false,$cnt_sub->recordid));
                fwrite ($bf,full_tag("FIELDID",8,false,$cnt_sub->fieldid));
                fwrite ($bf,full_tag("CONTENT",8,false,$cnt_sub->content));
                fwrite ($bf,full_tag("CONTENT1",8,false,$cnt_sub->content1));
                fwrite ($bf,full_tag("CONTENT2",8,false,$cnt_sub->content2));
                fwrite ($bf,full_tag("CONTENT3",8,false,$cnt_sub->content3));
                fwrite ($bf,full_tag("CONTENT4",8,false,$cnt_sub->content4));
                //End submission
                $status =fwrite ($bf,end_tag("CONTENT",7,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("CONTENTS",6,true));
        }
        return $status;

}
function backup_baseline_ratings($bf,$preferences,$recordid){
    global $DB,$CFG;
    $status = true;
    $baseline_ratings = $DB->get_records("baseline_ratings","recordid",$recordid);

    //If there is submissions
    if ($baseline_ratings) {
        //Write start tag
        $status =fwrite ($bf,start_tag("RATINGS",6,true));
        //Iterate over each submission
        foreach ($baseline_ratings as $rat_sub) {
            //Start submission
            $status =fwrite ($bf,start_tag("RATING",7,true));
            //Print submission contents
            fwrite ($bf,full_tag("ID",8,false,$rat_sub->id));
            fwrite ($bf,full_tag("RECORDID",8,false,$rat_sub->recordid));
            fwrite ($bf,full_tag("USERID",8,false,$rat_sub->userid));
            fwrite ($bf,full_tag("RATING",8,false,$rat_sub->rating));
            //End submission
            $status =fwrite ($bf,end_tag("RATING",7,true));
        }
            //Write end tag
        $status =fwrite ($bf,end_tag("RATINGS",6,true));

    }

    return $status;
}
function backup_baseline_comments($bf,$preferences,$recordid){
    global $DB,$CFG;
    $status = true;
    $baseline_comments = $DB->get_records("baseline_comments","recordid",$recordid);

    //If there is submissions
    if ($baseline_comments) {
        //Write start tag
        $status =fwrite ($bf,start_tag("COMMENTS",6,true));
            //Iterate over each submission
        foreach ($baseline_comments as $com_sub) {
            //Start submission
            $status =fwrite ($bf,start_tag("COMMENT",7,true));
            //Print submission contents
            fwrite ($bf,full_tag("ID",8,false,$com_sub->id));
            fwrite ($bf,full_tag("RECORDID",8,false,$com_sub->recordid));
            fwrite ($bf,full_tag("USERID",8,false,$com_sub->userid));
            fwrite ($bf,full_tag("CONTENT",8,false,$com_sub->content));
            fwrite ($bf,full_tag("CREATED",8,false,$com_sub->created));
            fwrite ($bf,full_tag("MODIFIED",8,false,$com_sub->modified));
            //End submission
            $status =fwrite ($bf,end_tag("COMMENT",7,true));
        }
        //Write end tag
        $status =fwrite ($bf,end_tag("COMMENTS",6,true));
    }
    return $status;
}


function backup_baseline_files_instance($bf,$preferences,$instanceid) {

    global $DB,$CFG;
    $status = true;

        //First we check to modbaseline exists and create it as necessary
        //in temp/backup/$backup_code  dir
    $status = check_and_create_modbaseline_dir($preferences->backup_unique_code);
    $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/modbaseline/baseline/",true);
        //Now copy the baseline dir
    if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
        if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline/".$instanceid)) {
            $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/modbaseline/baseline/".$instanceid);
        }
    }
    return $status;
}

function backup_baseline_records($bf,$preferences,$baselineid){

    global $DB,$CFG;
    $status = true;

    $baseline_records = $DB->get_records("baseline_records","baselineid",$baselineid);
        //If there is submissions
        if ($baseline_records) {
            //Write start tag
            $status =fwrite ($bf,start_tag("RECORDS",4,true));
            //Iterate over each submission
            foreach ($baseline_records as $rec_sub) {
                //Start submission
                $status =fwrite ($bf,start_tag("RECORD",5,true));
                //Print submission contents
                fwrite ($bf,full_tag("ID",6,false,$rec_sub->id));
                fwrite ($bf,full_tag("USERID",6,false,$rec_sub->userid));
                fwrite ($bf,full_tag("GROUPID",6,false,$rec_sub->groupid));
                fwrite ($bf,full_tag("BASELINEID",6,false,$rec_sub->baselineid));
                fwrite ($bf,full_tag("TIMECREATED",6,false,$rec_sub->timecreated));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$rec_sub->timemodified));
                fwrite ($bf,full_tag("APPROVED",6,false,$rec_sub->approved));
                //End submission

                backup_baseline_content($bf,$preferences,$rec_sub->id);
                backup_baseline_ratings($bf,$preferences,$rec_sub->id);
                backup_baseline_comments($bf,$preferences,$rec_sub->id);

                $status =fwrite ($bf,end_tag("RECORD",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("RECORDS",4,true));
        }
        return $status;

}

function backup_baseline_files($bf,$preferences) {

    global $DB,$CFG;

    $status = true;

        //First we check to modbaseline exists and create it as necessary
        //in temp/backup/$backup_code  dir
    $status = check_and_create_modbaseline_dir($preferences->backup_unique_code);
        //Now copy the baseline dir
    if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
        if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline")) {
            $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline",
                                               $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/modbaseline/baseline");
        }
    }

    return $status;
}

function backup_baseline_file_instance($bf,$preferences,$instanceid) {

    global $DB,$CFG;
    $status = true;

        //First we check to modbaseline exists and create it as necessary
        //in temp/backup/$backup_code  dir
    $status = check_and_create_modbaseline_dir($preferences->backup_unique_code);
    $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/modbaseline/baseline/",true);
        //Now copy the baseline dir
    if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
        if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline/".$instanceid)) {
            $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->modbaseline."/baseline/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/modbaseline/baseline/".$instanceid);
        }
    }
    return $status;
}

function baseline_check_backup_mods_instances($instance,$backup_unique_code) {
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';
    if (!empty($instance->userbaseline)) {
        // any other needed stuff
    }
    return $info;
}

function baseline_check_backup_mods($course,$user_baseline=false,$backup_unique_code,$instances=null) {
    if (!empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ($instances as $id => $instance) {
            $info += baseline_check_backup_mods_instances($instance,$backup_unique_code);
        }
        return $info;
    }

    // otherwise continue as normal
    //First the course baseline
    $info[0][0] = get_string("modulenameplural","baseline");
    if ($ids = baseline_ids ($course)) {
        $info[0][1] = count($ids);
    } else {
        $info[0][1] = 0;
    }

    //Now, if requested, the user_baseline
    if ($user_baseline) {
        // any other needed stuff
    }
    return $info;

}

/**
 * Returns a content encoded to support interactivities linking. Every module
 * should have its own. They are called automatically from the backup procedure.
 *
 * @param string $content content to be encoded
 * @param object $preferences backup preferences in use
 * @return string the content encoded
 */
function baseline_encode_content_links ($content,$preferences) {

    global $DB,$CFG;

    $base = preg_quote($CFG->wwwroot,"/");

/// Link to one "record" of the baselinebase
    $search="/(".$base."\/mod\/baseline\/view.php\?d\=)([0-9]+)\&rid\=([0-9]+)/";
    $result= preg_replace($search,'$@BASELINEVIEWRECORD*$2*$3@$',$content);

/// Link to the list of baselinebases
    $search="/(".$base."\/mod\/baseline\/index.php\?id\=)([0-9]+)/";
    $result= preg_replace($search,'$@BASELINEINDEX*$2@$',$result);

/// Link to baselinebase view by moduleid
    $search="/(".$base."\/mod\/baseline\/view.php\?id\=)([0-9]+)/";
    $result= preg_replace($search,'$@BASELINEVIEWBYID*$2@$',$result);

/// Link to baselinebase view by baselinebaseid
    $search="/(".$base."\/mod\/baseline\/view.php\?d\=)([0-9]+)/";
    $result= preg_replace($search,'$@BASELINEVIEWBYD*$2@$',$result);

    return $result;
}

function baseline_ids($course) {
    // stub function, return number of modules
    return 1;
}
