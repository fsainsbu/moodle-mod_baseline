<?php // $Id: pagelib.php,v 1.10 2007/07/05 04:41:07 mattc-catalyst Exp $

require_once($CFG->libdir.'/pagelib.php');
require_once($CFG->dirroot.'/course/lib.php'); // needed for some blocks

define('PAGE_BASELINE_VIEW',   'mod-baseline-view');
define('PAGE_BASELINE_BVIEW',   'mod-baseline-bview');
define('PAGE_BASELINE_SUMMARY',   'mod-baseline-summary');
define('PAGE_BASELINE_OSUMMARY',   'mod-baseline-osummary');

page_map_class(PAGE_BASELINE_VIEW, 'page_baseline');
page_map_class(PAGE_BASELINE_SUMMARY, 'pages_baseline');
page_map_class(PAGE_BASELINE_OSUMMARY, 'pageos_baseline');
page_map_class(PAGE_BASELINE_BVIEW, 'pageb_baseline');

$DEFINEDPAGES = array(PAGE_BASELINE_VIEW,PAGE_BASELINE_BVIEW,PAGE_BASELINE_SUMMARY,PAGE_BASELINE_OSUMMARY);
/*
*/

/**
 * Class that models the behavior of a baseline
 *
 * @author Jon Papaioannou
 * @package pages
 */

class page_baseline extends page_generic_activity {

    function init_quick($baseline) {
        if(empty($baseline->pageid)) {
            error('Cannot quickly initialize page: empty course id');
        }
        $this->activityname = 'baseline';
        parent::init_quick($baseline);
    }

    function print_header($title, $morenavlinks = NULL, $meta) {
        parent::print_header($title, $morenavlinks, '', $meta);
    }

    function get_type() {
        return PAGE_BASELINE_VIEW;
    }
}
class pageb_baseline extends page_generic_activity {

    function init_quick($baseline) {
        if(empty($baseline->pageid)) {
            error('Cannot quickly initialize page: empty course id');
        }
        $this->activityname = 'baseline';
        parent::init_quick($baseline);
    }

    function print_header($title, $morenavlinks = NULL, $meta) {
        parent::print_header($title, $morenavlinks, '', $meta);
    }

    function get_type() {
        return PAGE_BASELINE_BVIEW;
    }
}
class pages_baseline extends page_generic_activity {

    function init_quick($baseline) {
        if(empty($baseline->pageid)) {
            error('Cannot quickly initialize page: empty course id');
        }
        $this->activityname = 'baseline';
        parent::init_quick($baseline);
    }

    function print_header($title, $morenavlinks = NULL, $meta) {
        parent::print_header($title, $morenavlinks, '', $meta);
    }

    function get_type() {
        return PAGE_BASELINE_SUMMARY;
    }
}
class pageos_baseline extends page_generic_activity {

    function init_quick($baseline) {
        if(empty($baseline->pageid)) {
            error('Cannot quickly initialize page: empty course id');
        }
        $this->activityname = 'baseline';
        parent::init_quick($baseline);
    }

    function print_header($title, $morenavlinks = NULL, $meta) {
        parent::print_header($title, $morenavlinks, '', $meta);
    }

    function get_type() {
        return PAGE_BASELINE_OSUMMARY;
    }


}




?>
