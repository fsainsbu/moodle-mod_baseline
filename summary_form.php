<?php //$Id: summary_form.php,v 1.3 2006/12/28 09:32:50 jamiesensei Exp $
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Moodle Pty Ltd    http://moodle.com                //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////


require_once $CFG->libdir.'/formslib.php';
require_once($CFG->libdir.'/csvlib.class.php');


class mod_baseline_summary_form extends moodleform {
    function definition() {
        $mform =& $this->_form;

        // visible elements
        $mform->addElement('htmleditor', 'content', get_string('summary', 'baseline'), array('cols'=>85, 'rows'=>18));
        $mform->addRule('content', get_string('required'), 'required', null, 'client');
        $mform->setType('content', PARAM_RAW); // cleaned before the display

        $mform->addElement('text', 'format', get_string('format', 'baseline'));
         $mform->addHelpButton('format', 'textformat',  'baseline');

        // hidden optional params
        $mform->addElement('hidden', 'mode', 'add');
        $mform->setType('mode', PARAM_ALPHA);

        $mform->addElement('hidden', 'page', 0);
        $mform->setType('page', PARAM_INT);

        $mform->addElement('hidden', 'd', 0);
        $mform->setType('d', PARAM_INT);

        $mform->addElement('hidden', 'rid', 0);
        $mform->setType('rid', PARAM_INT);
        
	$mform->addElement('hidden', 'summaryid', 0);
        $mform->setType('summaryid', PARAM_INT);

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();

    }
}
?>
