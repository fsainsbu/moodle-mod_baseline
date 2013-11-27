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

class baseline_field_bpbutton extends baseline_field_base {

    var $type = 'bpbutton';

    function baseline_field_bpbutton($field=0, $baseline=0) {
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
        $str="<td>";
        // $str .= '<div> <img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.'00.gif" width="248" height="104"'.' alt="'.s($this->field->description).'"> ';
        $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name.'</span></legend>';

        $i = 0;

        foreach (explode("\n",$this->field->param3) as $answer)
        {
	 $answerClean = explode("\xE2",$answer);
         $answer2[$i] = trim($answerClean[0]).':'.substr($answerClean[2],2,strlen($answerClean[2])-3);
	  //echo $i.' '. $answer2[$i][0].'XX'. $answer2[$i][1].'<br>';
	
 	/*if ( trim($answer2[$i][0]) == '' ) {
	  $answer2[$i][0] = i; 
	 $answer3[$i] = '';
	print_r($answer2[$i]);
	} else {
         $answer3[$i] =  substr($answer2[$i][2],2,strlen($answer2[$i][2])-3);
	 $answer3[$i] = '';
	print_r($answer3[$i]);
	}
	print_r( $answer2);
	echo '<br>';
*/
	$i++;
	}
        $i = 0;
        foreach (explode("\n",$this->field->param1) as $bpbutton) {
            $bpbutton = trim($bpbutton);
            if ($bpbutton === '') {
                continue; // skip empty lines
            }
            if ($i == 0 )  $mystr = '<select name="field_'.$this->field->id.'" size="1"  id="field_'.$this->field->id.'" value ="'.$bpbutton.'"  style="display: none;">';
	//was radio needs to look like:
            $k=$i+1;
	if (trim($answer2[$i]) === '') {
            $str .= '<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"><img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.$i.'.gif" width="250" height="96" alt = "'.$bpbutton.'"  ';
            //$str .= '<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"><img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.$i.'.gif" alt = "'.$bpbutton.'"  ';
   } else {
            //$str .= '<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"'.' onmouseover="return overlib('."'".trim($answer2[$i])."'".', SNAPX, 20, SNAPY, 20, BGCOLOR, '."'".'#000000'."'".');" onmouseout="nd();"><img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.$i.'.gif"  alt = "'.$bpbutton.'"  ';
            $str .= '<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"'.' onmouseover="return overlib('."'".trim($answer2[$i])."'".', SNAPX, 20, SNAPY, 20, BGCOLOR, '."'".'#000000'."'".');" onmouseout="nd();"><img src="'.$CFG->httpswwwroot.'/pix/cf/'.$this->field->name.$i.'.gif" width="250" height="96" alt = "'.$bpbutton.'"  ';
}

            if ($content == $bpbutton) {
                // Selected by user.
                $str .='"style="border-style:inset;border-width:1px;border-color:gray" id="'.$this->field->id.'-'.$i.'"';
                $str .= 'checked ></a>&nbsp&nbsp&nbsp';
            } else {
                $str .='"style="border-style:none;border-width:1px;" id="'.$this->field->id.'-'.$i.'"';
                $str .= '></a>&nbsp&nbsp&nbsp';
            }

//            $str .= '<label for="field_'.$this->field->id.'_'.$i.'">'.$bpbutton.'</label>'."\n";
      $countme = 0;
        if ( ! $content) { $content = 5; }
            if ($content == $bpbutton) {
                // Selected by user.
                $mystr .= '<option selected="selected"';
		} else {
               $mystr .= '<option ';
            } 
            $mystr .= 'value="'.$bpbutton. '">'.$bpbutton.'</option>';
            $i++;
        }
        $str .= $mystr;
        $str .= '</select>';
        $str .= '</fieldset>';
         foreach (explode("\n",$this->field->param3) as $answer)
        {
         $answer2 =  explode("\xE2",$answer);
         $answer3 =  substr($answer2[2],2,strlen($answer2[2])-3);

	// if (trim($answer3) === '' )  $answer3 = $countme; 
         if ($countme == $content ) { $ax = ' active'; } else { $ax = '';}
                $str .= '<span  style="display: none;"  id="'.$this->field->id.'-'.$countme.'_txt" > <a href="javascript:void(0);" onmouseover="return overlib('."'".$answer3."'".', AUTOSTATUS, WRAP,WIDTH ,250);" onmouseout="nd();">'.trim($answer2[0].': '.$answer3).' </a></p></li></span>';
                //$str .= '<span  style="display:block;"  id="'.$this->field->name.'-'.$countme.'" class="'.$countme.$ax.'"><p><a href="javascript:void(0);" onmouseover="return overlib('."'".$answer3."'".', AUTOSTATUS, WRAP);" onmouseout="nd();">'.$answer2[0].': '.$answer3.' </a></p></li></span>';
                //$str .= '<li '.$answer2[0].' '.$answer3.'></p></li>';
                $countme++;
}

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
	return html_writer::select($options, 'f_'.$this->field->id, $value);
        // return choose_from_menu($options, 'f_'.$this->field->id, $value, 'choose', '', 0, true);
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }
    
    function generate_sql($tablealias, $value) {
        return " ({$tablealias}.fieldid = {$this->field->id} AND {$tablealias}.content = '$value') "; 
    }

}
?>
