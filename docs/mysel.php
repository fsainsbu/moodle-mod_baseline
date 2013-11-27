select r. timecreated from cf3_baseline_records r;
select c.content from cf3_baseline_base_content c ;
select c.content,r. timecreated  from cf3_baseline_base_content c ,cf3_baseline_records r where r.userid = 2 and r.id = c.recordid and c.fieldid =1;
select c.content,r. r.timecreated  from cf3_baseline_base_content c ,cf3_baseline_base_records r where r.userid = 2 and r.id = c.recordid and c.fieldid =1;
select c.content,r. r.timecreated  from cf3_baseline_content c ,cf3_baseline_records r where r.userid = 2 and r.id = c.recordid and c.fieldid =1;
 
 $table->data[] = array("<b>$strs->time:</b>", userdate($order->timecreated));

/// To actually fetch the records

        $fromsql    = "FROM $tables $advtables $where $advwhere $groupselect $approveselect $searchselect $advsearchselect";
        $sqlselect  = "SELECT $what $fromsql $sortorder";
        $sqlcount   = "SELECT $count $fromsql";   // Total number of records when searching
        $sqlrids    = "SELECT tmp.id FROM ($sqlselect) tmp";
        $sqlmax     = "SELECT $count FROM $tables $where $groupselect $approveselect"; // number of all recoirds user may see


 if (!$records = $DB->get_records_sql($sqlselect, $page * $nowperpage, $nowperpage)) {
            // Nothing to show!
            if ($record) { 

 if ($allrecordids = $DB->get_records_sql($sqlrids)) {
                $allrecordids = array_keys($allrecordids);
                $page = (int)array_search($record->id, $allrecordids);
                unset($allrecordids);
            }

SELECT r.id, r.userid, r.baselineid, u.id AS userexists, u.deleted AS userdeleted
                         FROM'.$this->field->myrecords.' r
                              INNER JOINbaseline d ON r.baselineid = d.id
                              LEFT OUTER JOINuser u ON r.userid = u.id
                        WHERE d.course = {$baseline->courseid} AND r.userid > 0";


field -> function get_diary(user,)
{
select c.content,r. r.timecreated  from cf3_baseline_base_content c ,cf3_baseline_base_records r where r.userid = 2 and r.id = c.recordid and c.fieldid =1;
select c.content,r. r.timecreated  from cf3_baseline_content c ,cf3_baseline_records r where r.userid = 2 and r.id = c.recordid and c.fieldid =1;

field->days= 
