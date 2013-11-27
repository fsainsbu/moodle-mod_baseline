<?php

defined('MOODLE_INTERNAL') || die();

class mod_baseline_renderer extends plugin_renderer_base {
    
    public function import_setting_mappings($baselinemodule, baseline_preset_importer $importer) {

        $strblank = get_string('blank', 'baseline');
        $strcontinue = get_string('continue');
        $strwarning = get_string('mappingwarning', 'baseline');
        $strfieldmappings = get_string('fieldmappings', 'baseline');
        $strnew = get_string('new');


        $params = $importer->get_preset_settings();
        $settings = $params->settings;
        $newfields = $params->importfields;
        $currentfields = $params->currentfields;

        $html  = html_writer::start_tag('div', array('class'=>'presetmapping'));
        $html .= html_writer::start_tag('form', array('method'=>'post', 'action'=>''));
        $html .= html_writer::start_tag('div');
        $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'finishimport'));
        $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));
        $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'d', 'value'=>$baselinemodule->id));
        
        if ($importer instanceof baseline_preset_existing_importer) {
            $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'fullname', 'value'=>$importer->get_userid().'/'.$importer->get_directory()));
        } else {
            $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'directory', 'value'=>$importer->get_directory()));
        }

        if (!empty($newfields)) {
            $html .= $this->output->heading_with_help($strfieldmappings, 'fieldmappings', 'baseline');

            $table = new html_table();
            $table->data = array();

            foreach ($newfields as $nid => $newfield) {
                $row = array();
                $row[0] = html_writer::tag('label', $newfield->name, array('for'=>'id_'.$newfield->name));
                $row[1] = html_writer::start_tag('select', array('name'=>'field_'.$nid, 'id'=>'id_'.$newfield->name));

                $selected = false;
                foreach ($currentfields as $cid => $currentfield) {
                    if ($currentfield->type != $newfield->type) {
                        continue;
                    }
                    if ($currentfield->name == $newfield->name) {
                        $row[1] .= html_writer::tag('option', get_string('mapexistingfield', 'baseline', $currentfield->name), array('value'=>$cid, 'selected'=>'selected'));
                        $selected=true;
                    } else {
                        $row[1] .= html_writer::tag('option', get_string('mapexistingfield', 'baseline', $currentfield->name), array('value'=>$cid));
                    }
                }

                if ($selected) {
                    $row[1] .= html_writer::tag('option', get_string('mapnewfield', 'baseline'), array('value'=>'-1'));
                } else {
                    $row[1] .= html_writer::tag('option', get_string('mapnewfield', 'baseline'), array('value'=>'-1', 'selected'=>'selected'));
                }
                
                $row[1] .= html_writer::end_tag('select');
                $table->data[] = $row;
            }
            $html .= html_writer::table($table);
            $html .= html_writer::tag('p', $strwarning);
        } else {
            $html .= $this->output->notification(get_string('nodefinedfields', 'baseline'));
        }

        $html .= html_writer::start_tag('div', array('class'=>'overwritesettings'));
        $html .= html_writer::tag('label', get_string('overwritesettings', 'baseline'), array('for'=>'overwritesettings'));
        $html .= html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'overwritesettings', 'id'=>'overwritesettings'));
        $html .= html_writer::end_tag('div');
        $html .= html_writer::empty_tag('input', array('type'=>'submit', 'class'=>'button', 'value'=>$strcontinue));

        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('form');
        $html .= html_writer::end_tag('div');

        return $html;
    }
    
}
