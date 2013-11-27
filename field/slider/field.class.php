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

class baseline_field_slider extends baseline_field_base {

    var $type = 'slider', $baselinemarker, $baseline;

/*    function baseline_field_slider($field=0, $baseline=0) {
        parent::baseline_field_base($field, $baseline);
    }
*/
    function display_add_field($recordid=0) {
        global $CFG;
     
        if ($recordid){
            $content = trim($DB->get_field($this->field->mycontent, 'content', 'fieldid', $this->field->id, 'recordid', $recordid));
        } else {
            $content = '';
        }
        $mystr = '';
    // $str = '<link rel="stylesheet" type="text/css" href="'.$CFG->httpswwwroot.'/lib/yui/fonts/fonts-min.css" />
	$str = '<td class="slider"><link rel="stylesheet" type="text/css" href="slider/assets/skins/sam/slider.css" />
<script type="text/javascript" src="'.$CFG->httpswwwroot.'/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="'.$CFG->httpswwwroot.'/lib/yui/2.9.0/build/dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="slider/slider-min.js"></script>';
  // tried in templates js worked in test needs debugging ...
  $str .= '<script type="text/javascript" src="'.$CFG->httpswwwroot.'/mod/baseline/field/slider/slider.js"></script>' ."\n";
        $i = 0;
	$Myoff = 0;
        foreach (explode("\n",$this->field->param1) as $slider) {
            $slider = trim($slider);
            if ($slider === '') {
                continue; // skip empty lines
            }

            $k=$i+1;

      // hide select   FPS Feb 2010
       if ($i == 0 )  $mystr .= '<select name="myfield_'.$this->field->id.'" size="1"  id="myfield_'.$this->field->id.'" value ="'.$content.'" style="display: none;">';
       // if ($i == 0 )  $mystr .= '<select name="myfield_'.$this->field->id.'" size="1"  id="myfield_'.$this->field->id.'" value ="'.$content.'" ;>';
          if($slider == 0)      $Myoff = $i;

            if ($content == $slider) {
		$mystr .= '<option selected="selected"';
                // Selected by user.
                $sliderstart=$i ;
               // $mystr .= '<option selected="selected" ';
            }   else { 
               $mystr .= '<option ';
            }
            $mystr .= 'value="'.$slider. '">'.$i.'</option>';
            //$mystr .= 'value="'.$slider. '">'.$slider.'</option>';
            $i++;
        }
//Change the $baseline to add a select baseline query to the slider
	 //print_r($this->field->basemyrecs);
	//echo '<br>';
foreach (explode(",",$this->field->basemyrecs) as $baseline)
	{
	}
	$baseline = $baseline + $Myoff;
	// echo $baseline;
        $mycount = $i;
       $mystr .="</select>";
	$mywidth = 400;
	$baselinemarker = ($mywidth / (5)) *($baseline); 
        $mystep = floor($mywidth / ($mycount ));
        $mywidth = $mystep * ($mycount );
             // $mystr = '<input type="text" size="6" name="mytextfield_'.$this->field->id.'" value="'.$content.'" id="mytextfield_'.$this->field->id.'" style="display: none;" />';
             // $mystr = '<input type="text" size="6" name="nametextfield_'.$this->field->id.'" value="'.$content.'" id="myfield_'.$this->field->id.'"  />';
          //  $mystr .= ' Slider Name '.$content.'  <span id="sliderName" style="display: none;"> </span> ';
           $mystr .= '   <span id="sliderName" style="display: none;"> </span> ';
             //$mystr .= '<span id="mytextfield_'.$this->field->id.'" style="display: none;" >'.$content.' </span>';
             //$mystr .= '<span id="mytextfield_'.$this->field->id.'" style="display: none;" > </span>';
             $myinit=$content+$Myoff;
             $mystr .= '<span class="accesshide" id="'.$this->field->name.'" value='.$content.'></span>';
        $str .= $mystr ."\n";

//css will need to be generated here... FPS
      // <!--begin custom header content for this example-->
 // #'.$this->field->name.'booth {float: left; background: #f2f2f2 url('.$CFG->httpswwwroot.'/boothBg.gif) 10 20 no-repeat; width: '.$mywidth.'px; height: 700px;}
$str .= '<style type="text/css"> 
#'.$this->field->name.'Slider-bg {position: relative; background:url(cf/Baseline.png) bottom left no-repeat ; width: '.$mywidth.'px; height: 145px; left: 5px;}
#'.$this->field->name.'SliderThumb {position: relative; } 
#'.$this->field->name.'baselinemarker {position: relative; left: 0px;}
</style>';
// #'.$this->field->name.'baselinemarker {position: absolute; left: '.$baselinemarker.'px;}
        $str .= '<div id="'.$this->field->name.'">';
        $str .= '    <div id="'.$this->field->name.'Slider-bg" class="yui-h-slider" tabindex="-1" title="'.$this->field->name.'Slider"> ';
       //  $str .= ' <div id="'.$this->field->name.'baselinemarker" class="yui-slider-thumb"> <img src="cf/Baseline.png" width=400 /></div>';
	$str .= '      <div id="'.$this->field->name.'SliderThumb" class="yui-slider-thumb"><img src="cf/MeterArrow.gif" width=51 /></div>';
       $str .= '    </div>';

       // $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name.'</span></legend>';
      $startstep =( $i  - $content) * $mystep;
     $str .= '<script type="text/javascript"> ';
	$mydiff=$mywidth - 51; //Kludge need to get width of files ;
    $str .= 'var '.$this->field->name.'Slider = MySliderInit("'.$this->field->name.'Slider-bg","'.$this->field->name.'SliderThumb","'.$this->field->name.'","myfield_'.$this->field->id.'","'.$this->field->name.'Slider",'.$mystep.','.$mydiff.','.$Myoff.');';
     $str .=  ' </script >  ';
	//$str .= '</td> </tr>'."\n".' <br><br><tr class="question '.$this->field->param2.'"><td></td> <td class="answers"> <ul>';
	$str .= '</td> </tr>'.' <tr class="question '.$this->field->param2.'"><td></td> <td class="answers"> <ul>';
	$countme = 1;
 	if ( ! $content) { $content = 5; }
	foreach (explode("\n",$this->field->param3) as $answer)
        {
	 $answer2 =  explode("\xE2",$answer); 
	 // $answer3 =  explode("\xE3",$answer2[2]); 
	 $answer3 =  substr($answer2[2],2,strlen($answer2[2])-3); 
 //  FPS print_r($answer2 );
 //  FPS print_r($answer3 );
 //  FPS print("<br>");
// "<a href=\"javascript:void(0);\" onmouseover=\"return overlib('This is a simple tooltip popup.', AUTOSTATUS, WRAP);\" onmouseout=\"nd();\">tooltip</a>';";

	 if ($countme == $content ) { $ax = ' active'; } else { $ax = '';}
		$str .= '<li id="'.$this->field->name.'-'.$countme.'" class="'.$countme.$ax.'"><p><a href="javascript:void(0);" onmouseover="return overlib('."'".$answer3."'".', AUTOSTATUS, WRAP);" onmouseout="nd();">'.$answer2[0].'</a></p></li>';
		$countme++;
	}

	$str .= '</ul> </td></tr>';
        return $str;
    }
    
     function display_search_field($value = '') {
        global $DB, $CFG;
 $sql = 'SELECT id, content FROM {baseline_content} c WHERE fieldid='.$this->field->id.' GROUP BY content ORDER BY content';
        $temp = $DB->get_records_sql_menu($sql);
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
