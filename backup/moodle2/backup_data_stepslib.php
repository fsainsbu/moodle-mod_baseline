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
 * Define all the backup steps that will be used by the backup_baseline_activity_task
 */

/**
 * Define the complete baseline structure for backup, with file and id annotations
 */
class backup_baseline_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $baseline = new backup_nested_element('baseline', array('id'), array(
            'name', 'intro', 'introformat', 'comments',
            'timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto',
            'requiredentries', 'requiredentriestoview', 'maxentries', 'rssarticles',
            'singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
            'addtemplate', 'rsstemplate', 'rsstitletemplate', 'csstemplate',
            'jstemplate', 'asearchtemplate', 'approval', 'scale',
            'assessed', 'assesstimestart', 'assesstimefinish', 'defaultsort',
            'defaultsortdir', 'editany', 'notification'));

        $fields = new backup_nested_element('fields');

        $field = new backup_nested_element('field', array('id'), array(
            'type', 'name', 'description', 'param1', 'param2',
            'param3', 'param4', 'param5', 'param6',
            'param7', 'param8', 'param9', 'param10'));

        $records = new backup_nested_element('records');

        $record = new backup_nested_element('record', array('id'), array(
            'userid', 'groupid', 'timecreated', 'timemodified',
            'approved'));

        $contents = new backup_nested_element('contents');

        $content = new backup_nested_element('content', array('id'), array(
            'fieldid', 'content', 'content1', 'content2',
            'content3', 'content4'));

        $ratings = new backup_nested_element('ratings');

        $rating = new backup_nested_element('rating', array('id'), array(
            'component', 'ratingarea', 'scaleid', 'value', 'userid', 'timecreated', 'timemodified'));

        // Build the tree
        $baseline->add_child($fields);
        $fields->add_child($field);

        $baseline->add_child($records);
        $records->add_child($record);

        $record->add_child($contents);
        $contents->add_child($content);

        $record->add_child($ratings);
        $ratings->add_child($rating);

        // Define sources
        $baseline->set_source_table('baseline', array('id' => backup::VAR_ACTIVITYID));

        $field->set_source_sql('
            SELECT *
              FROM {baseline_fields}
             WHERE baselineid = ?',
            array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $record->set_source_table('baseline_records', array('baselineid' => backup::VAR_PARENTID));

            $content->set_source_table('baseline_content', array('recordid' => backup::VAR_PARENTID));

            $rating->set_source_table('rating', array('contextid'  => backup::VAR_CONTEXTID,
                                                      'itemid'     => backup::VAR_PARENTID,
                                                      'component'  => backup_helper::is_sqlparam('mod_baseline'),
                                                      'ratingarea' => backup_helper::is_sqlparam('entry')));
            $rating->set_source_alias('rating', 'value');
        }

        // Define id annotations
        $baseline->annotate_ids('scale', 'scale');

        $record->annotate_ids('user', 'userid');
        $record->annotate_ids('group', 'groupid');

        $rating->annotate_ids('scale', 'scaleid');
        $rating->annotate_ids('user', 'userid');

        // Define file annotations
        $baseline->annotate_files('mod_baseline', 'intro', null); // This file area hasn't itemid
        $content->annotate_files('mod_baseline', 'content', 'id'); // By content->id

        // Return the root element (baseline), wrapped into standard activity structure
        return $this->prepare_activity_structure($baseline);
    }
}
