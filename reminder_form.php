<?php  // $Id: export_form.php,v 1.1.2.2 2008/05/31 15:03:34 robertall Exp $


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden!');
}
require_once($CFG->libdir . '/formslib.php');

class mod_data_reminder extends moodleform {
    var $_baselinefields = array();
     // @param string $url: the url to post to
     // @param array $baselinefields: objects in this baselinebase
    function mod_data_reminder($url, $baselinefields,  $userid,$mode) {
        $this->_datafields = $baselinefields;
        $this->userid = $userid;
        $this->mymode = $mode;
        parent::moodleform($url);
    }

    function definition() {
	global $DB, $CFG;
        $mode=$this->mymode;
        $mform =& $this->_form;
       // $mform->addElement('header', 'notice', get_string('chooseexportformat', 'baseline'));
        $choices = csv_import_reader::get_delimiter_list();
        $key = array_search(';', $choices);
        if (! $key === FALSE) {
            // array $choices contains the semicolon -> drop it (because its encrypted form also contains a semicolon):
            unset($choices[$key]);
        }
        if ($mode=='reminder') { 
	$mform->addElement('header', 'notice', get_string('choosefields', 'baseline'));
  	} else {
         // just print it.
        $mform->addElement('header', 'notice', get_string('reminders', 'baseline'));
	}
        foreach($this->_datafields as $field) {
        if ($mode=='reminder') {
                  $mform->addElement('advcheckbox', 'field_'.$field->id, '<div title="' . $field->name . '"></div>'. $field->description, ' (' . $field->name . ')', array('group'=>1));
         $chked = 0;
         if ($myff= $DB->get_records_select('baseline_reminder2user', 'userid = '. $this->userid. ' AND field_id = '. $field->id.' order by id desc')) {

           $chked =1;
        }
          $mform->setDefault('field_'.$field->id, $chked);
          $this->add_checkbox_controller(1, null, null, 1);
	  $this->add_action_buttons($cancel = true, $submitlabel=get_string('savechanges','baseline'));
         } else {
	 // just print it.
	$mform->addElement('header', 'reminder', $field->description  );
//	$mform->addElement('header', 'reminder', '<centre>'.$field->description.'</centre>'  );
	 }
         
    }

   }
}

?>
