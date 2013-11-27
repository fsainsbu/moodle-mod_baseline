<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod-baseline
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Some constants
define ('BASELINE_MAX_ENTRIES', 50);
define ('BASELINE_PERPAGE_SINGLE', 1);

define ('BASELINE_FIRSTNAME', -1);
define ('BASELINE_LASTNAME', -2);
define ('BASELINE_APPROVED', -3);
define ('BASELINE_TIMEADDED', 0);
define ('BASELINE_TIMEMODIFIED', -4);

define ('BASELINE_CAP_EXPORT', 'mod/baseline:viewalluserpresets');

define('BASELINE_PRESET_COMPONENT', 'mod_baseline');
define('BASELINE_PRESET_FILEAREA', 'site_presets');
define('BASELINE_PRESET_CONTEXT', SYSCONTEXTID);

// Users having assigned the default role "Non-editing teacher" can export baseline records
// Using the mod/baseline capability "viewalluserpresets" existing in Moodle 1.9.x.
// In Moodle >= 2, new roles may be introduced and used instead.

/**
 * @package   mod-baseline
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class baseline_field_base {     // Base class for Baselinebase Field Types (see field/*/field.class.php)

    /** @var string Subclasses must override the type with their name */
    var $type = 'unknown';
    /** @var object The baseline object that this field belongs to */
    var $baseline = NULL;
    /** @var object The field object itself, if we know it */
    var $field = NULL;
    /** @var int Width of the icon for this fieldtype */
    var $iconwidth = 16;
    /** @var int Width of the icon for this fieldtype */
    var $iconheight = 16;
    /** @var object course module or cmifno */
    var $cm;
    /** @var object activity context */
    var $context;

    /**
     * Constructor function
     *
     * @global object
     * @uses CONTEXT_MODULE
     * @param int $field
     * @param int $baseline
     * @param int $cm
     */
    function __construct($field=0, $baseline=0, $cm=0) {   // Field or baseline or both, each can be id or object
        global $DB;

        if (empty($field) && empty($baseline)) {
            print_error('missingfield', 'baseline');
        }

        if (!empty($field)) {
            if (is_object($field)) {
                $this->field = $field;  // Programmer knows what they are doing, we hope
            } else if (!$this->field = $DB->get_record('baseline_fields', array('id'=>$field))) {
                print_error('invalidfieldid', 'baseline');
            }
            if (empty($baseline)) {
                if (!$this->baseline = $DB->get_record('baseline', array('id'=>$this->field->baselineid))) {
                    print_error('invalidid', 'baseline');
                }
            }
        }

        if (empty($this->baseline)) {         // We need to define this properly
            if (!empty($baseline)) {
                if (is_object($baseline)) {
                    $this->baseline = $baseline;  // Programmer knows what they are doing, we hope
                } else if (!$this->baseline = $DB->get_record('baseline', array('id'=>$baseline))) {
                    print_error('invalidid', 'baseline');
                }
            } else {                      // No way to define it!
                print_error('missingbaseline', 'baseline');
            }
        }

        if ($cm) {
            $this->cm = $cm;
        } else {
            $this->cm = get_coursemodule_from_instance('baseline', $this->baseline->id);
        }

        if (empty($this->field)) {         // We need to define some default values
            $this->define_default_field();
        }

        $this->context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
    }


    /**
     * This field just sets up a default field object
     *
     * @return bool
     */
    function define_default_field() {
        global $OUTPUT;
        if (empty($this->baseline->id)) {
            echo $OUTPUT->notification('Programmer error: baselineid not defined in field class');
        }
        $this->field = new stdClass();
        $this->field->id = 0;
        $this->field->baselineid = $this->baseline->id;
        $this->field->type   = $this->type;
        $this->field->param1 = '';
        $this->field->param2 = '';
        $this->field->param3 = '';
        $this->field->name = '';
        $this->field->description = '';
        $this->field->base = false;
        $this->field->myrecords = 'baseline_records';
        $this->field->mycontent = 'baseline_content';

        return true;
    }

    /**
     * Set up the field object according to baseline in an object.  Now is the time to clean it!
     *
     * @return bool
     */
    function define_field($baseline) {
        $this->field->type        = $this->type;
        $this->field->baselineid      = $this->baseline->id;

        $this->field->name        = trim($baseline->name);
        $this->field->description = trim($baseline->description);
        $this->field->base = false;
        $this->field->myrecords = 'baseline_records';
        $this->field->mycontent = 'baseline_content';

        if (isset($baseline->param1)) {
            $this->field->param1 = trim($baseline->param1);
        }
        if (isset($baseline->param2)) {
            $this->field->param2 = trim($baseline->param2);
        }
        if (isset($baseline->param3)) {
            $this->field->param3 = trim($baseline->param3);
        }
        if (isset($baseline->param4)) {
            $this->field->param4 = trim($baseline->param4);
        }
        if (isset($baseline->param5)) {
            $this->field->param5 = trim($baseline->param5);
        }

        return true;
    }
    // baselines or entry set field to reflect filename
    function which_set($base=false) {
               if (empty($this->field)) {
            notify('Programmer error: Field has not been defined yet!  See define_field()');
            return false;
        }
        if ($base) {
        $this->field->base = $base;
        $this->field->myrecords = 'baseline_base_records';
        $this->field->mycontent = 'baseline_base_content';
        } else {
        $this->field->base = false;
        $this->field->myrecords = 'baseline_records';
        $this->field->mycontent = 'baseline_content';
        }
       }



    /**
     * Insert a new field in the baseline
     * We assume the field object is already defined as $this->field
     *
     * @global object
     * @return bool
     */
    function insert_field() {
        global $DB, $OUTPUT;

        if (empty($this->field)) {
            echo $OUTPUT->notification('Programmer error: Field has not been defined yet!  See define_field()');
            return false;
        }

        $this->field->id = $DB->insert_record('baseline_fields',$this->field);
         //  FPS added picture handler here*
        $filename =  $this->field->name.'1.gif';
        if (! file_exists($filename))  {
        $i=0;
        foreach (explode("\n",$this->field->param1) as $myval) {
           $myvalue = trim($myval);
            $i++;
            $file='cf/'.$i.'.gif';
            $newfile='cf/'.$this->field->name.$i.'.gif';
 	    if (! file_exists($newfile))  {
             echo "copy $file to $newfile for $myval";
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
                }
        }
        else echo "File exists";
        $i++;
        }
        }

         // end of  FPS added picture handler here*


        return true;
    }


    /**
     * Update a field in the baseline
     *
     * @global object
     * @return bool
     */
    function update_field() {
        global $DB;

        $DB->update_record('baseline_fields', $this->field);
        return true;
    }

    /**
     * Delete a field completely
     *
     * @global object
     * @return bool
     */
    function delete_field() {
        global $DB;

        if (!empty($this->field->id)) {
            $this->delete_content();
            $DB->delete_records('baseline_fields', array('id'=>$this->field->id));
        }
        return true;
    }
//  Display icons for field;

function display_icns() {
        global $DB,$CFG;
     $str ="</td> </tr><tr><td>";
 $i=0;
   $bi='';
   $bblock='';
//  Display icns for field;
 if (($this->field->type =='pbutton') ||  ($this->field->type =='slider')) {
      $ria =  array_pop(explode(',',$this->field->myrecs) );
      $rb =  array_pop(explode(',',$this->field->basemyrecs) );
        foreach (explode("\n",$this->field->param1) as $myval) {
         $myvalue = trim($myval);
          $i++;
        if( $ria == $myvalue ) {$bi= $i; }
        if( $rb == $myvalue ) {$bblock= $i; }
         }
      // echo "<br>'$rb'&nbsp;'$ria'&nbsp;'$myvalue' &nbsp; '$bblock'&nbsp;'$bi'<br>" ;
     if( $bi) {
     $str .= '<img src="cf/'.$this->field->name.$bi.'.gif" width="70" height="90" alt="'.$bi.'">Previous diary<br>';
       }
   //  if( $bblock) {
    // $str .= ' <img src="'.$CFG->httpswwwroot.'cf/'.$this->field->name.$bblock.'.gif" width="70" height="90" alt="'.$bblock.'">Base<br>';
     //  }
         // $str .= ' </td>';
   return $str;
}
}


//single icon
function display_icn() {
        global $DB,$CFG;
     // $str ="</td> <td>";
      $str ="</td> </tr><td>";
 $i=0;
   $bi='';
   $bblock='';
//  Display icns for field;
 if (($this->field->type =='pbutton') ||  ($this->field->type =='slider')) {
// FPS fix emty myrecs
      $ria='';
      $rb='';
      if (isset($this->field->myrecs))  $ria =  array_pop(explode(',',$this->field->myrecs) );
      $rb =  array_pop(explode(',',$this->field->basemyrecs) );
        foreach (explode("\n",$this->field->param1) as $myval) {
         $myvalue = trim($myval);
          $i++;
        if( $ria == $myvalue ) {$bi= $i; }
        if( $rb == $myvalue ) {$bblock= $i; }
         }
      // echo "<br>'$rb'&nbsp;'$ria'&nbsp;'$myvalue' &nbsp; '$bblock'&nbsp;'$bi'<br>" ;
     if( $bi) {
     //$str .= '<img src="cf/'.$this->field->name.$bi.'.gif" width="70" height="90" alt="'.$bi.'">Previous diary<br>';
     $str .= '<img src="cf/'.$this->field->name.$bi.'.gif" width="70" height="90" alt="'.$bi.'">Previous diary';
       }
     if( $bblock) {
     $str .= ' <img src="cf/'.$this->field->name.$bblock.'.gif" width="70" height="90" alt="'.$bblock.'">Base<br>';
       }
          $str .= ' </td>';
// FPS Here August 2012 
   return $str;
}
}

