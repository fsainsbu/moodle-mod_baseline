<?php // $Id: field.class.php,v 1.9 2007/02/26 06:56:09 toyomoyo Exp $
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999-onwards Moodle Pty Ltd  http://moodle.com          //
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

class baseline_field_returnmenu extends baseline_field_base {

    var $type = 'returnmenu';

    function baseline_field_returnmenu($field=0, $baseline=0) {
        parent::baseline_field_base($field, $baseline);
    }


    function display_add_field($recordid=0) {
        global $DB,$CFG;

        if ($recordid){
            $content = trim($DB->->get_field($this->field->mycontent, 'content', 'fieldid', $this->field->id, 'recordid', $recordid));
        } else {
            $content = '';
        }
      //placeholder for value:
        //$str = "<input type='hidden' name='".'field_' . $this->field->id ."' value=''";
        $str="";
        // $str .= '<div> <img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.'00.gif" width="248" height="104"'.' alt="'.s($this->field->description).'"> ';
        $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name.'</span></legend>';

        $i = 0;
        foreach (explode("\n",$this->field->param1) as $returnmenu) {
            $returnmenu = trim($returnmenu);
            if ($returnmenu === '') {
                continue; // skip empty lines
            }
//get display into 2nd part, return in first part
           $vals=explode('.',$returnmenu,2);
             $fieldname='field_'.$this->field->id;
             $fieldid='field_'.$this->field->id;
            //if ($i == 0 )  $mystr = '<select name="field_'.$this->field->id.'" size="1"  id="field_'.$this->field->id.'" value ="'.$returnmenu.'"  ;">';
            if ($i == 0 )  $mystr = '<select name="'.$fieldname.'" size="1"  id="'.$fieldid.'" value ="'.$vals[0].'"  ;">';
            $k=$i+1;
            // $str .= '<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"'.'><img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.$i.'.gif" width="70" height="90 alt = "'.$returnmenu.'" ';

            if ($content == $vals[0]) {
                // Selected by user.
                $mystr .= '<option selected="selected"';
                //$str .='"style="border-style:inset;border-width:1px;border-color:grey" id="'.$this->field->id.'-'.$i.'"';
                //$str .= 'checked ></a>';
            } else {
               $mystr .= '<option ';
                //$str .='"style="border-style:none;border-width:1px;" id="'.$this->field->id.'-'.$i.'"';
                //$str .= '></a>';
            }

            $mystr .= 'value="'.$vals[0]. '">'.$vals[1].'</option>';
            $i++;
        }
        $str .= $mystr;
        $str .= '</select>';
        $str .= '</fieldset>';
        $str .= '</div>';
        return $str;
    }
    
     function display_search_field($value = '') {
        global $DB,$CFG;
        $temp = $DB->get_records_sql_menu('SELECT id, content from '.'baseline_content WHERE fieldid='.$this->field->id.' GROUP BY content ORDER BY content');
        $options = array();
        if(!empty($temp)) {
            $options[''] = '';              //Make first index blank.
            foreach ($temp as $key) {
                $options[$key] = $key;  //Build following indicies from the sql.
            }
        }
        //return choose_from_menu($options, 'f_'.$this->field->id, $value, 'choose', '', 0, true);
	return html_writer::select($options, 'f_'.$this->field->id, $value);
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }
    
    function generate_sql($tablealias, $value) {
        return " ({$tablealias}.fieldid = {$this->field->id} AND {$tablealias}.content = '$value') "; 
    }

}
?>
