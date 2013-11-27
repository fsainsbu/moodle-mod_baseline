 $allrecordssql = "SELECT r.id
                        FROMbaseline_records r
                             INNER JOINbaseline d ON r.baselineid = d.id
                       WHERE d.course = {$baseline->courseid}";
$records = $DB->->get_records_select('baseline_records', 'baselineid = '.$baseline->id.' AND userid = '.$user->id,
                                                      'timemodified DESC')) {



$mentors_ids = $DB->->get_records_select('baseline_mentor_user',)
$mentors_ids = $DB->->get_records_select('baseline_mentor_user',)
// FPS we need mentors_ids to list all ids a user can look at from table mentor_userid
// This is a library syle thing


// FPS if they mentor someone set up a list  for an IN clause select on tables.
if ($mentors_ids = $DB->get_records('baseline_mentor_user', 'userid', $baseline->id, 'mentor_userid')) { $mentors_ids = $baseline->id }

$where .= ' AND u.id in ' .mentors_ids;