function get_bases() {
// Need to know the field id and userid
        global $DB,$CFG;
        $this->field->days = '0';
        $this->field->myrecs = '0';
        $this->field->basedays = '';
        $this->field->basemyrecs = '';
        $this->field->scaleday = 0;
        $this->field->scalebase = 0;
	//FPS
 	$recordssql = "select content,timecreated from (SELECT  c.content, r.timecreated FROM {baseline_records} r, {baseline_content} c WHERE r.userid = '{$CFG->user}' and c.fieldid = '".$this->field->id. "' and r.id =c.recordid ORDER BY timecreated  DESC ) as a  ORDER BY timecreated limit 10 ";
 //echo $recordssql;
        $mydays = 0;
        if ($rs = $DB->get_recordset_sql($recordssql)) {
           foreach ($rs as $record) {
               // number of days in the first record.
                $myweek=floor($record->timecreated/86400) - $mydays;
               // number of days in the first record.
	      if( $mydays == 0) { 
                    $mydays = $myweek;
                    $this->field->days .= '0,';
               	$this->field->myrecs .= $record->content.','; 
                } else {
               $this->field->days .= $myweek.',';
               if ( $this->field->scaleday <  $myweek ) { $this->field->scaleday =  $myweek; }
               }
                // $str .= $record->content.','.$record->timecreated;
               $this->field->myrecs .= $record->content.','; 
		 }
        }
 //can we get the previous one to the first date above.
        $recordssql = "select content,timecreated from ( SELECT  c.content, r.timecreated FROM {baseline_base_records} r, {baseline_base_content} c WHERE r.userid = '{$CFG->user}' and c.fieldid = '".$this->field->id. "' and r.id =c.recordid ORDER BY timecreated ) as a  limit 10 ";
 //echo $recordssql;
       $mybaserec=''; 
       $myweek=0;
        if ($rs = $DB->get_recordset_sql($recordssql)) {
           foreach ($rs as $record) {
                $mybaserec=$record->content;
              if ( $record->content != '' ) {
                $myweek=floor($record->timecreated/86400) - $mydays;
                if ($myweek <= 0 ){
               $this->field->basedays .=  '0,' .$myweek. ','	;
               $this->field->basemyrecs .= $record->content.',';
               } else {
               $this->field->basedays .= $myweek.',';
               if ( $this->field->scaleday <  $myweek ) { $this->field->scaleday =  $myweek; }
                 }
               $this->field->basemyrecs .= $record->content.',';

        } //first
        } // not null
        } //while record
        $this->field->basemyrecs .= $mybaserec;
        $this->field->basedays .= $myweek;
       if ( $this->field->scaleday == 0 ) {$this->field->scaleday =  1;}
         $this->field->days=substr($this->field->days,0,-1);
         $this->field->myrecs= substr($this->field->myrecs,0,-1);
        // $this->field->basedays=substr($this->field->basedays,0,-1);
        // $this->field->basemyrecs=  substr($this->field->basemyrecs,0,-1);
      // print_r($this->field);
}


    /**
     * Print the relevant form element in the ADD template for this field
     *
     * @global object
     * @param int $recordid
     * @return string
     */
    function display_add_field($recordid=0){
        global $DB;

        if ($recordid){
            $content = $DB->get_field($this->field->mycontent, 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid));
        } else {
            $content = '';
        }

        // beware get_field returns false for new, empty records MDL-18567
        if ($content===false) {
            $content='';
        }

        $str = '<div title="'.s($this->field->description).'">';
        $str .= '<input style="width:300px;" type="text" name="field_'.$this->field->id.'" id="field_'.$this->field->id.'" value="'.s($content).'" />';
        $str .= '</div>';

        return $str;
    }

    /**
     * Print the relevant form element to define the attributes for this field
     * viewable by teachers only.
     *
     * @global object
     * @global object
     * @return void Output is echo'd
     */
    function display_edit_field() {
        global $CFG, $DB, $OUTPUT;
        if (empty($this->field)) {   // No field has been defined yet, try and make one
            $this->define_default_field();
        }

        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

        echo '<form id="editfield" action="'.$CFG->wwwroot.'/mod/baseline/field.php" method="post">'."\n";
        echo '<input type="hidden" name="d" value="'.$this->baseline->id.'" />'."\n";
        if (empty($this->field->id)) {
            echo '<input type="hidden" name="mode" value="add" />'."\n";
            $savebutton = get_string('add');
        } else {
            echo '<input type="hidden" name="fid" value="'.$this->field->id.'" />'."\n";
            echo '<input type="hidden" name="mode" value="update" />'."\n";
            $savebutton = get_string('savechanges');
        }
        echo '<input type="hidden" name="type" value="'.$this->type.'" />'."\n";
        echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />'."\n";

        echo $OUTPUT->heading($this->name());

        require_once($CFG->dirroot.'/mod/baseline/field/'.$this->type.'/mod.html');

        echo '<div class="mdl-align">';
        echo '<input type="submit" value="'.$savebutton.'" />'."\n";
        echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />'."\n";
        echo '</div>';

        echo '</form>';

        echo $OUTPUT->box_end();
    }

    /**
     * Display the content of the field in browse mode
     *
     * @global object
     * @param int $recordid
     * @param object $template
     * @return bool|string
     */
    function display_browse_field($recordid, $template,$mycontent) {
        global $DB;

        if ($content = $DB->get_record($mycontent, array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            if (isset($content->content)) {
                $options = new stdClass();
                if ($this->field->param1 == '1') {  // We are autolinking this field, so disable linking within us
                    //$content->content = '<span class="nolink">'.$content->content.'</span>';
                    //$content->content1 = FORMAT_HTML;
                    $options->filter=false;
                }
                $options->para = false;
//FPS Here to add images to view.php
// Lookup the value for the line no. in param1 fps
                 $i = 0;
        $myValue = $i;
        foreach (explode("\n",$this->field->param1) as $pbutton) {
            $pbutton = trim($pbutton);
            if ($pbutton === '') {
                continue; // skip empty lines
            }
            if ($content->content == $pbutton ) { 
                        $myValue = $i;
                }
            $i++ ;
            }
        $k=0;
 foreach (explode("\n",$this->field->param3) as $pbutton) {
       if ( $myValue == $k) {$answer = $pbutton; }
        $k++;
        }
         $answer2 =  explode("\xE2",$answer);
          if (array_key_exists(2,$answer2) ) {

         $answer3 =  substr($answer2[2],2,strlen($answer2[2])-3);
          $myText =trim($answer2[0].': '.$answer3);
	} else
           $myText = trim($answer);

        //  this needs coresponding colour from order listx in field, cant find actual if not recorded....

                 $str = '<img src="cf/'.$this->field->name.$myValue.'.gif" width="70" height="90" alt = "'.$content->content.$myText.'" '.' class="uihint" );>';
                //$str = format_text($content->content, $content->content1, $options);
            } else {
                $str = '';
            }
            return $str;
        }
        return false;
    }

    /**
     * Update the content of one baseline field in the baseline_content table
     * @global object
     * @param int $recordid
     * @param mixed $value
     * @param string $name
     * @return bool
     */
    function update_content($recordid, $value, $name=''){
        global $DB;

        $content = new stdClass();
        $content->fieldid = $this->field->id;
        $mycontent=$this->field->mycontent;
        $content->recordid = $recordid;
        $content->content = clean_param($value, PARAM_NOTAGS);
        if ($oldcontent = $DB->get_record( $mycontent, array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $content->id = $oldcontent->id;
            return $DB->update_record( $mycontent, $content);
        } else {
            return $DB->insert_record( $mycontent, $content);
        }
    }

    /**
     * Delete all content associated with the field
     *
     * @global object
     * @param int $recordid
     * @return bool
     */
    function delete_content($recordid=0) {
        global $DB;

        if ($recordid) {
            $conditions = array('fieldid'=>$this->field->id, 'recordid'=>$recordid);
        } else {
            $conditions = array('fieldid'=>$this->field->id);
        }

        $rs = $DB->get_recordset('baseline_content', $conditions);
        if ($rs->valid()) {
            $fs = get_file_storage();
            foreach ($rs as $content) {
                $fs->delete_area_files($this->context->id, 'mod_baseline', 'content', $content->id);
            }
        }
        $rs->close();

        return $DB->delete_records('baseline_content', $conditions);
        //Need to vcontinue and delete baselines here
    }

    /**
     * Check if a field from an add form is empty
     *
     * @param mixed $value
     * @param mixed $name
     * @return bool
     */
    function notemptyfield($value, $name) {
        return !empty($value);
    }

    /**
     * Just in case a field needs to print something before the whole form
     */
    function print_before_form() {
    }

    /**
     * Just in case a field needs to print something after the whole form
     */
    function print_after_form() {
    }


    /**
     * Returns the sortable field for the content. By default, it's just content
     * but for some plugins, it could be content 1 - content4
     *
     * @return string
     */
    function get_sort_field() {
        return 'content';
    }

    /**
     * Returns the SQL needed to refer to the column.  Some fields may need to CAST() etc.
     *
     * @param string $fieldname
     * @return string $fieldname
     */
    function get_sort_sql($fieldname) {
        return $fieldname;
    }

    /**
     * Returns the name/type of the field
     *
     * @return string
     */
    function name() {
        return get_string('name'.$this->type, 'baseline');
    }

    /**
     * Prints the respective type icon
     *
     * @global object
     * @return string
     */
    function image() {
        global $OUTPUT;

        $params = array('d'=>$this->baseline->id, 'fid'=>$this->field->id, 'mode'=>'display', 'sesskey'=>sesskey());
        $link = new moodle_url('/mod/baseline/field.php', $params);
        $str = '<a href="'.$link->out().'">';
        $str .= '<img src="'.$OUTPUT->pix_url('field/'.$this->type, 'baseline') . '" ';
        $str .= 'height="'.$this->iconheight.'" width="'.$this->iconwidth.'" alt="'.$this->type.'" title="'.$this->type.'" /></a>';
        return $str;
    }

    /**
     * Per default, it is assumed that fields support text exporting.
     * Override this (return false) on fields not supporting text exporting.
     *
     * @return bool true
     */
    function text_export_supported() {
        return true;
    }

    /**
     * Per default, return the record's text value only from the "content" field.
     * Override this in fields class if necesarry.
     *
     * @param string $record
     * @return string
     */
    function export_text_value($record) {
        if ($this->text_export_supported()) {
            return $record->content;
        }
    }

    /**
     * @param string $relativepath
     * @return bool false
     */
    function file_ok($relativepath) {
        return false;
    }
}


/**
 * Given a template and a baselineid, generate a default case template
 *
 * @global object
 * @param object $baseline
 * @param string template [addtemplate, singletemplate, listtempalte, rsstemplate]
 * @param int $recordid
 * @param bool $form
 * @param bool $update
 * @return bool|string
 */
function baseline_generate_default_template(&$baseline, $template, $recordid=0, $form=false, $update=true) {
    global $DB;

    if (!$baseline && !$template) {
        return false;
    }
    if ($template == 'csstemplate' or $template == 'jstemplate' ) {
        return '';
    }

    // get all the fields for that baseline
    if ($fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id), 'id')) {

        $table = new html_table();
        $table->attributes['class'] = 'mod-baseline-default-template';
        $table->colclasses = array('template-field', 'template-token');
        $table->data = array();
        foreach ($fields as $field) {
            if ($form) {   // Print forms instead of baseline
                $fieldobj = baseline_get_field($field, $baseline);
                $token = $fieldobj->display_add_field($recordid);
            } else {           // Just print the tag
                $token = '[['.$field->name.']]';
            }
            $table->data[] = array(
                $field->name.': ',
                $token
            );
        }
        if ($template == 'listtemplate') {
            $cell = new html_table_cell('##edit##  ##more##  ##delete##  ##approve##  ##export##');
            $cell->colspan = 2;
            $cell->attributes['class'] = 'controls';
            $table->data[] = new html_table_row(array($cell));
        } else if ($template == 'singletemplate') {
            $cell = new html_table_cell('##edit##  ##delete##  ##approve##  ##export##');
            $cell->colspan = 2;
            $cell->attributes['class'] = 'controls';
            $table->data[] = new html_table_row(array($cell));
        } else if ($template == 'asearchtemplate') {
            $row = new html_table_row(array(get_string('authorfirstname', 'baseline').': ', '##firstname##'));
            $row->attributes['class'] = 'searchcontrols';
            $table->data[] = $row;
            $row = new html_table_row(array(get_string('authorlastname', 'baseline').': ', '##lastname##'));
            $row->attributes['class'] = 'searchcontrols';
            $table->data[] = $row;
        }

        $str  = html_writer::start_tag('div', array('class' => 'defaulttemplate'));
        $str .= html_writer::table($table);
        $str .= html_writer::end_tag('div');
        if ($template == 'listtemplate'){
            $str .= html_writer::empty_tag('hr');
        }

        if ($update) {
            $newbaseline = new stdClass();
            $newbaseline->id = $baseline->id;
            $newbaseline->{$template} = $str;
            $DB->update_record('baseline', $newbaseline);
            $baseline->{$template} = $str;
        }

        return $str;
    }
}


/**
 * Search for a field name and replaces it with another one in all the
 * form templates. Set $newfieldname as '' if you want to delete the
 * field from the form.
 *
 * @global object
 * @param object $baseline
 * @param string $searchfieldname
 * @param string $newfieldname
 * @return bool
 */
function baseline_replace_field_in_templates($baseline, $searchfieldname, $newfieldname) {
    global $DB;

    if (!empty($newfieldname)) {
        $prestring = '[[';
        $poststring = ']]';
        $idpart = '#id';

    } else {
        $prestring = '';
        $poststring = '';
        $idpart = '';
    }

    $newbaseline = new stdClass();
    $newbaseline->id = $baseline->id;
    $newbaseline->singletemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $baseline->singletemplate);

    $newbaseline->listtemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $baseline->listtemplate);

    $newbaseline->addtemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $baseline->addtemplate);

    $newbaseline->addtemplate = str_ireplace('[['.$searchfieldname.'#id]]',
            $prestring.$newfieldname.$idpart.$poststring, $baseline->addtemplate);

    $newbaseline->rsstemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $baseline->rsstemplate);

    return $DB->update_record('baseline', $newbaseline);
}


/**
 * Appends a new field at the end of the form template.
 *
 * @global object
 * @param object $baseline
 * @param string $newfieldname
 */
function baseline_append_new_field_to_templates($baseline, $newfieldname) {
    global $DB;

    $newbaseline = new stdClass();
    $newbaseline->id = $baseline->id;
    $change = false;

    if (!empty($baseline->singletemplate)) {
        $newbaseline->singletemplate = $baseline->singletemplate.' [[' . $newfieldname .']]';
        $change = true;
    }
    if (!empty($baseline->addtemplate)) {
        $newbaseline->addtemplate = $baseline->addtemplate.' [[' . $newfieldname . ']]';
        $change = true;
    }
    if (!empty($baseline->rsstemplate)) {
        $newbaseline->rsstemplate = $baseline->singletemplate.' [[' . $newfieldname . ']]';
        $change = true;
    }
    if ($change) {
        $DB->update_record('baseline', $newbaseline);
    }
}


/**
 * given a field name
 * this function creates an instance of the particular subfield class
 *
 * @global object
 * @param string $name
 * @param object $baseline
 * @return object|bool
 */
function baseline_get_field_from_name($name, $baseline){
    global $DB;

    $field = $DB->get_record('baseline_fields', array('name'=>$name, 'baselineid'=>$baseline->id));

    if ($field) {
        return baseline_get_field($field, $baseline);
    } else {
        return false;
    }
}

/**
 * given a field id
 * this function creates an instance of the particular subfield class
 *
 * @global object
 * @param int $fieldid
 * @param object $baseline
 * @return bool|object
 */
function baseline_get_field_from_id($fieldid, $baseline){
    global $DB;

    $field = $DB->get_record('baseline_fields', array('id'=>$fieldid, 'baselineid'=>$baseline->id));

    if ($field) {
        return baseline_get_field($field, $baseline);
    } else {
        return false;
    }
}

/**
 * given a field id
 * this function creates an instance of the particular subfield class
 *
 * @global object
 * @param string $type
 * @param object $baseline
 * @return object
 */
function baseline_get_field_new($type, $baseline) {
    global $CFG;

    require_once($CFG->dirroot.'/mod/baseline/field/'.$type.'/field.class.php');
    $newfield = 'baseline_field_'.$type;
    $newfield = new $newfield(0, $baseline);
    return $newfield;
}

/**
 * returns a subclass field object given a record of the field, used to
 * invoke plugin methods
 * input: $param $field - record from db
 *
 * @global object
 * @param object $field
 * @param object $baseline
 * @param object $cm
 * @return object
 */
function baseline_get_field($field, $baseline, $cm=null) {
    global $CFG;

    if ($field) {
        require_once('field/'.$field->type.'/field.class.php');
        $newfield = 'baseline_field_'.$field->type;
        $newfield = new $newfield($field, $baseline, $cm);
        return $newfield;
    }
}


/**
 * Given record object (or id), returns true if the record belongs to the current user
 *
 * @global object
 * @global object
 * @param mixed $record record object or id
 * @return bool
 */
function baseline_isowner($record) {
    global $USER, $DB;

    if (!isloggedin()) { // perf shortcut
        return false;
    }

    if (!is_object($record)) {
        if (!$record = $DB->get_record('baseline_records', array('id'=>$record))) {
            return false;
        }
    }

    return ($record->userid == $USER->id);
}

/**
 * has a user reached the max number of entries?
 *
 * @param object $baseline
 * @return bool
 */
function baseline_atmaxentries($baseline){
    if (!$baseline->maxentries){
        return false;

    } else {
        return (baseline_numentries($baseline) >= $baseline->maxentries);
    }
}

/**
 * returns the number of entries already made by this user
 *
 * @global object
 * @global object
 * @param object $baseline
 * @return int
 */
function baseline_numentries($baseline){
    global $USER, $DB,$CFG;
 if ($CFG->base) {
    $sql = 'SELECT COUNT(*) FROM {baseline_base_records} WHERE baselineid=? AND userid=?';
    } else {
    $sql = 'SELECT COUNT(*) FROM {baseline_records} WHERE baselineid=? AND userid=?';
    }
    return $DB->count_records_sql($sql, array($baseline->id, $USER->id));
}

/**
 * function that takes in a baselineid and adds a record
 * this is used everytime an add template is submitted
 *
 * @global object
 * @global object
 * @param object $baseline
 * @param int $groupid
 * @return bool
 */
