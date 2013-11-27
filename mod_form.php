<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_baseline_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('intro', 'baseline'));

        $mform->addElement('date_selector', 'timeavailablefrom', get_string('availablefromdate', 'baseline'), array('optional'=>true));

        $mform->addElement('date_selector', 'timeavailableto', get_string('availabletodate', 'baseline'), array('optional'=>true));

        $mform->addElement('date_selector', 'timeviewfrom', get_string('viewfromdate', 'baseline'), array('optional'=>true));

        $mform->addElement('date_selector', 'timeviewto', get_string('viewtodate', 'baseline'), array('optional'=>true));


        $countoptions = array(0=>get_string('none'))+
                        (array_combine(range(1, BASELINE_MAX_ENTRIES),//keys
                                        range(1, BASELINE_MAX_ENTRIES)));//values
        $mform->addElement('select', 'requiredentries', get_string('requiredentries', 'baseline'), $countoptions);
        $mform->addHelpButton('requiredentries', 'requiredentries', 'baseline');

        $mform->addElement('select', 'requiredentriestoview', get_string('requiredentriestoview', 'baseline'), $countoptions);
        $mform->addHelpButton('requiredentriestoview', 'requiredentriestoview', 'baseline');

        $mform->addElement('select', 'maxentries', get_string('maxentries', 'baseline'), $countoptions);
        $mform->addHelpButton('maxentries', 'maxentries', 'baseline');

        $ynoptions = array(0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'comments', get_string('comments', 'baseline'), $ynoptions);

        $mform->addElement('select', 'approval', get_string('requireapproval', 'baseline'), $ynoptions);
        $mform->addHelpButton('approval', 'requireapproval', 'baseline');

        if($CFG->enablerssfeeds && $CFG->baseline_enablerssfeeds){
            $mform->addElement('select', 'rssarticles', get_string('numberrssarticles', 'baseline') , $countoptions);
        }

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    function baseline_preprocessing(&$default_values){
        parent::baseline_preprocessing($default_values);
    }

}

