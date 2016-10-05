<?php
/*
 * Copyright (C) 2015 onwards Catalyst IT
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Eugene Venter <eugene@catalyst.net.nz>
 * @package mod_confidence
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class rb_source_confidence_record extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    function __construct() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/confidence/lib.php');

        $this->base = '{confidence_record}';

        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = "Confidence Levels";

        parent::__construct();
    }


    protected function define_joinlist() {
        global $CFG;

        // to get access to constants
        require_once($CFG->dirroot.'/mod/confidence/lib.php');

        $joinlist = array(
            new rb_join(
                'confidence',
                'LEFT',
                '{confidence}',
                'base.confidenceid = confidence.id'
            ),
            new rb_join(
                'userdata',
                'LEFT',
                '{auser}',
                'base.userid = userdata.id'
            ),
            new rb_join(
                'coursedata',
                'LEFT',
                '{course}',
                'confidence.course = coursedata.id'
            )
        );
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'confidence', 'course');
        $this->add_course_category_table_to_joinlist($joinlist, 'coursedata', 'category');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array(
            new rb_column_option(
                'confidence',
                'name',
                'Confidece Name Label',
                'confidence.name',
                array('joins' => 'confidence', 'displayfunc' => 'confidence_link',
                    'extrafields' => array('userid' => 'base.userid', 'confidenceid' => 'base.confidenceid'))
            ),
            new rb_column_option(
                'base',
                'level',
                'Level Label',
                'base.level'
            ),
            new rb_column_option(
                'base',
                'timecreated',
                'Time Recorded Label',
                'base.timecreated',
                array('displayfunc' => 'confidence_time_created',
                        'extrafields' => array ('time' => 'base.timecreated')
                )
            ),
        );

        // include some standard columns
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
            new rb_filter_option(
                'coursedata',
                'name',
                'Course Name',
                'text'
            )
        );

        // include some standard filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array(
        );
        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
        );

        return $defaultfilters;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();
        return $requiredcolumns;
    }

    public function rb_display_confidence_time_created($time) {
        return date('Y-m-d H:i', $time);
    }

}

