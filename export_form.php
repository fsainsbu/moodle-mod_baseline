<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden!');
}
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

class mod_baseline_export_form extends moodleform {
    var $_baselinefields = array();
    var $_cm;

     // @param string $url: the url to post to
     // @param array $baselinefields: objects in this baseline
    function mod_baseline_export_form($url, $baselinefields, $cm) {
        $this->_baselinefields = $baselinefields;
        $this->_cm = $cm;
        parent::moodleform($url);
    }

    function definition() {
        global $CFG;
        $mform =& $this->_form;
        $mform->addElement('header', 'notice', get_string('chooseexportformat', 'baseline'));
        $choices = csv_import_reader::get_delimiter_list();
        $key = array_search(';', $choices);
        if (! $key === FALSE) {
            // array $choices contains the semicolon -> drop it (because its encrypted form also contains a semicolon):
            unset($choices[$key]);
        }
        $typesarray = array();
        $typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('csvwithselecteddelimiter', 'baseline') . '&nbsp;', 'csv');
        $typesarray[] = &MoodleQuickForm::createElement('select', 'delimiter_name', null, $choices);
        //temporarily commenting out Excel export option. See MDL-19864
        //$typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('excel', 'baseline'), 'xls');
        $typesarray[] = &MoodleQuickForm::createElement('radio', 'exporttype', null, get_string('ods', 'baseline'), 'ods');
        $mform->addGroup($typesarray, 'exportar', '', array(''), false);
        $mform->addRule('exportar', null, 'required');
        $mform->setDefault('exporttype', 'csv');
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
        $mform->addElement('header', 'notice', get_string('chooseexportfields', 'baseline'));
        foreach($this->_baselinefields as $field) {
            if($field->text_export_supported()) {
                $mform->addElement('advcheckbox', 'field_'.$field->field->id, '<div title="' . s($field->field->description) . '">' . $field->field->name . '</div>', ' (' . $field->name() . ')', array('group'=>1));
                $mform->setDefault('field_'.$field->field->id, 1);
            } else {
                $a = new stdClass();
                $a->fieldtype = $field->name();
                $mform->addElement('static', 'unsupported'.$field->field->id, $field->field->name, get_string('unsupportedexport', 'baseline', $a));
            }
        }
        $this->add_checkbox_controller(1, null, null, 1);
        $this->add_action_buttons(true, get_string('exportentries', 'baseline'));
    }

}


