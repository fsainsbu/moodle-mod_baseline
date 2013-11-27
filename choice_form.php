<?php  // $Id: export_form.php,v 1.1.2.2 2008/05/31 15:03:34 robertall Exp $


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden!');
}
require_once($CFG->libdir . '/formslib.php');
 require_once($CFG->libdir . '/csvlib.class.php');

class mod_data_choice extends moodleform {
    var $_baselinefields = array();
     var $_cm;
     // @param string $url: the url to post to
     // @param array $baselinefields: objects in this baselinebase
    function mod_data_choice($url, $baselinefields, $userid) {
        $this->_datafields = $baselinefields;
        $this->userid = $userid;
        $this->_cm = $cm;
        parent::moodleform($url);
    }

    function definition() {
	global $CFG;
        $mform =& $this->_form;
        $mform->addElement('header', 'notice', get_string('setting', 'baseline'));
        $choices = csv_import_reader::get_delimiter_list();
        $key = array_search(';', $choices);
        if (! $key === FALSE) {
            // array $choices contains the semicolon -> drop it (because its encrypted form also contains a semicolon):
            unset($choices[$key]);
        }
       /* 
        $typesarray = array();
        $typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('csvwithselecteddelimiter', 'baseline') . '&nbsp;', 'csv');
        $typesarray[] = &MoodleQuickForm::createElement('select', 'delimiter_name', null, $choices);
        $typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('excel', 'baseline'), 'xls');
        $typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('ods', 'baseline'), 'ods');
        $mform->addGroup($typesarray, 'exportar', '', array(''), false);
        $mform->addRule('exportar', null, 'required');
        $mform->setDefault('exporttype', 'csv');
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
*/
        $mform->addElement('header', 'notice', get_string('choosefields', 'baseline'));
        foreach($this->_datafields as $field) {
            if($field->text_export_supported()) {
                $mform->addElement('advcheckbox', 'field_'.$field->field->id, $field->field->param5  );
         $chked = 0;
         if ($myff= $DB->->get_records_select('baseline_field2user', 'userid = '. $this->userid. ' AND field_id = '. $field->field->id,'id DESC')) {

           $chked =1;
        }
                $mform->setDefault('field_'.$field->field->id, $chked);
            } else {
                $a = new object;
                $a->fieldtype = $field->name();
                $mform->addElement('static', 'unsupported'.$field->field->id, $field->field->name, get_string('unsupportedexport', 'baseline', $a));
            }
        }
        $this->add_checkbox_controller(1, null, null, 1);
	 $this->add_action_buttons($cancel = true, $submitlabel=get_string('savechanges','baseline'));
    }

}

?>
