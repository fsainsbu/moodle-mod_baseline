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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_baseline_activity_task
 */

/**
 * Structure step to restore one baseline activity
 */
class restore_baseline_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('baseline', '/activity/baseline');
        $paths[] = new restore_path_element('baseline_field', '/activity/baseline/fields/field');
        if ($userinfo) {
            $paths[] = new restore_path_element('baseline_record', '/activity/baseline/records/record');
            $paths[] = new restore_path_element('baseline_content', '/activity/baseline/records/record/contents/content');
            $paths[] = new restore_path_element('baseline_rating', '/activity/baseline/records/record/ratings/rating');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_baseline($baseline) {
        global $DB;

        $baseline = (object)$baseline;
        $oldid = $baseline->id;
        $baseline->course = $this->get_courseid();

        $baseline->timeavailablefrom = $this->apply_date_offset($baseline->timeavailablefrom);
        $baseline->timeavailableto = $this->apply_date_offset($baseline->timeavailableto);
        $baseline->timeviewfrom = $this->apply_date_offset($baseline->timeviewfrom);
        $baseline->timeviewto = $this->apply_date_offset($baseline->timeviewto);
        $baseline->assesstimestart = $this->apply_date_offset($baseline->assesstimestart);
        $baseline->assesstimefinish = $this->apply_date_offset($baseline->assesstimefinish);

        if ($baseline->scale < 0) { // scale found, get mapping
            $baseline->scale = -($this->get_mappingid('scale', abs($baseline->scale)));
        }

        // Some old backups can arrive with baseline->notification = null (MDL-24470)
        // convert them to proper column default (zero)
        if (is_null($baseline->notification)) {
            $baseline->notification = 0;
        }

        // insert the baseline record
        $newitemid = $DB->insert_record('baseline', $baseline);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_baseline_field($baseline) {
        global $DB;

        $baseline = (object)$baseline;
        $oldid = $baseline->id;

        $baseline->baselineid = $this->get_new_parentid('baseline');

        // insert the baseline_fields record
        $newitemid = $DB->insert_record('baseline_fields', $baseline);
        $this->set_mapping('baseline_field', $oldid, $newitemid, false); // no files associated
    }

    protected function process_baseline_record($baseline) {
        global $DB;

        $baseline = (object)$baseline;
        $oldid = $baseline->id;

        $baseline->timecreated = $this->apply_date_offset($baseline->timecreated);
        $baseline->timemodified = $this->apply_date_offset($baseline->timemodified);

        $baseline->userid = $this->get_mappingid('user', $baseline->userid);
        $baseline->groupid = $this->get_mappingid('group', $baseline->groupid);
        $baseline->baselineid = $this->get_new_parentid('baseline');

        // insert the baseline_records record
        $newitemid = $DB->insert_record('baseline_records', $baseline);
        $this->set_mapping('baseline_record', $oldid, $newitemid, false); // no files associated
    }

    protected function process_baseline_content($baseline) {
        global $DB;

        $baseline = (object)$baseline;
        $oldid = $baseline->id;

        $baseline->fieldid = $this->get_mappingid('baseline_field', $baseline->fieldid);
        $baseline->recordid = $this->get_new_parentid('baseline_record');

        // insert the baseline_content record
        $newitemid = $DB->insert_record('baseline_content', $baseline);
        $this->set_mapping('baseline_content', $oldid, $newitemid, true); // files by this itemname
    }

    protected function process_baseline_rating($baseline) {
        global $DB;

        $baseline = (object)$baseline;

        // Cannot use ratings API, cause, it's missing the ability to specify times (modified/created)
        $baseline->contextid = $this->task->get_contextid();
        $baseline->itemid    = $this->get_new_parentid('baseline_record');
        if ($baseline->scaleid < 0) { // scale found, get mapping
            $baseline->scaleid = -($this->get_mappingid('scale', abs($baseline->scaleid)));
        }
        $baseline->rating = $baseline->value;
        $baseline->userid = $this->get_mappingid('user', $baseline->userid);
        $baseline->timecreated = $this->apply_date_offset($baseline->timecreated);
        $baseline->timemodified = $this->apply_date_offset($baseline->timemodified);

        // We need to check that component and ratingarea are both set here.
        if (empty($baseline->component)) {
            $baseline->component = 'mod_baseline';
        }
        if (empty($baseline->ratingarea)) {
            $baseline->ratingarea = 'entry';
        }

        $newitemid = $DB->insert_record('rating', $baseline);
    }

    protected function after_execute() {
        global $DB;
        // Add baseline related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_baseline', 'intro', null);
        // Add content related files, matching by itemname (baseline_content)
        $this->add_related_files('mod_baseline', 'content', 'baseline_content');
        // Adjust the baseline->defaultsort field
        if ($defaultsort = $DB->get_field('baseline', 'defaultsort', array('id' => $this->get_new_parentid('baseline')))) {
            if ($defaultsort = $this->get_mappingid('baseline_field', $defaultsort)) {
                $DB->set_field('baseline', 'defaultsort', $defaultsort, array('id' => $this->get_new_parentid('baseline')));
            }
        }
    }
}
