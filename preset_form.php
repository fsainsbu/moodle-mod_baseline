<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden!');
}
require_once($CFG->libdir . '/formslib.php');


class baseline_existing_preset_form extends moodleform {
    public function definition() {
        $this->_form->addElement('header', 'presets', get_string('usestandard', 'baseline'));
        $this->_form->addHelpButton('presets', 'usestandard', 'baseline');

        $this->_form->addElement('hidden', 'd');
        $this->_form->addElement('hidden', 'action', 'confirmdelete');
        $delete = get_string('delete');
        foreach ($this->_custombaseline['presets'] as $preset) {
            $this->_form->addElement('radio', 'fullname', null, ' '.$preset->description, $preset->userid.'/'.$preset->shortname);
        }
        $this->_form->addElement('submit', 'importexisting', get_string('choose'));
    }
}

class baseline_import_preset_zip_form extends moodleform {
    public function definition() {
        $this->_form->addElement('header', 'uploadpreset', get_string('fromfile', 'baseline'));
        $this->_form->addHelpButton('uploadpreset', 'fromfile', 'baseline');

        $this->_form->addElement('hidden', 'd');
        $this->_form->addElement('hidden', 'action', 'importzip');
        $this->_form->addElement('filepicker', 'importfile', get_string('chooseorupload', 'baseline'));
        $this->_form->addRule('importfile', null, 'required');
        $this->_form->addElement('submit', 'uploadzip', get_string('import'));
    }
}

class baseline_export_form extends moodleform {
    public function definition() {
        $this->_form->addElement('header', 'exportheading', get_string('exportaszip', 'baseline'));
        $this->_form->addElement('hidden', 'd');
        $this->_form->addElement('hidden', 'action', 'export');
        $this->_form->addElement('submit', 'export', get_string('export', 'baseline'));
    }
}

class baseline_save_preset_form extends moodleform {
    public function definition() {
        $this->_form->addElement('header', 'exportheading', get_string('saveaspreset', 'baseline'));
        $this->_form->addElement('hidden', 'd');
        $this->_form->addElement('hidden', 'action', 'save2');
        $this->_form->addElement('text', 'name', get_string('name'));
        $this->_form->setType('name', PARAM_FILE);
        $this->_form->addRule('name', null, 'required');
        $this->_form->addElement('checkbox', 'overwrite', get_string('overwrite', 'baseline'), get_string('overrwritedesc', 'baseline'));
        $this->_form->addElement('submit', 'saveaspreset', get_string('continue'));
    }
}