function baseline_add_record($baseline, $groupid=0){
    global $USER, $DB,$CFG;

    $cm = get_coursemodule_from_instance('baseline', $baseline->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->baselineid = $baseline->id;
    $record->groupid = $groupid;
    $record->timecreated = $record->timemodified = time();
    if (has_capability('mod/baseline:approve', $context)) {
        $record->approved = 1;
    } else {
        $record->approved = 0;
    }
    if ($CFG->base) {
    return $DB->insert_record('baseline_base_records',$record);
    }  else {
    return $DB->insert_record('baseline_records', $record);
    }
}

/**
 * check the multple existence any tag in a template
 *
 * check to see if there are 2 or more of the same tag being used.
 *
 * @global object
 * @param int $baselineid,
 * @param string $template
 * @return bool
 */
function baseline_tags_check($baselineid, $template) {
    global $DB, $OUTPUT;

    // first get all the possible tags
    $fields = $DB->get_records('baseline_fields', array('baselineid'=>$baselineid));
    // then we generate strings to replace
    $tagsok = true; // let's be optimistic
    foreach ($fields as $field){
        $pattern="/\[\[".$field->name."\]\]/i";
        if (preg_match_all($pattern, $template, $dummy)>1){
            $tagsok = false;
            echo $OUTPUT->notification('[['.$field->name.']] - '.get_string('multipletags','baseline'));
        }
    }
    // else return true
    return $tagsok;
}

/**
 * Adds an instance of a baseline
 *
 * @global object
 * @param object $baseline
 * @return $int
 */
function baseline_add_instance($baseline) {
    global $DB;

    if (empty($baseline->assessed)) {
        $baseline->assessed = 0;
    }

    $baseline->timemodified = time();

    $baseline->id = $DB->insert_record('baseline', $baseline);

    baseline_grade_item_update($baseline);

    return $baseline->id;
}

/**
 * updates an instance of a baseline
 *
 * @global object
 * @param object $baseline
 * @return bool
 */
function baseline_update_instance($baseline) {
    global $DB, $OUTPUT;

    $baseline->timemodified = time();
    $baseline->id           = $baseline->instance;

    if (empty($baseline->assessed)) {
        $baseline->assessed = 0;
    }

    if (empty($baseline->ratingtime) or empty($baseline->assessed)) {
        $baseline->assesstimestart  = 0;
        $baseline->assesstimefinish = 0;
    }

    if (empty($baseline->notification)) {
        $baseline->notification = 0;
    }

    $DB->update_record('baseline', $baseline);

    baseline_grade_item_update($baseline);

    return true;

}

/**
 * deletes an instance of a baseline
 *
 * @global object
 * @param int $id
 * @return bool
 */
function baseline_delete_instance($id) {    // takes the baselineid
    global $DB, $CFG;

    if (!$baseline = $DB->get_record('baseline', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('baseline', $baseline->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Delete all the associated information

    // files
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_baseline');

    // get all the records in this baseline
    $sql = "SELECT r.id
              FROM {baseline_records} r
             WHERE r.baselineid = ?";

    $DB->delete_records_select('baseline_content', "recordid IN ($sql)", array($id));

    // delete all the records 
    $DB->delete_records('baseline_records', array('baselineid'=>$id));
    // get all the records in this baselines base
    $sql = "SELECT r.id
              FROM {baseline_base_records} r
             WHERE r.baselineid = ?";

    $DB->delete_records_select('baseline_base_content', "recordid IN ($sql)", array($id));

    // delete all the base records and fields
    $DB->delete_records('baseline_base_records', array('baselineid'=>$id));
    $DB->delete_records('baseline_fields', array('baselineid'=>$id));

    // Delete the instance itself
    $result = $DB->delete_records('baseline', array('id'=>$id));

    // cleanup gradebook
    baseline_grade_item_delete($baseline);

    return $result;
}

/**
 * returns a summary of baseline activity of this user
 *
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $baseline
 * @return object|null
 */
function baseline_user_outline($course, $user, $mod, $baseline) {
    global $DB, $CFG;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'baseline', $baseline->id, $user->id);
    if (empty($grades->items[0]->grades)) {
        $grade = false;
    } else {
        $grade = reset($grades->items[0]->grades);
    }


    if ($countrecords = $DB->count_records('baseline_records', array('baselineid'=>$baseline->id, 'userid'=>$user->id))) {
        $result = new stdClass();
        $result->info = get_string('numrecords', 'baseline', $countrecords);
        $lastrecord   = $DB->get_record_sql('SELECT id,timemodified FROM {baseline_records}
                                              WHERE baselineid = ? AND userid = ?
                                           ORDER BY timemodified DESC', array($baseline->id, $user->id), true);
        $result->time = $lastrecord->timemodified;
        if ($grade) {
            $result->info .= ', ' . get_string('grade') . ': ' . $grade->str_long_grade;
        }
        return $result;
    } else if ($grade) {
        $result = new stdClass();
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;

        //datesubmitted == time created. dategraded == time modified or time overridden
        //if grade was last modified by the user themselves use date graded. Otherwise use date submitted
        //TODO: move this copied & pasted code somewhere in the grades API. See MDL-26704
        if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }

        return $result;
    }
    return NULL;
}

/**
 * Prints all the records uploaded by this user
 *
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $baseline
 */
function baseline_user_complete($course, $user, $mod, $baseline) {
    global $DB, $CFG, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'baseline', $baseline->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($records = $DB->get_records('baseline_records', array('baselineid'=>$baseline->id,'userid'=>$user->id), 'timemodified DESC')) {
        baseline_print_template('singletemplate', $records, $baseline);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @global object
 * @param object $baseline
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function baseline_get_user_grades($baseline, $userid=0) {
    global $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ratingoptions = new stdClass;
    $ratingoptions->component = 'mod_baseline';
    $ratingoptions->ratingarea = 'entry';
    $ratingoptions->modulename = 'baseline';
    $ratingoptions->moduleid   = $baseline->id;

    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $baseline->assessed;
    $ratingoptions->scaleid = $baseline->scale;
    $ratingoptions->itemtable = 'baseline_records';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();
    return $rm->get_user_grades($ratingoptions);
}

/**
 * Update activity grades
 *
 * @global object
 * @global object
 * @param object $baseline
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function baseline_update_grades($baseline, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!$baseline->assessed) {
        baseline_grade_item_update($baseline);

    } else if ($grades = baseline_get_user_grades($baseline, $userid)) {
        baseline_grade_item_update($baseline, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        baseline_grade_item_update($baseline, $grade);

    } else {
        baseline_grade_item_update($baseline);
    }
}

/**
 * Update all grades in gradebook.
 *
 * @global object
 */
function baseline_upgrade_grades() {
    global $DB;

    $sql = "SELECT COUNT('x')
              FROM {baseline} d, {course_modules} cm, {modules} m
             WHERE m.name='baseline' AND m.id=cm.module AND cm.instance=d.id";
    $count = $DB->count_records_sql($sql);

    $sql = "SELECT d.*, cm.idnumber AS cmidnumber, d.course AS courseid
              FROM {baseline} d, {course_modules} cm, {modules} m
             WHERE m.name='baseline' AND m.id=cm.module AND cm.instance=d.id";
    $rs = $DB->get_recordset_sql($sql);
    if ($rs->valid()) {
        // too much debug output
        $pbar = new progress_bar('baselineupgradegrades', 500, true);
        $i=0;
        foreach ($rs as $baseline) {
            $i++;
            upgrade_set_timeout(60*5); // set up timeout, may also abort execution
            baseline_update_grades($baseline, 0, false);
            $pbar->update($i, $count, "Updating Baselinebase grades ($i/$count).");
        }
    }
    $rs->close();
}

/**
 * Update/create grade item for given baseline
 *
 * @global object
 * @param object $baseline object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function baseline_grade_item_update($baseline, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname'=>$baseline->name, 'idnumber'=>$baseline->cmidnumber);

    if (!$baseline->assessed or $baseline->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;

    } else if ($baseline->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $baseline->scale;
        $params['grademin']  = 0;

    } else if ($baseline->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$baseline->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/baseline', $baseline->course, 'mod', 'baseline', $baseline->id, 0, $grades, $params);
}

/**
 * Delete grade item for given baseline
 *
 * @global object
 * @param object $baseline object
 * @return object grade_item
 */
function baseline_grade_item_delete($baseline) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/baseline', $baseline->course, 'mod', 'baseline', $baseline->id, 0, NULL, array('deleted'=>1));
}

/**
 * returns a list of participants of this baseline
 *
 * Returns the users with baseline in one baseline
 * (users with records in baseline_records, baseline_comments and ratings)
 *
 * @todo: deprecated - to be deleted in 2.2
 *
 * @param int $baselineid
 * @return array
 */
function baseline_get_participants($baselineid) {
    global $DB;

    $params = array('baselineid' => $baselineid);

    $sql = "SELECT DISTINCT u.id, u.id
              FROM {user} u,
                   {baseline_records} r
             WHERE r.baselineid = :baselineid AND
                   u.id = r.userid";
    $records = $DB->get_records_sql($sql, $params);

    $sql = "SELECT DISTINCT u.id, u.id
              FROM {user} u,
                   {baseline_records} r,
                   {comments} c
             WHERE r.baselineid = ? AND
                   u.id = r.userid AND
                   r.id = c.itemid AND
                   c.commentarea = 'baseline_entry'";
    $comments = $DB->get_records_sql($sql, $params);

    $sql = "SELECT DISTINCT u.id, u.id
              FROM {user} u,
                   {baseline_records} r,
                   {ratings} a
             WHERE r.baselineid = ? AND
                   u.id = r.userid AND
                   r.id = a.itemid AND
                   a.component = 'mod_baseline' AND
                   a.ratingarea = 'entry'";
    $ratings = $DB->get_records_sql($sql, $params);

    $participants = array();

    if ($records) {
        foreach ($records as $record) {
            $participants[$record->id] = $record;
        }
    }
    if ($comments) {
        foreach ($comments as $comment) {
            $participants[$comment->id] = $comment;
        }
    }
    if ($ratings) {
        foreach ($ratings as $rating) {
            $participants[$rating->id] = $rating;
        }
    }

    return $participants;
}

// junk functions
/**
 * takes a list of records, the current baseline, a search string,
 * and mode to display prints the translated template
 *
 * @global object
 * @global object
 * @param string $template
 * @param array $records
 * @param object $baseline
 * @param string $search
 * @param int $page
 * @param bool $return
 * @return mixed
 */
function baseline_print_template($template, $records, $baseline, $search='', $page=0, $return=false) {
    global $CFG, $DB, $OUTPUT;
    $cm = get_coursemodule_from_instance('baseline', $baseline->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    static $fields = NULL;
    static $isteacher;
    static $baselineid = NULL;

    if (empty($baselineid)) {
        $baselineid = $baseline->id;
    } else if ($baselineid != $baseline->id) {
        $fields = NULL;
    }
     $base = $CFG->base;
     $summary = $CFG->summary;
          if ($base) {
                    $my_base_file = 'baseline_base_content';
                    $my_record_table = 'baseline_base_records';
            } else  {
                    $my_base_file = 'baseline_content' ;
                    $my_record_table = 'baseline_records';
            }


    if (empty($fields)) {
        $fieldrecords = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id));
        foreach ($fieldrecords as $fieldrecord) {
            $fields[]= baseline_get_field($fieldrecord, $baseline);
        }
        $isteacher = has_capability('mod/baseline:managetemplates', $context);
    }

    if (empty($records)) {
        return;
    }

    foreach ($records as $record) {   // Might be just one for the single template

    // Replacing tags
        $patterns = array();
        $replacement = array();

    // Then we generate strings to replace for normal tags
        foreach ($fields as $field) {
		     $field->base = $base;
                     $field->mycontent = $my_base_file;
                     $field->myrecords = $my_record_table ;
            $patterns[]='[['.$field->field->name.']]';
               if (!$summary) {
             $replacement[] = highlight($search, $field->display_browse_field($record->id, $template, $my_base_file));
        } else {
                $replacement[] =table_graph($search, $field->display_browse_field($record->id, $template, $my_base_file));
        } 
         //   $replacement[] = highlight($search, $field->display_browse_field($record->id, $template));
        }

    // Replacing special tags (##Edit##, ##Delete##, ##More##)
        $patterns[]='##edit##';
        $patterns[]='##delete##';
        if (has_capability('mod/baseline:manageentries', $context) or baseline_isowner($record->id)) {
     if ($base) $whPHP= 'bedit.php?d='; else $whPHP= 'edit.php?d=';
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/baseline/'. $whPHP
                             .$baseline->id.'&amp;rid='.$record->id.'&amp;base='.$base.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('/t/edit').'" class="iconsmall" alt="'.get_string('edit').'" title="'.get_string('edit').'" /></a>';
         if (!$summary) {
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/baseline/view.php?d='
                             .$baseline->id.'&amp;delete='.$record->id.'&amp;base='.$base.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('/t/delete'). '" class="iconsmall" alt="'.get_string('delete').'" title="'.get_string('delete').'" /></a>';
            } else {
                $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/baseline/summary.php?d='
                             .$baseline->id.'&amp;delete='.$record->id.'&amp;base='.$base.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('/t/delete'). '" class="iconsmall" alt="'.get_string('delete').'" title="'.get_string('delete').'" /></a>';
        } // sumary
        } else {
            $replacement[] = '';
            $replacement[] = '';
        }

        $moreurl = $CFG->wwwroot . '/mod/baseline/view.php?d=' . $baseline->id . '&amp;rid=' . $record->id;
 if (!$summary) {
        $moreurl = $CFG->wwwroot . '/mod/baseline/view.php?d=' . $baseline->id . '&amp;rid=' . $record->id.'&amp;base='.$base;
            } else {
        $moreurl = $CFG->wwwroot . '/mod/baseline/summary.php?d=' . $baseline->id . '&amp;rid=' . $record->id.'&amp;base='.$base;
        } // sumary

        if ($search) {
            $moreurl .= '&amp;filter=1';
        }
        $patterns[]='##more##';
        $replacement[] = '<a href="' . $moreurl . '"><img src="' . $OUTPUT->pix_url('i/search') . '" class="iconsmall" alt="' . get_string('more', 'baseline') . '" title="' . get_string('more', 'baseline') . '" /></a>';

        $patterns[]='##moreurl##';
        $replacement[] = $moreurl;

        $patterns[]='##user##';
         if (!$summary) {
                 $replacement[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$record->userid.
                               '&amp;course='.$baseline->course.'">'.fullname($record).'&amp;base='.$base.'</a>';
        } else {
                 $replacement[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$record->userid.
                               '&amp;course='.$baseline->course.'">'.fullname($record).'&amp;base='.$base.'</a>';
       } // sumary
        $patterns[]='##export##';

        if (!empty($CFG->enableportfolios) && ($template == 'singletemplate' || $template == 'listtemplate')
            && ((has_capability('mod/baseline:exportentry', $context)
                || (baseline_isowner($record->id) && has_capability('mod/baseline:exportownentry', $context))))) {
            require_once($CFG->libdir . '/portfoliolib.php');
            $button = new portfolio_add_button();
            $button->set_callback_options('baseline_portfolio_caller', array('id' => $cm->id, 'recordid' => $record->id), '/mod/baseline/locallib.php');
            list($formats, $files) = baseline_portfolio_caller::formats($fields, $record);
            $button->set_formats($formats);
            $replacement[] = $button->to_html(PORTFOLIO_ADD_ICON_LINK);
        } else {
            $replacement[] = '';
        }

        $patterns[] = '##timeadded##';
        $replacement[] = userdate($record->timecreated);

        $patterns[] = '##timemodified##';
        $replacement [] = userdate($record->timemodified);

        $patterns[]='##approve##';
        if (has_capability('mod/baseline:approve', $context) && ($baseline->approval) && (!$record->approved)){
 if (!$summary) {
            $replacement[] = '<span class="approve"><a href="'.$CFG->wwwroot.'/mod/baseline/view.php?d='.$baseline->id.'&amp;approve='.$record->id.'&amp;base='.$base.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('i/approve').'" class="icon" alt="'.get_string('approve').'" /></a></span>';
        } else {
            $replacement[] = '<span class="approve"><a href="'.$CFG->wwwroot.'/mod/baseline/summary.php?d='.$baseline->id.'&amp;approve='.$record->id.'&amp;base='.$base.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('i/approve').'" class="icon" alt="'.get_string('approve').'" /></a></span>';
            }

        } else {
            $replacement[] = '';
        }

        $patterns[]='##comments##';
        if (($template == 'listtemplate') && ($baseline->comments)) {

            if (!empty($CFG->usecomments)) {
                require_once($CFG->dirroot  . '/comment/lib.php');
                list($context, $course, $cm) = get_context_info_array($context->id);
                $cmt = new stdClass();
                $cmt->context = $context;
                $cmt->course  = $course;
                $cmt->cm      = $cm;
                $cmt->area    = 'baseline_entry';
                $cmt->itemid  = $record->id;
                $cmt->showcount = true;
                $cmt->component = 'mod_baseline';
                $comment = new comment($cmt);
                $replacement[] = $comment->output(true);
            }
        } else {
            $replacement[] = '';
        }

        // actual replacement of the tags
        $newtext = str_ireplace($patterns, $replacement, $baseline->{$template});

        // no more html formatting and filtering - see MDL-6635
        if ($return) {
            return $newtext;
        } else {
            echo $newtext;

            // hack alert - return is always false in singletemplate anyway ;-)
            /**********************************
             *    Printing Ratings Form       *
             *********************************/
            if ($template == 'singletemplate') {    //prints ratings options
                baseline_print_ratings($baseline, $record);
            }

            /**********************************
             *    Printing Comments Form       *
             *********************************/
            if (($template == 'singletemplate') && ($baseline->comments)) {
                if (!empty($CFG->usecomments)) {
                    require_once($CFG->dirroot . '/comment/lib.php');
                    list($context, $course, $cm) = get_context_info_array($context->id);
                    $cmt = new stdClass();
                    $cmt->context = $context;
                    $cmt->course  = $course;
                    $cmt->cm      = $cm;
                    $cmt->area    = 'baseline_entry';
                    $cmt->itemid  = $record->id;
                    $cmt->showcount = true;
                    $cmt->component = 'mod_baseline';
                    $comment = new comment($cmt);
                    $comment->output(false);
                }
            }
        }
    }
}

/**
 * Return rating related permissions
 *
 * @param string $contextid the context id
 * @param string $component the component to get rating permissions for
 * @param string $ratingarea the rating area to get permissions for
 * @return array an associative array of the user's rating permissions
 */
function baseline_rating_permissions($contextid, $component, $ratingarea) {
    $context = get_context_instance_by_id($contextid, MUST_EXIST);
    if ($component != 'mod_baseline' || $ratingarea != 'entry') {
        return null;
    }
    return array(
        'view'    => has_capability('mod/baseline:viewrating',$context),
        'viewany' => has_capability('mod/baseline:viewanyrating',$context),
        'viewall' => has_capability('mod/baseline:viewallratings',$context),
        'rate'    => has_capability('mod/baseline:rate',$context)
    );
}

/**
 * Validates a submitted rating
 * @param array $params submitted baseline
 *            context => object the context in which the rated items exists [required]
 *            itemid => int the ID of the object being rated
 *            scaleid => int the scale from which the user can select a rating. Used for bounds checking. [required]
 *            rating => int the submitted rating
 *            rateduserid => int the id of the user whose items have been rated. NOT the user who submitted the ratings. 0 to update all. [required]
 *            aggregation => int the aggregation method to apply when calculating grades ie RATING_AGGREGATE_AVERAGE [required]
 * @return boolean true if the rating is valid. Will throw rating_exception if not
 */
function baseline_rating_validate($params) {
    global $DB, $USER;

    // Check the component is mod_baseline
    if ($params['component'] != 'mod_baseline') {
        throw new rating_exception('invalidcomponent');
    }

    // Check the ratingarea is entry (the only rating area in baseline module)
    if ($params['ratingarea'] != 'entry') {
        throw new rating_exception('invalidratingarea');
    }

    // Check the rateduserid is not the current user .. you can't rate your own entries
    if ($params['rateduserid'] == $USER->id) {
        throw new rating_exception('nopermissiontorate');
    }

    $baselinesql = "SELECT d.id as baselineid, d.scale, d.course, r.userid as userid, d.approval, r.approved, r.timecreated, d.assesstimestart, d.assesstimefinish, r.groupid
                  FROM {baseline_records} r
                  JOIN {baseline} d ON r.baselineid = d.id
                 WHERE r.id = :itemid";
    $baselineparams = array('itemid'=>$params['itemid']);
    if (!$info = $DB->get_record_sql($baselinesql, $baselineparams)) {
        //item doesn't exist
        throw new rating_exception('invaliditemid');
    }

    if ($info->scale != $params['scaleid']) {
        //the scale being submitted doesnt match the one in the baseline
        throw new rating_exception('invalidscaleid');
    }

    //check that the submitted rating is valid for the scale

    // lower limit
    if ($params['rating'] < 0  && $params['rating'] != RATING_UNSET_RATING) {
        throw new rating_exception('invalidnum');
    }

    // upper limit
    if ($info->scale < 0) {
        //its a custom scale
        $scalerecord = $DB->get_record('scale', array('id' => -$info->scale));
        if ($scalerecord) {
            $scalearray = explode(',', $scalerecord->scale);
            if ($params['rating'] > count($scalearray)) {
                throw new rating_exception('invalidnum');
            }
        } else {
            throw new rating_exception('invalidscaleid');
        }
    } else if ($params['rating'] > $info->scale) {
        //if its numeric and submitted rating is above maximum
        throw new rating_exception('invalidnum');
    }

    if ($info->approval && !$info->approved) {
        //baseline requires approval but this item isnt approved
        throw new rating_exception('nopermissiontorate');
    }

    // check the item we're rating was created in the assessable time window
    if (!empty($info->assesstimestart) && !empty($info->assesstimefinish)) {
        if ($info->timecreated < $info->assesstimestart || $info->timecreated > $info->assesstimefinish) {
            throw new rating_exception('notavailable');
        }
    }

    $course = $DB->get_record('course', array('id'=>$info->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('baseline', $info->baselineid, $course->id, false, MUST_EXIST);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id, MUST_EXIST);

    // if the supplied context doesnt match the item's context
    if ($context->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

    // Make sure groups allow this user to see the item they're rating
    $groupid = $info->groupid;
    if ($groupid > 0 and $groupmode = groups_get_activity_groupmode($cm, $course)) {   // Groups are being used
        if (!groups_group_exists($groupid)) { // Can't find group
            throw new rating_exception('cannotfindgroup');//something is wrong
        }

        if (!groups_is_member($groupid) and !has_capability('moodle/site:accessallgroups', $context)) {
            // do not allow rating of posts from other groups when in SEPARATEGROUPS or VISIBLEGROUPS
            throw new rating_exception('notmemberofgroup');
        }
    }

    return true;
}


/**
 * function that takes in the current baseline, number of items per page,
 * a search string and prints a preference box in view.php
 *
 * This preference box prints a searchable advanced search template if
 *     a) A template is defined
 *  b) The advanced search checkbox is checked.
 *
 * @global object
 * @global object
 * @param object $baseline
 * @param int $perpage
 * @param string $search
 * @param string $sort
 * @param string $order
 * @param array $search_array
 * @param int $advanced
 * @param string $mode
 * @return void
 */
function baseline_print_preference_form($baseline, $perpage, $search, $sort='', $order='ASC', $search_array = '', $advanced = 0, $mode= ''){
    global $CFG, $DB, $PAGE, $OUTPUT;

    $cm = get_coursemodule_from_instance('baseline', $baseline->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    echo '<br /><div class="baselinepreferences">';
    echo '<form id="options" action="view.php" method="get">';
    echo '<div>';
    echo '<input type="hidden" name="d" value="'.$baseline->id.'" />';
    if ($mode =='asearch') {
        $advanced = 1;
        echo '<input type="hidden" name="mode" value="list" />';
    }
    echo '<label for="pref_perpage">'.get_string('pagesize','baseline').'</label> ';
    $pagesizes = array(2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,
                       20=>20,30=>30,40=>40,50=>50,100=>100,200=>200,300=>300,400=>400,500=>500,1000=>1000);
    echo html_writer::select($pagesizes, 'perpage', $perpage, false, array('id'=>'pref_perpage'));
    echo '<div id="reg_search" style="display: ';
    if ($advanced) {
        echo 'none';
    }
    else {
        echo 'inline';
    }
    echo ';" >&nbsp;&nbsp;&nbsp;<label for="pref_search">'.get_string('search').'</label> <input type="text" size="16" name="search" id= "pref_search" value="'.s($search).'" /></div>';
    echo '&nbsp;&nbsp;&nbsp;<label for="pref_sortby">'.get_string('sortby').'</label> ';
    // foreach field, print the option
    echo '<select name="sort" id="pref_sortby">';
    if ($fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id), 'name')) {
        echo '<optgroup label="'.get_string('fields', 'baseline').'">';
        foreach ($fields as $field) {
            if ($field->id == $sort) {
                echo '<option value="'.$field->id.'" selected="selected">'.$field->name.'</option>';
            } else {
                echo '<option value="'.$field->id.'">'.$field->name.'</option>';
            }
        }
        echo '</optgroup>';
    }
    $options = array();
    $options[BASELINE_TIMEADDED]    = get_string('timeadded', 'baseline');
    $options[BASELINE_TIMEMODIFIED] = get_string('timemodified', 'baseline');
    $options[BASELINE_FIRSTNAME]    = get_string('authorfirstname', 'baseline');
    $options[BASELINE_LASTNAME]     = get_string('authorlastname', 'baseline');
    if ($baseline->approval and has_capability('mod/baseline:approve', $context)) {
        $options[BASELINE_APPROVED] = get_string('approved', 'baseline');
    }
    echo '<optgroup label="'.get_string('other', 'baseline').'">';
    foreach ($options as $key => $name) {
        if ($key == $sort) {
            echo '<option value="'.$key.'" selected="selected">'.$name.'</option>';
        } else {
            echo '<option value="'.$key.'">'.$name.'</option>';
        }
    }
    echo '</optgroup>';
    echo '</select>';
    echo '<label for="pref_order" class="accesshide">'.get_string('order').'</label>';
    echo '<select id="pref_order" name="order">';
    if ($order == 'ASC') {
        echo '<option value="ASC" selected="selected">'.get_string('ascending','baseline').'</option>';
    } else {
        echo '<option value="ASC">'.get_string('ascending','baseline').'</option>';
    }
    if ($order == 'DESC') {
        echo '<option value="DESC" selected="selected">'.get_string('descending','baseline').'</option>';
    } else {
        echo '<option value="DESC">'.get_string('descending','baseline').'</option>';
    }
    echo '</select>';

    if ($advanced) {
        $checked = ' checked="checked" ';
    }
    else {
        $checked = '';
    }
    $PAGE->requires->js('/mod/baseline/baseline.js');
    echo '&nbsp;<input type="hidden" name="advanced" value="0" />';
    echo '&nbsp;<input type="hidden" name="filter" value="1" />';
    echo '&nbsp;<input type="checkbox" id="advancedcheckbox" name="advanced" value="1" '.$checked.' onchange="showHideAdvSearch(this.checked);" /><label for="advancedcheckbox">'.get_string('advancedsearch', 'baseline').'</label>';
    echo '&nbsp;<input type="submit" value="'.get_string('savesettings','baseline').'" />';

    echo '<br />';
    echo '<div class="baselineadvancedsearch" id="baseline_adv_form" style="display: ';

    if ($advanced) {
        echo 'inline';
    }
    else {
        echo 'none';
    }
    echo ';margin-left:auto;margin-right:auto;" >';
    echo '<table class="boxaligncenter">';

    // print ASC or DESC
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    $i = 0;

    // Determine if we are printing all fields for advanced search, or the template for advanced search
    // If a template is not defined, use the deafault template and display all fields.
    if(empty($baseline->asearchtemplate)) {
        baseline_generate_default_template($baseline, 'asearchtemplate');
    }

    static $fields = NULL;
    static $isteacher;
    static $baselineid = NULL;

    if (empty($baselineid)) {
        $baselineid = $baseline->id;
    } else if ($baselineid != $baseline->id) {
        $fields = NULL;
    }

    if (empty($fields)) {
        $fieldrecords = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id));
        foreach ($fieldrecords as $fieldrecord) {
            $fields[]= baseline_get_field($fieldrecord, $baseline);
        }

        $isteacher = has_capability('mod/baseline:managetemplates', $context);
    }

    // Replacing tags
    $patterns = array();
    $replacement = array();

    // Then we generate strings to replace for normal tags
    foreach ($fields as $field) {
        $fieldname = $field->field->name;
        $fieldname = preg_quote($fieldname, '/');
        $patterns[] = "/\[\[$fieldname\]\]/i";
        $searchfield = baseline_get_field_from_id($field->field->id, $baseline);
        if (!empty($search_array[$field->field->id]->baseline)) {
            $replacement[] = $searchfield->display_search_field($search_array[$field->field->id]->baseline);
        } else {
            $replacement[] = $searchfield->display_search_field();
        }
    }
    $fn = !empty($search_array[BASELINE_FIRSTNAME]->baseline) ? $search_array[BASELINE_FIRSTNAME]->baseline : '';
    $ln = !empty($search_array[BASELINE_LASTNAME]->baseline) ? $search_array[BASELINE_LASTNAME]->baseline : '';
    $patterns[]    = '/##firstname##/';
    $replacement[] = '<input type="text" size="16" name="u_fn" value="'.$fn.'" />';
    $patterns[]    = '/##lastname##/';
    $replacement[] = '<input type="text" size="16" name="u_ln" value="'.$ln.'" />';

    // actual replacement of the tags
    $newtext = preg_replace($patterns, $replacement, $baseline->asearchtemplate);

    $options = new stdClass();
    $options->para=false;
    $options->noclean=true;
    echo '<tr><td>';
    echo format_text($newtext, FORMAT_HTML, $options);
    echo '</td></tr>';

    echo '<tr><td colspan="4" style="text-align: center;"><br/><input type="submit" value="'.get_string('savesettings','baseline').'" /><input type="submit" name="resetadv" value="'.get_string('resetsettings','baseline').'" /></td></tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
}

/**
 * @global object
 * @global object
 * @param object $baseline
 * @param object $record
 * @return void Output echo'd
 */
function baseline_print_ratings($baseline, $record) {
    global $OUTPUT;
    if (!empty($record->rating)){
        echo $OUTPUT->render($record->rating);
    }
}

/**
 * For Participantion Reports
 *
 * @return array
 */
function baseline_get_view_actions() {
    return array('view');
}

/**
 * @return array
 */
function baseline_get_post_actions() {
    return array('add','update','record delete');
}

/**
 * @param string $name
 * @param int $baselineid
 * @param int $fieldid
 * @return bool
 */
function baseline_fieldname_exists($name, $baselineid, $fieldid = 0) {
    global $DB;

    if (!is_numeric($name)) {
        $like = $DB->sql_like('df.name', ':name', false);
    } else {
        $like = "df.name = :name";
    }
    $params = array('name'=>$name);
    if ($fieldid) {
        $params['baselineid']   = $baselineid;
        $params['fieldid1'] = $fieldid;
        $params['fieldid2'] = $fieldid;
        return $DB->record_exists_sql("SELECT * FROM {baseline_fields} df
                                        WHERE $like AND df.baselineid = :baselineid
                                              AND ((df.id < :fieldid1) OR (df.id > :fieldid2))", $params);
    } else {
        $params['baselineid']   = $baselineid;
        return $DB->record_exists_sql("SELECT * FROM {baseline_fields} df
                                        WHERE $like AND df.baselineid = :baselineid", $params);
    }
}

/**
 * @param array $fieldinput
 */
function baseline_convert_arrays_to_strings(&$fieldinput) {
    foreach ($fieldinput as $key => $val) {
        if (is_array($val)) {
            $str = '';
            foreach ($val as $inner) {
                $str .= $inner . ',';
            }
            $str = substr($str, 0, -1);

            $fieldinput->$key = $str;
        }
    }
}


/**
 * Converts a baseline (module instance) to use the Roles System
 *
 * @global object
 * @global object
 * @uses CONTEXT_MODULE
 * @uses CAP_PREVENT
 * @uses CAP_ALLOW
 * @param object $baseline a baseline object with the same attributes as a record
 *                     from the baseline baseline table
 * @param int $baselinemodid the id of the baseline module, from the modules table
 * @param array $teacherroles array of roles that have archetype teacher
 * @param array $studentroles array of roles that have archetype student
 * @param array $guestroles array of roles that have archetype guest
 * @param int $cmid the course_module id for this baseline instance
 * @return boolean baseline module was converted or not
 */
function baseline_convert_to_roles($baseline, $teacherroles=array(), $studentroles=array(), $cmid=NULL) {
    global $CFG, $DB, $OUTPUT;

    if (!isset($baseline->participants) && !isset($baseline->assesspublic)
            && !isset($baseline->groupmode)) {
        // We assume that this baseline has already been converted to use the
        // Roles System. above fields get dropped the baseline module has been
        // upgraded to use Roles.
        return false;
    }

    if (empty($cmid)) {
        // We were not given the course_module id. Try to find it.
        if (!$cm = get_coursemodule_from_instance('baseline', $baseline->id)) {
            echo $OUTPUT->notification('Could not get the course module for the baseline');
            return false;
        } else {
            $cmid = $cm->id;
        }
    }
    $context = get_context_instance(CONTEXT_MODULE, $cmid);


    // $baseline->participants:
    // 1 - Only teachers can add entries
    // 3 - Teachers and students can add entries
    switch ($baseline->participants) {
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:writeentry', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:writeentry', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 3:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:writeentry', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:writeentry', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

    // $baseline->assessed:
    // 2 - Only teachers can rate posts
    // 1 - Everyone can rate posts
    // 0 - No one can rate posts
    switch ($baseline->assessed) {
        case 0:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:rate', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:rate', CAP_PREVENT, $teacherrole->id, $context->id);
            }
            break;
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:rate', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:rate', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 2:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:rate', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:rate', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

    // $baseline->assesspublic:
    // 0 - Students can only see their own ratings
    // 1 - Students can see everyone's ratings
    switch ($baseline->assesspublic) {
        case 0:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:viewrating', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:viewrating', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/baseline:viewrating', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/baseline:viewrating', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

    if (empty($cm)) {
        $cm = $DB->get_record('course_modules', array('id'=>$cmid));
    }

    switch ($cm->groupmode) {
        case NOGROUPS:
            break;
        case SEPARATEGROUPS:
            foreach ($studentroles as $studentrole) {
                assign_capability('moodle/site:accessallgroups', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case VISIBLEGROUPS:
            foreach ($studentroles as $studentrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }
    return true;
}

/**
 * Returns the best name to show for a preset
 *
 * @param string $shortname
 * @param  string $path
 * @return string
 */
function baseline_preset_name($shortname, $path) {

    // We are looking inside the preset itself as a first choice, but also in normal baseline directory
    $string = get_string('modulename', 'baselinepreset_'.$shortname);

    if (substr($string, 0, 1) == '[') {
        return $shortname;
    } else {
        return $string;
    }
}

/**
 * Returns an array of all the available presets.
 *
 * @return array
 */
function baseline_get_available_presets($context) {
    global $CFG, $USER;

    $presets = array();

    // First load the ratings sub plugins that exist within the modules preset dir
    if ($dirs = get_list_of_plugins('mod/baseline/preset')) {
        foreach ($dirs as $dir) {
            $fulldir = $CFG->dirroot.'/mod/baseline/preset/'.$dir;
            if (is_baseline_a_preset($fulldir)) {
                $preset = new stdClass();
                $preset->path = $fulldir;
                $preset->userid = 0;
                $preset->shortname = $dir;
                $preset->name = baseline_preset_name($dir, $fulldir);
                if (file_exists($fulldir.'/screenshot.jpg')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/baseline/preset/'.$dir.'/screenshot.jpg';
                } else if (file_exists($fulldir.'/screenshot.png')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/baseline/preset/'.$dir.'/screenshot.png';
                } else if (file_exists($fulldir.'/screenshot.gif')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/baseline/preset/'.$dir.'/screenshot.gif';
                }
                $presets[] = $preset;
            }
        }
    }
    // Now add to that the site presets that people have saved
    $presets = baseline_get_available_site_presets($context, $presets);
    return $presets;
}

/**
 * Gets an array of all of the presets that users have saved to the site.
 *
 * @param stdClass $context The context that we are looking from.
 * @param array $presets
 * @return array An array of presets
 */
function baseline_get_available_site_presets($context, array $presets=array()) {
    global $USER;

    $fs = get_file_storage();
    $files = $fs->get_area_files(BASELINE_PRESET_CONTEXT, BASELINE_PRESET_COMPONENT, BASELINE_PRESET_FILEAREA);
    $canviewall = has_capability('mod/baseline:viewalluserpresets', $context);
    if (empty($files)) {
        return $presets;
    }
    foreach ($files as $file) {
        if (($file->is_directory() && $file->get_filepath()=='/') || !$file->is_directory() || (!$canviewall && $file->get_userid() != $USER->id)) {
            continue;
        }
        $preset = new stdClass;
        $preset->path = $file->get_filepath();
        $preset->name = trim($preset->path, '/');
        $preset->shortname = $preset->name;
        $preset->userid = $file->get_userid();
        $preset->id = $file->get_id();
        $preset->storedfile = $file;
        $presets[] = $preset;
    }
    return $presets;
}

/**
 * Deletes a saved preset.
 *
 * @param string $name
 * @return bool
 */
function baseline_delete_site_preset($name) {
    $fs = get_file_storage();

    $files = $fs->get_directory_files(BASELINE_PRESET_CONTEXT, BASELINE_PRESET_COMPONENT, BASELINE_PRESET_FILEAREA, 0, '/'.$name.'/');
    if (!empty($files)) {
        foreach ($files as $file) {
            $file->delete();
        }
    }

    $dir = $fs->get_file(BASELINE_PRESET_CONTEXT, BASELINE_PRESET_COMPONENT, BASELINE_PRESET_FILEAREA, 0, '/'.$name.'/', '.');
    if (!empty($dir)) {
        $dir->delete();
    }
    return true;
}

/**
 * Prints the heads for a page
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $baseline
 * @param string $currenttab
 */
function baseline_print_header($course, $cm, $baseline, $currenttab='') {

    global $CFG, $displaynoticegood, $displaynoticebad, $OUTPUT, $PAGE;

    $PAGE->set_title($baseline->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($baseline->name));

// Groups needed for Add entry tab
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

    // Print the tabs

    if ($currenttab) {
        include('tabs.php');
    }

    // Print any notices

    if (!empty($displaynoticegood)) {
        echo $OUTPUT->notification($displaynoticegood, 'notifysuccess');    // good (usually green)
    } else if (!empty($displaynoticebad)) {
        echo $OUTPUT->notification($displaynoticebad);                     // bad (usuually red)
    }
}

/**
 * Can user add more entries?
 *
 * @param object $baseline
 * @param mixed $currentgroup
 * @param int $groupmode
 * @param stdClass $context
 * @return bool
 */
function baseline_user_can_add_entry($baseline, $currentgroup, $groupmode, $context = null) {
    global $USER;

    if (empty($context)) {
        $cm = get_coursemodule_from_instance('baseline', $baseline->id, 0, false, MUST_EXIST);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    }

    if (has_capability('mod/baseline:manageentries', $context)) {
        // no entry limits apply if user can manage

    } else if (!has_capability('mod/baseline:writeentry', $context)) {
        return false;

    } else if (baseline_atmaxentries($baseline)) {
        return false;
    }

    //if in the view only time window
    $now = time();
    if ($now>$baseline->timeviewfrom && $now<$baseline->timeviewto) {
        return false;
    }

    if (!$groupmode or has_capability('moodle/site:accessallgroups', $context)) {
        return true;
    }

    if ($currentgroup) {
        return groups_is_member($currentgroup);
    } else {
        //else it might be group 0 in visible mode
        if ($groupmode == VISIBLEGROUPS){
            return true;
        } else {
            return false;
        }
    }
}


/**
 * @return bool
 */
function is_baseline_a_preset($directory) {
    $directory = rtrim($directory, '/\\') . '/';
    $status = file_exists($directory.'singletemplate.html') &&
              file_exists($directory.'listtemplate.html') &&
              file_exists($directory.'listtemplateheader.html') &&
              file_exists($directory.'listtemplatefooter.html') &&
              file_exists($directory.'addtemplate.html') &&
              file_exists($directory.'rsstemplate.html') &&
              file_exists($directory.'rsstitletemplate.html') &&
              file_exists($directory.'csstemplate.css') &&
              file_exists($directory.'jstemplate.js') &&
              file_exists($directory.'preset.xml');

    return $status;
}

/**
 * Abstract class used for baseline preset importers
 */
abstract class baseline_preset_importer {

    protected $course;
    protected $cm;
    protected $module;
    protected $directory;

    /**
     * Constructor
     *
     * @param stdClass $course
     * @param stdClass $cm
     * @param stdClass $module
     * @param string $directory
     */
    public function __construct($course, $cm, $module, $directory) {
        $this->course = $course;
        $this->cm = $cm;
        $this->module = $module;
        $this->directory = $directory;
    }

    /**
     * Returns the name of the directory the preset is located in
     * @return string
     */
    public function get_directory() {
        return basename($this->directory);
    }

    /**
     * Retreive the contents of a file. That file may either be in a conventional directory of the Moodle file storage
     * @param file_storage $filestorage. should be null if using a conventional directory
     * @param stored_file $fileobj the directory to look in. null if using a conventional directory
     * @param string $dir the directory to look in. null if using the Moodle file storage
     * @param string $filename the name of the file we want
     * @return string the contents of the file
     */
    public function baseline_preset_get_file_contents(&$filestorage, &$fileobj, $dir, $filename) {
        if(empty($filestorage) || empty($fileobj)) {
            if (substr($dir, -1)!='/') {
                $dir .= '/';
            }
            return file_get_contents($dir.$filename);
        } else {
            $file = $filestorage->get_file(BASELINE_PRESET_CONTEXT, BASELINE_PRESET_COMPONENT, BASELINE_PRESET_FILEAREA, 0, $fileobj->get_filepath(), $filename);
            return $file->get_content();
        }

    }
    /**
     * Gets the preset settings
     * @global moodle_baseline $DB
     * @return stdClass
     */
    public function get_preset_settings() {
        global $DB;

        $fs = $fileobj = null;
        if (!is_baseline_a_preset($this->directory)) {
            //maybe the user requested a preset stored in the Moodle file storage

            $fs = get_file_storage();
            $files = $fs->get_area_files(BASELINE_PRESET_CONTEXT, BASELINE_PRESET_COMPONENT, BASELINE_PRESET_FILEAREA);

            //preset name to find will be the final element of the directory
            $presettofind = end(explode('/',$this->directory));

            //now go through the available files available and see if we can find it
            foreach ($files as $file) {
                if (($file->is_directory() && $file->get_filepath()=='/') || !$file->is_directory()) {
                    continue;
                }
                $presetname = trim($file->get_filepath(), '/');
                if ($presetname==$presettofind) {
                    $this->directory = $presetname;
                    $fileobj = $file;
                }
            }

            if (empty($fileobj)) {
                print_error('invalidpreset', 'baseline', '', $this->directory);
            }
        }

        $allowed_settings = array(
            'intro',
            'comments',
            'requiredentries',
            'requiredentriestoview',
            'maxentries',
            'rssarticles',
            'approval',
            'defaultsortdir',
            'defaultsort');

        $result = new stdClass;
        $result->settings = new stdClass;
        $result->importfields = array();
        $result->currentfields = $DB->get_records('baseline_fields', array('baselineid'=>$this->module->id));
        if (!$result->currentfields) {
            $result->currentfields = array();
        }


        /* Grab XML */
        $presetxml = $this->baseline_preset_get_file_contents($fs, $fileobj, $this->directory,'preset.xml');
        $parsedxml = xmlize($presetxml, 0);

        /* First, do settings. Put in user friendly array. */
        $settingsarray = $parsedxml['preset']['#']['settings'][0]['#'];
        $result->settings = new StdClass();
        foreach ($settingsarray as $setting => $value) {
            if (!is_array($value) || !in_array($setting, $allowed_settings)) {
                // unsupported setting
                continue;
            }
            $result->settings->$setting = $value[0]['#'];
        }

        /* Now work out fields to user friendly array */
        $fieldsarray = $parsedxml['preset']['#']['field'];
        foreach ($fieldsarray as $field) {
            if (!is_array($field)) {
                continue;
            }
            $f = new StdClass();
            foreach ($field['#'] as $param => $value) {
                if (!is_array($value)) {
                    continue;
                }
                $f->$param = $value[0]['#'];
            }
            $f->baselineid = $this->module->id;
            $f->type = clean_param($f->type, PARAM_ALPHA);
            $result->importfields[] = $f;
        }
        /* Now add the HTML templates to the settings array so we can update d */
        $result->settings->singletemplate     = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"singletemplate.html");
        $result->settings->listtemplate       = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplate.html");
        $result->settings->listtemplateheader = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplateheader.html");
        $result->settings->listtemplatefooter = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplatefooter.html");
        $result->settings->addtemplate        = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"addtemplate.html");
        $result->settings->rsstemplate        = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"rsstemplate.html");
        $result->settings->rsstitletemplate   = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"rsstitletemplate.html");
        $result->settings->csstemplate        = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"csstemplate.css");
        $result->settings->jstemplate         = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"jstemplate.js");

        //optional
        if (file_exists($this->directory."/asearchtemplate.html")) {
            $result->settings->asearchtemplate = $this->baseline_preset_get_file_contents($fs, $fileobj,$this->directory,"asearchtemplate.html");
        } else {
            $result->settings->asearchtemplate = NULL;
        }
        $result->settings->instance = $this->module->id;

        return $result;
    }

    /**
     * Import the preset into the given baseline module
     * @return bool
     */
    function import($overwritesettings) {
        global $DB, $CFG;

        $params = $this->get_preset_settings();
        $settings = $params->settings;
        $newfields = $params->importfields;
        $currentfields = $params->currentfields;
        $preservedfields = array();

        /* Maps fields and makes new ones */
        if (!empty($newfields)) {
            /* We require an injective mapping, and need to know what to protect */
            foreach ($newfields as $nid => $newfield) {
                $cid = optional_param("field_$nid", -1, PARAM_INT);
                if ($cid == -1) {
                    continue;
                }
                if (array_key_exists($cid, $preservedfields)){
                    print_error('notinjectivemap', 'baseline');
                }
                else $preservedfields[$cid] = true;
            }

            foreach ($newfields as $nid => $newfield) {
                $cid = optional_param("field_$nid", -1, PARAM_INT);

                /* A mapping. Just need to change field params. Baseline kept. */
                if ($cid != -1 and isset($currentfields[$cid])) {
                    $fieldobject = baseline_get_field_from_id($currentfields[$cid]->id, $this->module);
                    foreach ($newfield as $param => $value) {
                        if ($param != "id") {
                            $fieldobject->field->$param = $value;
                        }
                    }
                    unset($fieldobject->field->similarfield);
                    $fieldobject->update_field();
                    unset($fieldobject);
                } else {
                    /* Make a new field */
                    include_once("field/$newfield->type/field.class.php");

                    if (!isset($newfield->description)) {
                        $newfield->description = '';
                    }
                    $classname = 'baseline_field_'.$newfield->type;
                    $fieldclass = new $classname($newfield, $this->module);
                    $fieldclass->insert_field();
                    unset($fieldclass);
                }
            }
        }

        /* Get rid of all old unused baseline */
        if (!empty($preservedfields)) {
            foreach ($currentfields as $cid => $currentfield) {
                if (!array_key_exists($cid, $preservedfields)) {
                    /* Baseline not used anymore so wipe! */
                    print "Deleting field $currentfield->name<br />";

                    $id = $currentfield->id;
                    //Why delete existing baseline records and related comments/ratings??
                    $DB->delete_records('baseline_content', array('fieldid'=>$id));
                    $DB->delete_records('baseline_fields', array('id'=>$id));
                }
            }
        }

        // handle special settings here
        if (!empty($settings->defaultsort)) {
            if (is_numeric($settings->defaultsort)) {
                // old broken value
                $settings->defaultsort = 0;
            } else {
                $settings->defaultsort = (int)$DB->get_field('baseline_fields', 'id', array('baselineid'=>$this->module->id, 'name'=>$settings->defaultsort));
            }
        } else {
            $settings->defaultsort = 0;
        }

        // do we want to overwrite all current baseline settings?
        if ($overwritesettings) {
            // all supported settings
            $overwrite = array_keys((array)$settings);
        } else {
            // only templates and sorting
            $overwrite = array('singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
                               'addtemplate', 'rsstemplate', 'rsstitletemplate', 'csstemplate', 'jstemplate',
                               'asearchtemplate', 'defaultsortdir', 'defaultsort');
        }

        // now overwrite current baseline settings
        foreach ($this->module as $prop=>$unused) {
            if (in_array($prop, $overwrite)) {
                $this->module->$prop = $settings->$prop;
            }
        }

        baseline_update_instance($this->module);

        return $this->cleanup();
    }

    /**
     * Any clean up routines should go here
     * @return bool
     */
    public function cleanup() {
        return true;
    }
}

/**
 * Baseline preset importer for uploaded presets
 */
class baseline_preset_upload_importer extends baseline_preset_importer {
    public function __construct($course, $cm, $module, $filepath) {
        global $USER;
        if (is_file($filepath)) {
            $fp = get_file_packer();
            if ($fp->extract_to_pathname($filepath, $filepath.'_extracted')) {
                fulldelete($filepath);
            }
            $filepath .= '_extracted';
        }
        parent::__construct($course, $cm, $module, $filepath);
    }
    public function cleanup() {
        return fulldelete($this->directory);
    }
}

/**
 * Baseline preset importer for existing presets
 */
class baseline_preset_existing_importer extends baseline_preset_importer {
    protected $userid;
    public function __construct($course, $cm, $module, $fullname) {
        global $USER;
        list($userid, $shortname) = explode('/', $fullname, 2);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        if ($userid && ($userid != $USER->id) && !has_capability('mod/baseline:manageuserpresets', $context) && !has_capability('mod/baseline:viewalluserpresets', $context)) {
           throw new coding_exception('Invalid preset provided');
        }

        $this->userid = $userid;
        $filepath = baseline_preset_path($course, $userid, $shortname);
        parent::__construct($course, $cm, $module, $filepath);
    }
    public function get_userid() {
        return $this->userid;
    }
}

/**
 * @global object
 * @global object
 * @param object $course
 * @param int $userid
 * @param string $shortname
 * @return string
 */
function baseline_preset_path($course, $userid, $shortname) {
    global $USER, $CFG;

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    $userid = (int)$userid;

    $path = null;
    if ($userid > 0 && ($userid == $USER->id || has_capability('mod/baseline:viewalluserpresets', $context))) {
        $path = $CFG->dataroot.'/baseline/preset/'.$userid.'/'.$shortname;
    } else if ($userid == 0) {
        $path = $CFG->dirroot.'/mod/baseline/preset/'.$shortname;
    } else if ($userid < 0) {
        $path = $CFG->tempdir.'/baseline/'.-$userid.'/'.$shortname;
    }

    return $path;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the baseline.
 *
 * @param $mform form passed by reference
 */
function baseline_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'baselineheader', get_string('modulenameplural', 'baseline'));
    $mform->addElement('checkbox', 'reset_baseline', get_string('deleteallentries','baseline'));

    $mform->addElement('checkbox', 'reset_baseline_notenrolled', get_string('deletenotenrolled', 'baseline'));
    $mform->disabledIf('reset_baseline_notenrolled', 'reset_baseline', 'checked');

    $mform->addElement('checkbox', 'reset_baseline_ratings', get_string('deleteallratings'));
    $mform->disabledIf('reset_baseline_ratings', 'reset_baseline', 'checked');

    $mform->addElement('checkbox', 'reset_baseline_comments', get_string('deleteallcomments'));
    $mform->disabledIf('reset_baseline_comments', 'reset_baseline', 'checked');
}

/**
 * Course reset form defaults.
 * @return array
 */
function baseline_reset_course_form_defaults($course) {
    return array('reset_baseline'=>0, 'reset_baseline_ratings'=>1, 'reset_baseline_comments'=>1, 'reset_baseline_notenrolled'=>0);
}

/**
 * Removes all grades from gradebook
 *
 * @global object
 * @global object
 * @param int $courseid
 * @param string $type optional type
 */
function baseline_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT d.*, cm.idnumber as cmidnumber, d.course as courseid
              FROM {baseline} d, {course_modules} cm, {modules} m
             WHERE m.name='baseline' AND m.id=cm.module AND cm.instance=d.id AND d.course=?";

    if ($baselines = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($baselines as $baseline) {
            baseline_grade_item_update($baseline, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * baseline responses for course $baseline->courseid.
 *
 * @global object
 * @global object
 * @param object $baseline the baseline submitted from the reset course.
 * @return array status array
 */
function baseline_reset_userbaseline($baseline) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->dirroot.'/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'baseline');
    $status = array();

    $allrecordssql = "SELECT r.id
                        FROM {baseline_records} r
                             INNER JOIN {baseline} d ON r.baselineid = d.id
                       WHERE d.course = ?";
    $allrecordssql = "SELECT r.id
                        FROM {baseline_base_records} r
                             INNER JOIN {baseline} d ON r.baselineid = d.id
                       WHERE d.course = ?";


    $allbaselinessql = "SELECT d.id
                      FROM {baseline} d
                     WHERE d.course=?";

    $rm = new rating_manager();
    $ratingdeloptions = new stdClass;
    $ratingdeloptions->component = 'mod_baseline';
    $ratingdeloptions->ratingarea = 'entry';

    // delete entries if requested
    if (!empty($baseline->reset_baseline)) {
        $DB->delete_records_select('comments', "itemid IN ($allrecordssql) AND commentarea='baseline_entry'", array($baseline->courseid));
        $DB->delete_records_select('baseline_content', "recordid IN ($allrecordssql)", array($baseline->courseid));
        $DB->delete_records_select('baseline_base_content', "recordid IN ($allbaserecordssql)", array($baseline->courseid));
        $DB->delete_records_select('baseline_records', "baselineid IN ($allbaselinessql)", array($baseline->courseid));
        $DB->delete_records_select('baseline_base_records', "baselineid IN ($allbaselinessql)", array($baseline->courseid));

        if ($baselines = $DB->get_records_sql($allbaselinessql, array($baseline->courseid))) {
            foreach ($baselines as $baselineid=>$unused) {
                fulldelete("$CFG->dataroot/$baseline->courseid/modbaseline/baseline/$baselineid");

                if (!$cm = get_coursemodule_from_instance('baseline', $baselineid)) {
                    continue;
                }
                $baselinecontext = get_context_instance(CONTEXT_MODULE, $cm->id);

                $ratingdeloptions->contextid = $baselinecontext->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        if (empty($baseline->reset_gradebook_grades)) {
            // remove all grades from gradebook
            baseline_reset_gradebook($baseline->courseid);
        }
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallentries', 'baseline'), 'error'=>false);
    }

    // remove entries by users not enrolled into course
    if (!empty($baseline->reset_baseline_notenrolled)) {
        $recordssql = "SELECT r.id, r.userid, r.baselineid, u.id AS userexists, u.deleted AS userdeleted
                         FROM {baseline_records} r
                              JOIN {baseline} d ON r.baselineid = d.id
                              LEFT JOIN {user} u ON r.userid = u.id
                        WHERE d.course = ? AND r.userid > 0";

        $course_context = get_context_instance(CONTEXT_COURSE, $baseline->courseid);
        $notenrolled = array();
        $fields = array();
        $rs = $DB->get_recordset_sql($recordssql, array($baseline->courseid));
        foreach ($rs as $record) {
            if (array_key_exists($record->userid, $notenrolled) or !$record->userexists or $record->userdeleted
              or !is_enrolled($course_context, $record->userid)) {
                //delete ratings
                if (!$cm = get_coursemodule_from_instance('baseline', $record->baselineid)) {
                    continue;
                }
                $baselinecontext = get_context_instance(CONTEXT_MODULE, $cm->id);
                $ratingdeloptions->contextid = $baselinecontext->id;
                $ratingdeloptions->itemid = $record->id;
                $rm->delete_ratings($ratingdeloptions);

                $DB->delete_records('comments', array('itemid'=>$record->id, 'commentarea'=>'baseline_entry'));
                $DB->delete_records('baseline_content', array('recordid'=>$record->id));
                $DB->delete_records('baseline_records', array('id'=>$record->id));
                // HACK: this is ugly - the recordid should be before the fieldid!
                if (!array_key_exists($record->baselineid, $fields)) {
                    if ($fs = $DB->get_records('baseline_fields', array('baselineid'=>$record->baselineid))) {
                        $fields[$record->baselineid] = array_keys($fs);
                    } else {
                        $fields[$record->baselineid] = array();
                    }
                }
                foreach($fields[$record->baselineid] as $fieldid) {
                    fulldelete("$CFG->dataroot/$baseline->courseid/modbaseline/baseline/$record->baselineid/$fieldid/$record->id");
                }
                $notenrolled[$record->userid] = true;
            }
        }
        $rs->close();
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletenotenrolled', 'baseline'), 'error'=>false);
    }

    // remove all ratings
    if (!empty($baseline->reset_baseline_ratings)) {
        if ($baselines = $DB->get_records_sql($allbaselinessql, array($baseline->courseid))) {
            foreach ($baselines as $baselineid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('baseline', $baselineid)) {
                    continue;
                }
                $baselinecontext = get_context_instance(CONTEXT_MODULE, $cm->id);

                $ratingdeloptions->contextid = $baselinecontext->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        if (empty($baseline->reset_gradebook_grades)) {
            // remove all grades from gradebook
            baseline_reset_gradebook($baseline->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallratings'), 'error'=>false);
    }

    // remove all comments
    if (!empty($baseline->reset_baseline_comments)) {
        $DB->delete_records_select('comments', "itemid IN ($allrecordssql) AND commentarea='baseline_entry'", array($baseline->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallcomments'), 'error'=>false);
    }

    // updating dates - shift may be negative too
    if ($baseline->timeshift) {
        shift_course_mod_dates('baseline', array('timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto'), $baseline->timeshift, $baseline->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function baseline_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate', 'moodle/comment:view', 'moodle/comment:post', 'moodle/comment:delete');
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function baseline_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}
/**
 * @global object
 * @param array $export
 * @param string $delimiter_name
 * @param object $baseline
 * @param int $count
 * @param bool $return
 * @return string|void
 */
function baseline_export_csv($export, $delimiter_name, $baselinename, $count, $return=false) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $delimiter = csv_import_reader::get_delimiter($delimiter_name);
    $filename = clean_filename("{$baselinename}-{$count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= clean_filename("-{$delimiter_name}_separated");
    $filename .= '.csv';
    if (empty($return)) {
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=$filename");
        header('Expires: 0');
        header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        header('Pragma: public');
    }
    $encdelim = '&#' . ord($delimiter) . ';';
    $returnstr = '';
    foreach($export as $row) {
        foreach($row as $key => $column) {
            $row[$key] = str_replace($delimiter, $encdelim, $column);
        }
        $returnstr .= implode($delimiter, $row) . "\n";
    }
    if (empty($return)) {
        echo $returnstr;
        return;
    }
    return $returnstr;
}

/**
 * @global object
 * @param array $export
 * @param string $baselinename
 * @param int $count
 * @return string
 */
function baseline_export_xls($export, $baselinename, $count) {
    global $CFG;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename("{$baselinename}-{$count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.xls';

    $filearg = '-';
    $workbook = new MoodleExcelWorkbook($filearg);
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] =& $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    return $filename;
}

/**
 * @global object
 * @param array $export
 * @param string $baselinename
 * @param int $count
 * @param string
 */
function baseline_export_ods($export, $baselinename, $count) {
    global $CFG;
    require_once("$CFG->libdir/odslib.class.php");
    $filename = clean_filename("{$baselinename}-{$count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.ods';
    $filearg = '-';
    $workbook = new MoodleODSWorkbook($filearg);
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] =& $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    return $filename;
}

/**
 * @global object
 * @param int $baselineid
 * @param array $fields
 * @param array $selectedfields
 * @param int $currentgroup group ID of the current group. This is used for
 * exporting baseline while maintaining group divisions.
 * @return array
 */
function baseline_get_exportbaseline($baselineid, $fields, $selectedfields, $currentgroup=0) {
    global $DB;

    $exportbaseline = array();

    // populate the header in first row of export
    foreach($fields as $key => $field) {
        if (!in_array($field->field->id, $selectedfields)) {
            // ignore values we aren't exporting
            unset($fields[$key]);
        } else {
            $exportbaseline[0][] = $field->field->name;
        }
    }

    $baselinerecords = $DB->get_records('baseline_records', array('baselineid'=>$baselineid));
    ksort($baselinerecords);
    $line = 1;
    foreach($baselinerecords as $record) {
        // get content indexed by fieldid
        if ($currentgroup) {
            $select = 'SELECT c.fieldid, c.content, c.content1, c.content2, c.content3, c.content4 FROM {baseline_content} c, {baseline_records} r WHERE c.recordid = ? AND r.id = c.recordid AND r.groupid = ?';
            $where = array($record->id, $currentgroup);
        } else {
            $select = 'SELECT fieldid, content, content1, content2, content3, content4 FROM {baseline_content} WHERE recordid = ?';
            $where = array($record->id);
        }

        if( $content = $DB->get_records_sql($select, $where) ) {
            foreach($fields as $field) {
                $contents = '';
                if(isset($content[$field->field->id])) {
                    $contents = $field->export_text_value($content[$field->field->id]);
                }
                $exportbaseline[$line][] = $contents;
            }
        }
        $line++;
    }
    $line--;
    return $exportbaseline;
}

/**
 * Lists all browsable file areas
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function baseline_get_file_areas($course, $cm, $context) {
    $areas = array();
    return $areas;
}

/**
 * File browsing support for baseline module.
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param cm_info $cm
 * @param context $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info_stored file_info_stored instance or null if not found
 */
function mod_baseline_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    if ($filearea === 'content') {
        if (!$content = $DB->get_record('baseline_content', array('id'=>$itemid))) {
            return null;
        }

        if (!$field = $DB->get_record('baseline_fields', array('id'=>$content->fieldid))) {
            return null;
        }

        if (!$record = $DB->get_record('baseline_records', array('id'=>$content->recordid))) {
            return null;
        }

        if (!$baseline = $DB->get_record('baseline', array('id'=>$field->baselineid))) {
            return null;
        }

        //check if approved
        if ($baseline->approval and !$record->approved and !baseline_isowner($record) and !has_capability('mod/baseline:approve', $context)) {
            return null;
        }

        // group access
        if ($record->groupid) {
            $groupmode = groups_get_activity_groupmode($cm, $course);
            if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                if (!groups_is_member($record->groupid)) {
                    return null;
                }
            }
        }

        $fieldobj = baseline_get_field($field, $baseline, $cm);

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$fieldobj->file_ok($filepath.$filename)) {
            return null;
        }

        $fs = get_file_storage();
        if (!($storedfile = $fs->get_file($context->id, 'mod_baseline', $filearea, $itemid, $filepath, $filename))) {
            return null;
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea, $itemid, true, true, false);
    }

    return null;
}

/**
 * Serves the baseline attachments. Implements needed access control ;-)
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function baseline_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea === 'content') {
        $contentid = (int)array_shift($args);

        if (!$content = $DB->get_record('baseline_content', array('id'=>$contentid))) {
            return false;
        }

        if (!$field = $DB->get_record('baseline_fields', array('id'=>$content->fieldid))) {
            return false;
        }

        if (!$record = $DB->get_record('baseline_records', array('id'=>$content->recordid))) {
            return false;
        }

        if (!$baseline = $DB->get_record('baseline', array('id'=>$field->baselineid))) {
            return false;
        }

        if ($baseline->id != $cm->instance) {
            // hacker attempt - context does not match the contentid
            return false;
        }

        //check if approved
        if ($baseline->approval and !$record->approved and !baseline_isowner($record) and !has_capability('mod/baseline:approve', $context)) {
            return false;
        }

        // group access
        if ($record->groupid) {
            $groupmode = groups_get_activity_groupmode($cm, $course);
            if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                if (!groups_is_member($record->groupid)) {
                    return false;
                }
            }
        }

        $fieldobj = baseline_get_field($field, $baseline, $cm);

        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_baseline/content/$content->id/$relativepath";

        if (!$fieldobj->file_ok($relativepath)) {
            return false;
        }

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        // finally send the file
        send_stored_file($file, 0, 0, true); // download MUST be forced - security!
    }

    return false;
}


function baseline_extend_navigation($navigation, $course, $module, $cm) {
    global $CFG, $OUTPUT, $USER, $DB;

    $rid = optional_param('rid', 0, PARAM_INT);

    $baseline = $DB->get_record('baseline', array('id'=>$cm->instance));
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

     $numentries = baseline_numentries($baseline);
    /// Check the number of entries required against the number of entries already made (doesn't apply to teachers)
    if ($baseline->requiredentries > 0 && $numentries < $baseline->requiredentries && !has_capability('mod/baseline:manageentries', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $baseline->entriesleft = $baseline->requiredentries - $numentries;
        $entriesnode = $navigation->add(get_string('entrieslefttoadd', 'baseline', $baseline));
        $entriesnode->add_class('note');
    }

    $navigation->add(get_string('list', 'baseline'), new moodle_url('/mod/baseline/view.php', array('d'=>$cm->instance)));
    if (!empty($rid)) {
        $navigation->add(get_string('single', 'baseline'), new moodle_url('/mod/baseline/view.php', array('d'=>$cm->instance, 'rid'=>$rid)));
    } else {
        $navigation->add(get_string('single', 'baseline'), new moodle_url('/mod/baseline/view.php', array('d'=>$cm->instance, 'mode'=>'single')));
    }
    $navigation->add(get_string('search', 'baseline'), new moodle_url('/mod/baseline/view.php', array('d'=>$cm->instance, 'mode'=>'asearch')));
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $baselinenode The node to add module settings to
 */
function baseline_extend_settings_navigation(settings_navigation $settings, navigation_node $baselinenode) {
    global $PAGE, $DB, $CFG, $USER;

    $baseline = $DB->get_record('baseline', array("id" => $PAGE->cm->instance));

    $currentgroup = groups_get_activity_group($PAGE->cm);
    $groupmode = groups_get_activity_groupmode($PAGE->cm);

    if (baseline_user_can_add_entry($baseline, $currentgroup, $groupmode, $PAGE->cm->context)) { // took out participation list here!
        if (empty($editentry)) { //TODO: undefined
            $addstring = get_string('add', 'baseline');
        } else {
            $addstring = get_string('editentry', 'baseline');
        }
        $baselinenode->add($addstring, new moodle_url('/mod/baseline/edit.php', array('d'=>$PAGE->cm->instance)));
    }

    if (has_capability(BASELINE_CAP_EXPORT, $PAGE->cm->context)) {
        // The capability required to Export baseline records is centrally defined in 'lib.php'
        // and should be weaker than those required to edit Templates, Fields and Presets.
        $baselinenode->add(get_string('exportentries', 'baseline'), new moodle_url('/mod/baseline/export.php', array('d'=>$baseline->id)));
    }
    if (has_capability('mod/baseline:manageentries', $PAGE->cm->context)) {
        $baselinenode->add(get_string('importentries', 'baseline'), new moodle_url('/mod/baseline/import.php', array('d'=>$baseline->id)));
    }

    if (has_capability('mod/baseline:managetemplates', $PAGE->cm->context)) {
        $currenttab = '';
        if ($currenttab == 'list') {
            $defaultemplate = 'listtemplate';
        } else if ($currenttab == 'add') {
            $defaultemplate = 'addtemplate';
        } else if ($currenttab == 'asearch') {
            $defaultemplate = 'asearchtemplate';
        } else {
            $defaultemplate = 'singletemplate';
        }

        $templates = $baselinenode->add(get_string('templates', 'baseline'));

        $templatelist = array ('listtemplate', 'singletemplate', 'asearchtemplate', 'addtemplate', 'rsstemplate', 'csstemplate', 'jstemplate');
        foreach ($templatelist as $template) {
            $templates->add(get_string($template, 'baseline'), new moodle_url('/mod/baseline/templates.php', array('d'=>$baseline->id,'mode'=>$template)));
        }

        $baselinenode->add(get_string('fields', 'baseline'), new moodle_url('/mod/baseline/field.php', array('d'=>$baseline->id)));
        $baselinenode->add(get_string('presets', 'baseline'), new moodle_url('/mod/baseline/preset.php', array('d'=>$baseline->id)));
    }

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->baseline_enablerssfeeds) && $baseline->rssarticles > 0) {
        require_once("$CFG->libdir/rsslib.php");

        $string = get_string('rsstype','forum');

        $url = new moodle_url(rss_get_url($PAGE->cm->context->id, $USER->id, 'mod_baseline', $baseline->id));
        $baselinenode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));
    }
}

/**
 * Save the baseline configuration as a preset.
 *
 * @param stdClass $course The course the baseline module belongs to.
 * @param stdClass $cm The course module record
 * @param stdClass $baseline The baseline record
 * @param string $path
 * @return bool
 */
function baseline_presets_save($course, $cm, $baseline, $path) {
    global $USER;
    $fs = get_file_storage();
    $filerecord = new stdClass;
    $filerecord->contextid = BASELINE_PRESET_CONTEXT;
    $filerecord->component = BASELINE_PRESET_COMPONENT;
    $filerecord->filearea = BASELINE_PRESET_FILEAREA;
    $filerecord->itemid = 0;
    $filerecord->filepath = '/'.$path.'/';
    $filerecord->userid = $USER->id;

    $filerecord->filename = 'preset.xml';
    $fs->create_file_from_string($filerecord, baseline_presets_generate_xml($course, $cm, $baseline));

    $filerecord->filename = 'singletemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->singletemplate);

    $filerecord->filename = 'listtemplateheader.html';
    $fs->create_file_from_string($filerecord, $baseline->listtemplateheader);

    $filerecord->filename = 'listtemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->listtemplate);

    $filerecord->filename = 'listtemplatefooter.html';
    $fs->create_file_from_string($filerecord, $baseline->listtemplatefooter);

    $filerecord->filename = 'addtemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->addtemplate);

    $filerecord->filename = 'rsstemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->rsstemplate);

    $filerecord->filename = 'rsstitletemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->rsstitletemplate);

    $filerecord->filename = 'csstemplate.css';
    $fs->create_file_from_string($filerecord, $baseline->csstemplate);

    $filerecord->filename = 'jstemplate.js';
    $fs->create_file_from_string($filerecord, $baseline->jstemplate);

    $filerecord->filename = 'asearchtemplate.html';
    $fs->create_file_from_string($filerecord, $baseline->asearchtemplate);

    return true;
}

/**
 * Generates the XML for the baseline module provided
 *
 * @global moodle_baseline $DB
 * @param stdClass $course The course the baseline module belongs to.
 * @param stdClass $cm The course module record
 * @param stdClass $baseline The baseline record
 * @return string The XML for the preset
 */
function baseline_presets_generate_xml($course, $cm, $baseline) {
    global $DB;

    // Assemble "preset.xml":
    $presetxmlbaseline = "<preset>\n\n";

    // Raw settings are not preprocessed during saving of presets
    $raw_settings = array(
        'intro',
        'comments',
        'requiredentries',
        'requiredentriestoview',
        'maxentries',
        'rssarticles',
        'approval',
        'defaultsortdir'
    );

    $presetxmlbaseline .= "<settings>\n";
    // First, settings that do not require any conversion
    foreach ($raw_settings as $setting) {
        $presetxmlbaseline .= "<$setting>" . htmlspecialchars($baseline->$setting) . "</$setting>\n";
    }

    // Now specific settings
    if ($baseline->defaultsort > 0 && $sortfield = baseline_get_field_from_id($baseline->defaultsort, $baseline)) {
        $presetxmlbaseline .= '<defaultsort>' . htmlspecialchars($sortfield->field->name) . "</defaultsort>\n";
    } else {
        $presetxmlbaseline .= "<defaultsort>0</defaultsort>\n";
    }
    $presetxmlbaseline .= "</settings>\n\n";
    // Now for the fields. Grab all that are non-empty
    $fields = $DB->get_records('baseline_fields', array('baselineid'=>$baseline->id));
    ksort($fields);
    if (!empty($fields)) {
        foreach ($fields as $field) {
            $presetxmlbaseline .= "<field>\n";
            foreach ($field as $key => $value) {
                if ($value != '' && $key != 'id' && $key != 'baselineid') {
                    $presetxmlbaseline .= "<$key>" . htmlspecialchars($value) . "</$key>\n";
                }
            }
            $presetxmlbaseline .= "</field>\n\n";
        }
    }
    $presetxmlbaseline .= '</preset>';
    return $presetxmlbaseline;
}

function baseline_presets_export($course, $cm, $baseline, $tostorage=false) {
    global $CFG, $DB;

    $presetname = clean_filename($baseline->name) . '-preset-' . gmdate("Ymd_Hi");
    $exportsubdir = "mod_baseline/presetexport/$presetname";
    make_temp_directory($exportsubdir);
    $exportdir = "$CFG->tempdir/$exportsubdir";

    // Assemble "preset.xml":
    $presetxmlbaseline = baseline_presets_generate_xml($course, $cm, $baseline);

    // After opening a file in write mode, close it asap
    $presetxmlfile = fopen($exportdir . '/preset.xml', 'w');
    fwrite($presetxmlfile, $presetxmlbaseline);
    fclose($presetxmlfile);

    // Now write the template files
    $singletemplate = fopen($exportdir . '/singletemplate.html', 'w');
    fwrite($singletemplate, $baseline->singletemplate);
    fclose($singletemplate);

    $listtemplateheader = fopen($exportdir . '/listtemplateheader.html', 'w');
    fwrite($listtemplateheader, $baseline->listtemplateheader);
    fclose($listtemplateheader);

    $listtemplate = fopen($exportdir . '/listtemplate.html', 'w');
    fwrite($listtemplate, $baseline->listtemplate);
    fclose($listtemplate);

    $listtemplatefooter = fopen($exportdir . '/listtemplatefooter.html', 'w');
    fwrite($listtemplatefooter, $baseline->listtemplatefooter);
    fclose($listtemplatefooter);

    $addtemplate = fopen($exportdir . '/addtemplate.html', 'w');
    fwrite($addtemplate, $baseline->addtemplate);
    fclose($addtemplate);

    $rsstemplate = fopen($exportdir . '/rsstemplate.html', 'w');
    fwrite($rsstemplate, $baseline->rsstemplate);
    fclose($rsstemplate);

    $rsstitletemplate = fopen($exportdir . '/rsstitletemplate.html', 'w');
    fwrite($rsstitletemplate, $baseline->rsstitletemplate);
    fclose($rsstitletemplate);

    $csstemplate = fopen($exportdir . '/csstemplate.css', 'w');
    fwrite($csstemplate, $baseline->csstemplate);
    fclose($csstemplate);

    $jstemplate = fopen($exportdir . '/jstemplate.js', 'w');
    fwrite($jstemplate, $baseline->jstemplate);
    fclose($jstemplate);

    $asearchtemplate = fopen($exportdir . '/asearchtemplate.html', 'w');
    fwrite($asearchtemplate, $baseline->asearchtemplate);
    fclose($asearchtemplate);

    // Check if all files have been generated
    if (! is_baseline_a_preset($exportdir)) {
        print_error('generateerror', 'baseline');
    }

    $filenames = array(
        'preset.xml',
        'singletemplate.html',
        'listtemplateheader.html',
        'listtemplate.html',
        'listtemplatefooter.html',
        'addtemplate.html',
        'rsstemplate.html',
        'rsstitletemplate.html',
        'csstemplate.css',
        'jstemplate.js',
        'asearchtemplate.html'
    );

    $filelist = array();
    foreach ($filenames as $filename) {
        $filelist[$filename] = $exportdir . '/' . $filename;
    }

    $exportfile = $exportdir.'.zip';
    file_exists($exportfile) && unlink($exportfile);

    $fp = get_file_packer('application/zip');
    $fp->archive_to_pathname($filelist, $exportfile);

    foreach ($filelist as $file) {
        unlink($file);
    }
    rmdir($exportdir);

    // Return the full path to the exported preset file:
    return $exportfile;
}

/**
 * Running addtional permission check on plugin, for example, plugins
 * may have switch to turn on/off comments option, this callback will
 * affect UI display, not like pluginname_comment_validate only throw
 * exceptions.
 * Capability check has been done in comment->check_permissions(), we
 * don't need to do it again here.
 *
 * @param stdClass $comment_param {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return array
 */
function baseline_comment_permissions($comment_param) {
    global $CFG, $DB;
    if (!$record = $DB->get_record('baseline_records', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$baseline = $DB->get_record('baseline', array('id'=>$record->baselineid))) {
        throw new comment_exception('invalidid', 'baseline');
    }
    if ($baseline->comments) {
        return array('post'=>true, 'view'=>true);
    } else {
        return array('post'=>false, 'view'=>false);
    }
}

/**
 * Validate comment parameter before perform other comments actions
 *
 * @param stdClass $comment_param {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean
 */
function baseline_comment_validate($comment_param) {
    global $DB;
    // validate comment area
    if ($comment_param->commentarea != 'baseline_entry') {
        throw new comment_exception('invalidcommentarea');
    }
    // validate itemid
    if (!$record = $DB->get_record('baseline_records', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$baseline = $DB->get_record('baseline', array('id'=>$record->baselineid))) {
        throw new comment_exception('invalidid', 'baseline');
    }
    if (!$course = $DB->get_record('course', array('id'=>$baseline->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('baseline', $baseline->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    if (!$baseline->comments) {
        throw new comment_exception('commentsoff', 'baseline');
    }
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    //check if approved
    if ($baseline->approval and !$record->approved and !baseline_isowner($record) and !has_capability('mod/baseline:approve', $context)) {
        throw new comment_exception('notapproved', 'baseline');
    }

    // group access
    if ($record->groupid) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            if (!groups_is_member($record->groupid)) {
                throw new comment_exception('notmemberofgroup');
            }
        }
    }
    // validate context id
    if ($context->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
    // validation for comment deletion
    if (!empty($comment_param->commentid)) {
        if ($comment = $DB->get_record('comments', array('id'=>$comment_param->commentid))) {
            if ($comment->commentarea != 'baseline_entry') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $comment_param->context->id) {
                throw new comment_exception('invalidcontext');
            }
            if ($comment->itemid != $comment_param->itemid) {
                throw new comment_exception('invalidcommentitemid');
            }
        } else {
            throw new comment_exception('invalidcommentid');
        }
    }
    return true;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function baseline_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-baseline-*'=>get_string('page-mod-baseline-x', 'baseline'));
    return $module_pagetype;
}
function my_nav($course) { 
    global $DB,$CFG, $USER;
    $str = '';
    if (!$user = $DB->get_record('user',  array('id'=>$USER->id))) {
           error("No such user! ", '', true);
       }

      $str .= fullname($user)."&nbsp;with id {$USER->id}<br>";
      if (! $baselines = get_all_instances_in_course("baseline", $course)) {

        notice(get_string('thereareno', 'moodle',$strbaselineplural) , "$CFG->wwwroot/course/bedit.php?id=$course->id&base=$true");
       } else {
       foreach($baselines as $baseline) {
       $str.='<a href="view.php?d='.$baseline->id.'">'.$baseline->name.'</a>&nbsp;';
        //$str .= "<a href=\"javascript:void(0);\" onmouseover=\"return overlib('This is a simple tooltip popup.', AUTOSTATUS, WRAP);\" onmouseout=\"nd();\">tooltip</a>';";
//        $str .= "<a href=\"javascript:void(0);\" onmouseover=\"return overlib('<a href=view.php?d=$baseline->id>$baseline->name</a>', AUTOSTATUS, WRAP);\" onmouseout=\"nd();\">$baseline->name</a>&nbsp;";
	}
	}
       $str .= my_users($course) ;
	//FPS removed navigation done via css Harry Rolf
        $str = '';
      return $str;

  }

function my_users($course) { 
// if have capability selectusers
        $users =(get_users_listing());
  //make a dropdown with a link to set global $DB,id and reload page.
 foreach( $users as $user ){
// <select id="navmenupopup_jump" name="jump" onchange="self.location=document.getElementById('navmenupopup').jump.options[document.getElementById('navmenupopup').jump.selectedIndex].value;">

     //echo '<br>'.$user->firstname.'&nbsp;'.$user->lastname.'&nbsp;id='.$user->id;
      //echo "$user->firstname&nbsp;$user->lastname&nbsp;id=$user->id";
  }
}
function set_user($user) {
      global $DB,$CFG, $USER;
// if not student
          //$myvuser = $_SESSION['vuser'];
            //print($_SESSION['vuser']."HHH ".$user);
           // if(!$_SESSION['vuser']) { $myvuser = $USER->id;} 
           //if($user)  { $myvuser = $user; } 
		$myvuser = $USER->id;
            //print($_SESSION['vuser']."YYY".$myvuser."XXX".$user);
         return $myvuser;
}
function getAge($birthdate,$edate) {
//DOB in extended USER profile.

  list($day,$month,$year) = explode("/",$birthdate);
  list($eday,$emonth,$eyear) = explode("/",$edate);
  if ($year != "" && $year != "0000") {   //If year is not blank and not null then we continue

        if ($year >= date("Y")-2) {                       //If the birthyear is within two years, then we'll only calculate the months not years
          if($year == date("Y")){                               //Current year
                $months = date('m') - $month;
          }
          if($year == date("Y")-1){                       //Last year
                $months = date('m') - $month + 12;
          }
          if($year == date("Y")-2){                       //Two years ago
                $months = date('m') - $month + 24;
          }

          return $months;
        } else {
          $years = date("Y")-$year-1;

          if(date("m")==$month){                   // If the birth month is equal to the current month then we just return years, no months.
                $years = $years + 1;
                return $years * 12;
          }else{                                                   // Else get the months and years
                $months = date("m")+12-$month;
                return $years*12 + $months;
          }
        }
  }else{
        return "";
  }
}
function table_graph($search, $fieldrid) {
        global $DB,$CFG;
// Show the current value as a highlighted value if found by search
 $str =  highlight($search, $fieldrid);
$str = '';
     if ( $CFG->base) {
        /* Mod to show rids baseline entries  FPS */
        //$rb =  array_pop(explode(',',$this->field->basemyrecs) );
        $str .= '<img src="cf/activity2.gif"> Better ';
      //$str .= $rb;
  } else {

 $str .=  ' <table class="ca ca1" border="10" BORDERCOLOR=lightblue cellpadding="2" cellspacing="0"><caption>Results display</caption><tbody><tr class="cl"><td></td>'.'<td>Sunday</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td><td class="cr">Saturday</td></tr> <tr><td>Worse than usual</td><td></td><td></td><td></td><td></td><td></td><td class="co1"><img src="cf/red.gif"</td><td class="co1 cr"></td></tr> <tr><td>Usual</td><td class="co1"><img src="cf/amber.gif"</td><td class="co1"></td><td></td><td></td><td></td><td></td><td class="co4 cr"></td></tr> <tr><td>Better than usual</td><td class="co1"></td><td></td><td></td><td></td><td><img src="cf/green.gif"</td><td></td><td class="co4 cr"></td></tr> <tr class="cb"><td></td><td></td><td></td><td></td><td></td><td></td><td class="cr"></td><td></td></tr> </tbody></table>';
}
return $str;
}

function display_graph() {
// Need to know the field id
        global $DB,$CFG;
     $str ='<td class="graph">';
 //get a scale from range of param.
     $myscalevalue = 0;
     $mynegvalue = 0;
     foreach (explode("\n",$this->field->param1) as $myvalue) {
          if( $myvalue >= 0 ){
      if ( $myscalevalue <= $myvalue ) { $myscalevalue = $myvalue; }
      } else {
      if ( $mynegvalue < $myvalue || $mynegvalue == 0 ) { $mynegvalue  =   $myvalue; }
         }
      }
	$this->field->scalebase =  $myscalevalue - $mynegvalue + 1; 
       if ( $this->field->scalebase == 0 ) {$this->field->scalebase =  1;}
        $days=$this->field->days;
        $myrecs= $this->field->myrecs;
        $basedays=$this->field->basedays;
        $basemyrecs=  $this->field->basemyrecs;
	  $myscale = 2 ;
	  $mywidth = 160 ;
	  $myheight = 80 ;
          $myxscale= $mywidth/$this->field->scaleday;
          $myyscale= $myheight/$this->field->scalebase;
       //echo "$myheight/$this->field->scaleday<br>$mywidth/$this->field->scalebase";
 //       echo "<br> base";
// print_r($this->field->scalebase);
        $bigdays='';
        $bigmyrecs='';
        $bigbasemyrecs='';
        $bigbasedays='';
       foreach (explode(',',$days) as $day ) $bigdays.=$day*$myxscale.',';
       foreach (explode(',',$myrecs) as $myrec ) $bigmyrecs.=$myheight/2 +($myrec*$myyscale).',';
       foreach (explode(',',$basedays) as $baseday ) $bigbasedays.=$baseday*$myxscale.',';
       foreach (explode(',',$basemyrecs) as $basemyrec ) $bigbasemyrecs.=$myheight/2 +($basemyrec*$myyscale).',';
       //need to remove trailing comma.
	//print_r($basedays);
	//print"<br>";
	 $somerecords = substr($bigdays,0,-1)."|". substr($bigmyrecs,0,-1)."|".  substr($bigbasedays,0,-1)."|".  substr($bigbasemyrecs,0,-1);
	 $somerecords = substr($bigdays,0,-1)."|". substr($bigmyrecs,0,-1);
        //$somebigrecords = $bigdays."|".$bigmyrecs."|".$bigbasedays. "|".$bigbasemyrecs;
	 // $somerecords = substr($days,0,-1)."|". substr($myrecs,0,-1)."|".  substr($basedays,0,-1)."|".  substr($basemyrecs,0,-1);
        // $somerecords = $somebigrecords; 
 $myyscale = $myyscale * $myscale;
 $myxscale = $myxscale * $myscale;
       foreach (explode(',',$days) as $day ) $bigdays.=$day*$myxscale.',';
       foreach (explode(',',$myrecs) as $myrec ) $bigmyrecs.=($myscale*$myheight) +($myrec*$myyscale).',';
       foreach (explode(',',$basedays) as $baseday ) $bigbasedays.=$baseday*$myxscale.',';
       foreach (explode(',',$basemyrecs) as $basemyrec ) $bigbasemyrecs.=($myscale*$myheight) +($basemyrec*$myyscale).',';
       //need to remove trailing comma.
	 $somebigrecords = substr($bigdays,0,-1)."|". substr($bigmyrecs,0,-1)."|".  substr($bigbasedays,0,-1)."|".  substr($bigbasemyrecs,0,-1);
	 $somebigrecords = substr($bigdays,0,-1)."|". substr($bigmyrecs,0,-1);
// use the sequence in reverse order latest 1st up to 10 or display demo graph?
//  popup_form(
      if( $somerecords) {
         $myurl=  $mywidth;
         $myurl .= 'x';
         $myurl .=  $myheight;
        $url = "http://chart.apis.google.com/chart?cht=lxy&chdl=Daily|Baseline&chs=".$myurl."&chco=ff0020,00ff00,0000ff,000000&chd=t:".$somerecords;
         $myburl=$myscale *  $mywidth;
         $myburl .= 'x';
         $myburl .= $myscale * $myheight;
        $bigurl = "http://chart.apis.google.com/chart?cht=lxy&chdl=Daily|Baseline&chs=".$myburl."&chco=ff0020,00ff00,0000ff,000000&chd=t:".$somebigrecords;

// curl needs  a gateway through the firewall.
   //if ( check_browser_version('MSIE', 6.0) ) {
   if ( check_browser_version('MSIE') ) {
    list ($usec, $sec) = explode(" ", microtime());
    $filename_pie_chart = 'dyna_'. $this->field->name . $sec . $usec . '.png';
    $baseline_dir = $CFG->dirroot.'/mod/baseline/cf';
    $baseline_pic = $CFG->httpswwwroot.'/mod/baseline/cf';
     system("/usr/bin/curl   --output $baseline_dir/$filename_pie_chart --url \"$url\"");
    // system("/usr/bin/curl --proxy proxy.utas.edu.au:8080 --proxy-user dk:XXXXXXX --silent  --output $baseline_dir/$filename_pie_chart --url \"$url\"");

    // The variable that will be used to display the pie chart.
    $reg_rep_pie_chart = "<img src=\"$baseline_pic/$filename_pie_chart\" alt=\"".$this->field->description."\" /> </td>";
       } else {
    $reg_rep_pie_chart = "<img src=\"$url\" alt=\"".$this->field->name."\" /> </td>";
       }
          $str .= $reg_rep_pie_chart;
       } else {
    $reg_rep_pie_chart = "<img src=\"$baseline_pic/graph_demo.png\" alt=\"Demo ".$this->field->name."\" /> </td> ";
          $str .= $reg_rep_pie_chart;
       }
      return $str;
}


?>
