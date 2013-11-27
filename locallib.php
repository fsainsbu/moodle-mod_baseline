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

require_once($CFG->dirroot . '/mod/baseline/lib.php');
require_once($CFG->libdir . '/portfolio/caller.php');

/**
 * The class to handle entry exports of a baseline module
 */
class baseline_portfolio_caller extends portfolio_module_caller_base {

    /** @var int the single record to export */
    protected $recordid;

    /** @var object the record from the baseline table */
    private $baseline;

    /**#@+ @var array the fields used and their fieldtypes */
    private $fields;
    private $fieldtypes;

    /** @var object the records to export */
    private $records;

    /** @var int how many records are 'mine' */
    private $minecount;

    /**
     * the required callback arguments for a single-record export
     *
     * @return array
     */
    public static function expected_callbackargs() {
        return array(
            'id'       => true,
            'recordid' => false,
        );
    }

    /**
     * @param array $callbackargs the arguments passed through
     */
    public function __construct($callbackargs) {
        parent::__construct($callbackargs);
        // set up the list of fields to export
        $this->selectedfields = array();
        foreach ($callbackargs as $key => $value) {
            if (strpos($key, 'field_') === 0) {
                $this->selectedfields[] = substr($key, 6);
            }
        }
    }

    /**
     * load up the baseline needed for the export
     *
     * @global object $DB
     */
    public function load_baseline() {
        global $DB, $USER;
        if (!$this->cm = get_coursemodule_from_id('baseline', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'baseline');
        }
        if (!$this->baseline = $DB->get_record('baseline', array('id' => $this->cm->instance))) {
            throw new portfolio_caller_exception('invalidid', 'baseline');
        }
        $fieldrecords = $DB->get_records('baseline_fields', array('baselineid' => $this->cm->instance), 'id');
        // populate objets for this baselines fields
        $this->fields = array();
        foreach ($fieldrecords as $fieldrecord) {
            $tmp = baseline_get_field($fieldrecord, $this->baseline);
            $this->fields[] = $tmp;
            $this->fieldtypes[]  = $tmp->type;
        }

        $this->records = array();
        if ($this->recordid) {
            $tmp = $DB->get_record('baseline_records', array('id' => $this->recordid));
            $tmp->content = $DB->get_records('baseline_content', array('recordid' => $this->recordid));
            $this->records[] = $tmp;
        } else {
            $where = array('baselineid' => $this->baseline->id);
            if (!has_capability('mod/baseline:exportallentries', get_context_instance(CONTEXT_MODULE, $this->cm->id))) {
                $where['userid'] = $USER->id; // get them all in case, we'll unset ones that aren't ours later if necessary
            }
            $tmp = $DB->get_records('baseline_records', $where);
            foreach ($tmp as $t) {
                $t->content = $DB->get_records('baseline_content', array('recordid' => $t->id));
                $this->records[] = $t;
            }
            $this->minecount = $DB->count_records('baseline_records', array('baselineid' => $this->baseline->id, 'userid' => $USER->id));
        }

        if ($this->recordid) {
            list($formats, $files) = self::formats($this->fields, $this->records[0]);
            $this->set_file_and_format_baseline($files);
        }
    }

    /**
     * How long we think the export will take
     * Single entry is probably not too long.
     * But we check for filesizes
     * Else base it on the number of records
     *
     * @return one of PORTFOLIO_TIME_XX constants
     */
    public function expected_time() {
        if ($this->recordid) {
            return $this->expected_time_file();
        } else {
            return portfolio_expected_time_db(count($this->records));
        }
    }

    /**
     * Calculate the shal1 of this export
     * Dependent on the export format.
     * @return string
     */
    public function get_sha1() {
        // in the case that we're exporting a subclass of 'file' and we have a singlefile,
        // then we're not exporting any metabaseline, just the file by itself by mimetype.
        if ($this->exporter->get('format') instanceof portfolio_format_file && $this->singlefile) {
            return $this->get_sha1_file();
        }
        // otherwise we're exporting some sort of multipart content so use the baseline
        $str = '';
        foreach ($this->records as $record) {
            foreach ($record as $baseline) {
                if (is_array($baseline) || is_object($baseline)) {
                    $testkey = array_pop(array_keys($baseline));
                    if (is_array($baseline[$testkey]) || is_object($baseline[$testkey])) {
                        foreach ($baseline as $d) {
                            $str .= implode(',', (array)$d);
                        }
                    } else {
                        $str .= implode(',', (array)$baseline);
                    }
                } else {
                    $str .= $baseline;
                }
            }
        }
        return sha1($str . ',' . $this->exporter->get('formatclass'));
    }

