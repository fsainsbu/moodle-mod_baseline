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
 * Provides support for the conversion of moodle1 backup to the moodle2 format
 * Based off of a template @ http://docs.moodle.org/dev/Backup_1.9_conversion_for_developers
 *
 * @package    mod
 * @subpackage baseline
 * @copyright  2011 Aparup Banerjee <aparup@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Baselinebase conversion handler
 */
class moodle1_mod_baseline_handler extends moodle1_mod_handler {

    /** @var moodle1_file_manager */
    protected $fileman = null;

    /** @var int cmid */
    protected $moduleid = null;

    /**
     * Declare the paths in moodle.xml we are able to convert
     *
     * The method returns list of {@link convert_path} instances.
     * For each path returned, the corresponding conversion method must be
     * defined.
     *
     * Note that the path /MOODLE_BACKUP/COURSE/MODULES/MOD/DATA does not
     * actually exist in the file. The last element with the module name was
     * appended by the moodle1_converter class.
     *
     * @return array of {@link convert_path} instances
     */
    public function get_paths() {
        return array(
            new convert_path('baseline', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DATA',
                        array(
                            'newfields' => array(
                                'introformat' => 0,
                                'assesstimestart' => 0,
                                'assesstimefinish' => 0,
                            )
                        )
                    ),
            new convert_path('baseline_field', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DATA/FIELDS/FIELD')
        );
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/DATA
     * baseline available
     */
    public function process_baseline($baseline) {
        global $CFG;

        // get the course module id and context id
        $instanceid     = $baseline['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

        // replay the upgrade step 2007101512
        if (!array_key_exists('asearchtemplate', $baseline)) {
            $baseline['asearchtemplate'] = null;
        }

        // replay the upgrade step 2007101513
        if (is_null($baseline['notification'])) {
            $baseline['notification'] = 0;
        }

        // conditionally migrate to html format in intro
        if ($CFG->texteditors !== 'textarea') {
            $baseline['intro'] = text_to_html($baseline['intro'], false, false, true);
            $baseline['introformat'] = FORMAT_HTML;
        }

        // get a fresh new file manager for this instance
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_baseline');

        // convert course files embedded into the intro
        $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $baseline['intro'] = moodle1_converter::migrate_referenced_files($baseline['intro'], $this->fileman);

        // @todo: user baseline - upgrade content to new file storage

        // add 'export' tag to list and single template.
        $pattern = '/\#\#delete\#\#(\s+)\#\#approve\#\#/';
        $replacement = '##delete##$1##approve##$1##export##';
        $baseline['listtemplate'] = preg_replace($pattern, $replacement, $baseline['listtemplate']);
        $baseline['singletemplate'] = preg_replace($pattern, $replacement, $baseline['singletemplate']);

        //@todo: user baseline - move baseline comments to comments table
        //@todo: user baseline - move baseline ratings to ratings table

        // start writing baseline.xml
        $this->open_xml_writer("activities/baseline_{$this->moduleid}/baseline.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'baseline', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('baseline', array('id' => $instanceid));

        foreach ($baseline as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('fields');

        return $baseline;
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/DATA/FIELDS/FIELD
     * baseline available
     */
    public function process_baseline_field($baseline) {
        // process baseline fields
        $this->write_xml('field', $baseline, array('/field/id'));
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/DATA/RECORDS/RECORD
     * baseline available
     */
    public function process_baseline_record($baseline) {
        //@todo process user baseline, and define the convert path in get_paths() above.
        //$this->write_xml('record', $baseline, array('/record/id'));
    }

    /**
     * This is executed when we reach the closing </MOD> tag of our 'baseline' path
     */
    public function on_baseline_end() {
        // finish writing baseline.xml
        $this->xmlwriter->end_tag('fields');
        $this->xmlwriter->end_tag('baseline');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        // write inforef.xml
        $this->open_xml_writer("activities/baseline_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
