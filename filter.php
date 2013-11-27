<?php // $Id: filter.php,v 1.11.4.1 2009/02/16 17:57:19 stronk7 Exp $
    //
    // This function provides automatic linking to baseline contents of text
    // fields where these fields have autolink enabled.
    //
    // Original code by Williams, Stronk7, Martin D.
    // Modified for baseline module by Vy-Shane SF.

    function baseline_filter($courseid, $text) {
        global $DB,$CFG;

        static $nothingtodo;
        static $contentlist;

        if (!empty($nothingtodo)) {   // We've been here in this page already
            return $text;
        }

        // if we don't have a courseid, we can't run the query, so
        if (empty($courseid)) {
            return $text;
        }

        // Create a list of all the resources to search for. It may be cached already.
        if (empty($contentlist)) {
            // We look for text field contents only, and only if the field has
            // autolink enabled (param1).
            $sql = 'SELECT dc.id AS contentid, ' .
                   'dr.id AS recordid, ' .
                   'dc.content AS content, ' .
                   'd.id AS baselineid ' .
                        'FROM '..'baseline d, ' .
                       .'baseline_fields df, ' .
                       .'baseline_records dr, ' .
                       .'baseline_content dc ' .
                            "WHERE (d.course = '$courseid' or d.course = '".SITEID."')" .
                            'AND d.id = df.baselineid ' .
                            'AND df.id = dc.fieldid ' .
                            'AND d.id = dr.baselineid ' .
                            'AND dr.id = dc.recordid ' .
                            "AND df.type = 'text' " .
                            "AND " .$DB->sql_compare_text('df.param1', 1) . " = '1'";

            if (!$baselinecontents = $DB->get_records_sql($sql)) {
                return $text;
            }

            $contentlist = array();

            foreach ($baselinecontents as $baselinecontent) {
                $currentcontent = trim($baselinecontent->content);
                $strippedcontent = strip_tags($currentcontent);

                if (!empty($strippedcontent)) {
                    $contentlist[] = new filterobject(
                                            $currentcontent,
                                            '<a class="baseline autolink" title="'.
                                            $strippedcontent.'" href="'.
                                            $CFG->wwwroot.'/mod/baseline/view.php?d='. $baselinecontent->baselineid .
                                            '&amp;rid='. $baselinecontent->recordid .'" '.$CFG->frametarget.'>',
                                            '</a>', false, true);
                }
            } // End foreach
        }
        return  filter_phrases($text, $contentlist);  // Look for all these links in the text
    }

?>