    /**
     * Prepare the package for export
     *
     * @return stored_file object
     */
    public function prepare_package() {
        global $DB;
        $leapwriter = null;
        $content = '';
        $filename = '';
        $uid = $this->exporter->get('user')->id;
        $users = array(); //cache
        $onlymine = $this->get_export_config('mineonly');
        if ($this->exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
            $leapwriter = $this->exporter->get('format')->leap2a_writer();
            $ids = array();
        }

        if ($this->exporter->get('format') instanceof portfolio_format_file && $this->singlefile) {
            return $this->get('exporter')->copy_existing_file($this->singlefile);
        }
        foreach ($this->records  as $key => $record) {
            if ($onlymine && $record->userid != $uid) {
                unset($this->records[$key]); // sha1
                continue;
            }
            list($tmpcontent, $files)  = $this->exportentry($record);
            $content .= $tmpcontent;
            if ($leapwriter) {
                $entry = new portfolio_format_leap2a_entry('baselineentry' . $record->id, $this->baseline->name, 'resource', $tmpcontent);
                $entry->published = $record->timecreated;
                $entry->updated = $record->timemodified;
                if ($record->userid != $uid) {
                    if (!array_key_exists($record->userid, $users)) {
                        $users[$record->userid] = $DB->get_record('user', array('id' => $record->userid), 'id,firstname,lastname');
                    }
                    $entry->author = $users[$record->userid];
                }
                $ids[] = $entry->id;
                $leapwriter->link_files($entry, $files, 'baselineentry' . $record->id . 'file');
                $leapwriter->add_entry($entry);
            }
        }
        if ($leapwriter) {
            if (count($this->records) > 1) { // make a selection element to tie them all together
                $selection = new portfolio_format_leap2a_entry('baselinedb' . $this->baseline->id,
                    get_string('entries', 'baseline') . ': ' . $this->baseline->name, 'selection');
                $leapwriter->add_entry($selection);
                $leapwriter->make_selection($selection, $ids, 'Grouping');
            }
            $filename = $this->exporter->get('format')->manifest_name();
            $content = $leapwriter->to_xml();
        } else {
            if (count($this->records) == 1) {
                $filename = clean_filename($this->cm->name . '-entry.html');
            } else {
                $filename = clean_filename($this->cm->name . '-full.html');
            }
        }
        return $this->exporter->write_new_file(
            $content,
            $filename,
            ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH) // if we have associate files, this is a 'manifest'
        );
    }

    /**
     * Verify the user can still export this entry
     *
     * @return bool
     */
    public function check_permissions() {
        if ($this->recordid) {
            if (baseline_isowner($this->recordid)) {
                return has_capability('mod/baseline:exportownentry', get_context_instance(CONTEXT_MODULE, $this->cm->id));
            }
            return has_capability('mod/baseline:exportentry', get_context_instance(CONTEXT_MODULE, $this->cm->id));
        }
        if ($this->has_export_config() && !$this->get_export_config('mineonly')) {
            return has_capability('mod/baseline:exportallentries', get_context_instance(CONTEXT_MODULE, $this->cm->id));
        }
        return has_capability('mod/baseline:exportownentry', get_context_instance(CONTEXT_MODULE, $this->cm->id));
    }

    /**
     *  @return string
     */
    public static function display_name() {
        return get_string('modulename', 'baseline');
    }

    /**
     * @global object
     * @return bool|void
     */
    public function __wakeup() {
        global $CFG;
        if (empty($CFG)) {
            return true; // too early yet
        }
        foreach ($this->fieldtypes as $key => $field) {
            require_once($CFG->dirroot . '/mod/baseline/field/' . $field .'/field.class.php');
            $this->fields[$key] = unserialize(serialize($this->fields[$key]));
        }
    }

    /**
     * Prepare a single entry for export, replacing all the content etc
     *
     * @param stdclass $record the entry to export
     *
     * @return array with key 0 = the html content, key 1 = array of attachments
     */
    private function exportentry($record) {
    // Replacing tags
        $patterns = array();
        $replacement = array();
        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);

        $files = array();
    // Then we generate strings to replace for normal tags
        $format = $this->get('exporter')->get('format');
        foreach ($this->fields as $field) {
            $patterns[]='[['.$field->field->name.']]';
            if (is_callable(array($field, 'get_file'))) {
                if (!$file = $field->get_file($record->id)) {
                    $replacement[] = '';
                    continue; // probably left empty
                }
                $replacement[] = $format->file_output($file);
                $this->get('exporter')->copy_existing_file($file);
                $files[] = $file;
            } else {
                $replacement[] = $field->display_browse_field($record->id, 'singletemplate');
            }
        }

    // Replacing special tags (##Edit##, ##Delete##, ##More##)
        $patterns[]='##edit##';
        $patterns[]='##delete##';
        $patterns[]='##export##';
        $patterns[]='##more##';
        $patterns[]='##moreurl##';
        $patterns[]='##user##';
        $patterns[]='##approve##';
        $patterns[]='##comments##';
        $patterns[] = '##timeadded##';
        $patterns[] = '##timemodified##';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = userdate($record->timecreated);
        $replacement[] = userdate($record->timemodified);

        // actual replacement of the tags
        return array(str_ireplace($patterns, $replacement, $this->baseline->singletemplate), $files);
    }

    /**
     * Given the fields being exported, and the single record,
     * work out which export format(s) we can use
     *
     * @param array $fields array of field objects
     * @param object $record The baseline record object
     *
     * @uses PORTFOLIO_FORMAT_PLAINHTML
     * @uses PORTFOLIO_FORMAT_RICHHTML
     *
     * @return array of PORTFOLIO_XX constants
     */
    public static function formats($fields, $record) {
        $formats = array(PORTFOLIO_FORMAT_PLAINHTML);
        $includedfiles = array();
        foreach ($fields as $singlefield) {
            if (is_callable(array($singlefield, 'get_file'))) {
                $includedfiles[] = $singlefield->get_file($record->id);
            }
        }
        if (count($includedfiles) == 1 && count($fields) == 1) {
            $formats = array(portfolio_format_from_mimetype($includedfiles[0]->get_mimetype()));
        } else if (count($includedfiles) > 0) {
            $formats = array(PORTFOLIO_FORMAT_RICHHTML);
        }
        return array($formats, $includedfiles);
    }

    public static function has_files($baseline) {
        global $DB;
        $fieldrecords = $DB->get_records('baseline_fields', array('baselineid' => $baseline->id), 'id');
        // populate objets for this baselines fields
        foreach ($fieldrecords as $fieldrecord) {
            $field = baseline_get_field($fieldrecord, $baseline);
            if (is_callable(array($field, 'get_file'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * base supported formats before we know anything about the export
     */
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_LEAP2A);
    }

    public function has_export_config() {
        // if we're exporting more than just a single entry,
        // and we have the capability to export all entries,
        // then ask whether we want just our own, or all of them
        return (empty($this->recordid) // multi-entry export
            && $this->minecount > 0    // some of them are mine
            && $this->minecount != count($this->records) // not all of them are mine
            && has_capability('mod/baseline:exportallentries', get_context_instance(CONTEXT_MODULE, $this->cm->id))); // they actually have a choice in the matter
    }

    public function export_config_form(&$mform, $instance) {
        if (!$this->has_export_config()) {
            return;
        }
        $mform->addElement('selectyesno', 'mineonly', get_string('exportownentries', 'baseline', (object)array('mine' => $this->minecount, 'all' => count($this->records))));
        $mform->setDefault('mineonly', 1);
    }

    public function get_allowed_export_config() {
        return array('mineonly');
    }
}
