 get_base_diary(id)
select * from cf3_baseline_content limit 10;
 select content  from cf3_baseline_base_content where date submited is greater than oldest cf3_baseline_content + the next previous 1;
+----+---------+----------+---------+----------+----------+----------+----------+
| id | fieldid | recordid | content | content1 | content2 | content3 | content4 |
+----+---------+----------+---------+----------+----------+----------+----------+

$sql = 'SELECT c.* FROM '..'baseline_records r LEFT JOIN '.
          .'baseline_content c ON c.recordid = r.id WHERE r.baselineid = '.$id;

  if ($contents = $DB->get_records_sql($sql)) {
        foreach($contents as $content) {
            $field = $DB->get_record('baseline_fields','id',$content->fieldid);
            if ($g = baseline->get_field($field, $baseline)) {
                $g->delete_content_files($id, $content->recordid, $content->content);
            }
:q


k we need
 $url = "http://chart.apis.google.com/chart?cht=lxy&chdl=Daily|Baseline&chs=220x180&chco=ff0020,00ff00,0000ff,000000&chd=t:";

10,20,30,40,50|120,30,120,30,140|20,30,40,50,60|70,180,70,180,60";


Display_icns(i)
select this.field->name  from cf3_baseline_content limit 1;
select * from cf3_baseline_base_content limit 10;

