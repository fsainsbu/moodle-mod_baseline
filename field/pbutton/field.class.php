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

class baseline_field_pbutton extends baseline_field_base {

    var $type = 'pbutton';
/*
    function baseline_field_pbutton($field=0, $baseline=0) {
        parent::baseline_field_base($field, $baseline);
    }
*/


    function display_add_field($recordid=0) {
        global $DB,$CFG;

        if ($recordid){
            $content = trim($DB->get_field($this->field->mycontent, 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid)));

        } else {
            $content = '';
        }
      //placeholder for value:
   if (stristr(strtolower($_SERVER['HTTP_USER_AGENT']),'msie 8.0')) {
	$str = '<tr><td>';
	} else {
        // Superfulous, ignored in all but ie 8 
	$str="<tr><td>";
	}
	$str .=  "\n";
        $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name.'</span></legend>';

        $i = 0;
        foreach (explode("\n",$this->field->param3) as $answer)
        {
 	 $answerClean = explode("\xE2",$answer);
	 if (array_key_exists(2,$answerClean) ) {
          $answerPartTwo =trim(substr($answerClean[2],2,strlen($answerClean[2])-3));
	   $answer2[$i] = trim($answerClean[0].':'.$answerPartTwo);
	} else {
	$answer2[$i] = trim($answerClean[0]);
	}
	$i++;
	}
        $i = 0;
        foreach (explode("\n",$this->field->param1) as $pbutton) {
            $pbutton = trim($pbutton);
            if ($pbutton === '') {
                continue; // skip empty lines
            }
     if ($i == 0 )  $mystr = '<label  for="field_'.$this->field->id.'" style="display: none;">'.$this->field->name.'</label> <select name="field_'.$this->field->id.'" size="1"  id="field_'.$this->field->id.'" value ="'.$pbutton.'"  style="display: none;" > ';
	//was radio needs to look like:
            $k=$i+1;
	if (trim($answer2[$i]) === '') {
            $str .= "\n".'<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"'.' onfocus="DisplayAnItem('."'edit.php','".$this->field->id."',".$i.');" onblur="nd();"><img src="cf/'.$this->field->name.$i.'.gif" tabindex='.'1'.' width="96" height="96" alt = "'.trim($answer2[$i]). '" title="' . trim($answer2[$i]) . '" class="uihint" ';"'\n"; 
         $i++;
        }
        }
        $i = 0;
        foreach (explode("\n",$this->field->param1) as $pbutton) {
            $pbutton = trim($pbutton);
            if ($pbutton === '') {
                continue; // skip empty lines
            }
     if ($i == 0 )  $mystr = '<label  for="field_'.$this->field->id.'" style="display: none;">'.$this->field->name.'</label> <select name="field_'.$this->field->id.'" size="1"  id="field_'.$this->field->id.'" value ="'.$pbutton.'"  style="display: none;" > ';
        //was radio needs to look like:
            $k=$i+1;
        if (trim($answer2[$i]) === '') {
             $str .= "\n".'<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"><img src="cf/'.$this->field->name.$i.'.gif" tabindex='.'1'.' width="96" height="96" alt="'.trim($answer2[$i]).'" title="' . trim($answer2[$i]) . '" class="uihint"  ';
   } else {

            $str .= "\n".'<a href="javascript:SelectAnItem('."'edit.php','".$this->field->id."',".$i.');"'.' onfocus="DisplayAnItem('."'edit.php','".$this->field->id."',".$i.');" onblur="nd();"'.' onmouseover="return overlib('."'".trim($answer2[$i])."'".');" onmouseout="nd()"><img src="cf/'.$this->field->name.$i.'.gif" tabindex='.'1'.' width="96" height="96" alt = "'.trim($answer2[$i]). '" title="' . trim($answer2[$i]) .'" class="uihint" ';
//"'\n'"; 
}

            if ($content == $pbutton) {
                // Selected by user.
                $str .='style="border-style:inset; border-width:1px; border-color:gray" id="'.$this->field->id.'-'.$i.'"';
                $str .= 'checked ></a>&nbsp&nbsp&nbsp';
            } else {
                $str .='style="border-style:none; border-width:1px;" id="'.$this->field->id.'-'.$i.'"';
                $str .= '></a>&nbsp&nbsp';
            }

        if ( ! $content) { $content = -1; }
            if ($content == $pbutton) {
                // Selected by user.
                $mystr .= '<option selected="selected"';
		} else {
               $mystr .= '<option ';
            } 
            $mystr .= 'value="'.$pbutton. '">'.$pbutton.'</option>';
		$answer4[$i]=$pbutton;
            $i++;
        }
        $str .= $mystr;
	//$str .= '<label  style="display: none;"  for="field_'.$this->field->id.'">'.$this->field->name.'</label>'."\n";
        $str .= '</select>';
        $str .= '</fieldset>';
      	$countme = 0;
        
         foreach (explode("\n",$this->field->param3) as $answer)
        {
         $answer2 =  explode("\xE2",$answer);
	 if (array_key_exists(2,$answer2) ) {
         $answer3 =  substr($answer2[2],2,strlen($answer2[2])-3);
          $answerp =trim($answer2[0].': '.$answer3);
         } else  $answerp = trim($answer);
         if ($answer4[$countme] == $content ) { $ax = ' active'; 
                 $str .= '<span  style="display:block;"  id="'.$this->field->id.'-'.$countme.'_txt" >'.$answerp.' </span>';
		} else { $ax = '';
                $str .= '<span  style="display: none;"  id="'.$this->field->id.'-'.$countme.'_txt" >'.$answerp.'</span>';
            //$str .= '<label  style="display: none;"  for="'.$this->field->id.'-'.$countme.'">'.$answerp.'</label>'."\n";
		}
           //  $str .= '<span  style="display: none;"  id="'.$this->field->id.'-'.$countme.'_txt" > <a href="javascript:void(0);" onmouseover="return overlib('."'".$answer3."'".', AUTOSTATUS, WRAP,WIDTH ,250);" onmouseout="nd();">'.trim($answer2[0].': '.$answer3).' </a></p></li></span>';
                //$str .= '<span  style="display:block;"  id="'.$this->field->name.'-'.$countme.'" class="'.$countme.$ax.'"><p><a href="javascript:void(0);" onmouseover="return overlib('."'".$answer3."'".', AUTOSTATUS, WRAP);" onmouseout="nd();">'.$answer2[0].': '.$answer3.' </a></p></li></span>';
                //$str .= '<li '.$answer2[0].' '.$answer3.'></p></li>';
                $countme++;
}

        $str .= '</div>';
        return $str;
    }
    
     function display_search_field($value = '') {
        global $DB,$CFG;
        $temp = $DB->get_records_sql_menu('SELECT id, content from {'.$this->field->mycontent.'} WHERE fieldid='.$this->field->id.' GROUP BY content ORDER BY content');
        $options = array();
        if(!empty($temp)) {
            $options[''] = '';              //Make first index blank.
            foreach ($temp as $key) {
                $options[$key] = $key;  //Build following indicies from the sql.
            }
        }
        // return choose_from_menu($options, 'f_'.$this->field->id, $value, 'choose', '', 0, true);
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
