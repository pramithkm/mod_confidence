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
 * SCORM module library functions tests
 *
 * @package    mod_scorm
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Totara 2.9.7
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/scorm/lib.php');

/**
 * SCORM module library functions tests
 *
 * @package    mod_scorm
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Totara 2.9.7
 */
class mod_scorm_lib_testcase extends externallib_advanced_testcase {

    /**
     * Set up for every test
     */
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Setup test data.
        $this->course = $this->getDataGenerator()->create_course(array('enablecompletion' => true));
        $this->scorm = $this->getDataGenerator()->create_module('scorm', array('course' => $this->course->id));
        $this->context = context_module::instance($this->scorm->cmid);
        $this->cm = get_coursemodule_from_instance('scorm', $this->scorm->id);

        // Create users.
        $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

        // Users enrolments.
        $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    /**
     * Test scorm_get_availability_status and scorm_require_available
     * @return void
     */
    public function test_scorm_check_and_require_available() {
        global $DB;

        // Set to the student user.
        self::setUser($this->student);

        // Usual case.
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // SCORM not open.
        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // SCORM closed.
        $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // SCORM not open and closed.
        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // Now additional checkings with different parameters values.
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // SCORM not open.
        $this->scorm->timeopen = time() + DAYSECS;
        $this->scorm->timeclose = 0;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // SCORM closed.
        $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // SCORM not open and closed.
        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // As teacher now.
        self::setUser($this->teacher);

        // SCORM not open and closed.
        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // Now, we use the special capability.
        // SCORM not open and closed.
        $this->scorm->timeopen = time() + DAYSECS;
        list($status, $warnings) = scorm_get_availability_status($this->scorm, true, $this->context);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // Check exceptions does not broke anything.
        scorm_require_available($this->scorm, true, $this->context);
        // Now, expect exceptions.
        $this->setExpectedException('moodle_exception', get_string("notopenyet", "scorm", userdate($this->scorm->timeopen)));

        // Now as student other condition.
        self::setUser($this->student);
        $this->scorm->timeopen = 0;
        $this->scorm->timeclose = time() - DAYSECS;

        $this->setExpectedException('moodle_exception', get_string("expired", "scorm", userdate($this->scorm->timeclose)));
        scorm_require_available($this->scorm, false);
    }

    /**
     * Test scorm_get_last_completed_attempt
     *
     * @return void
     */
    public function test_scorm_get_last_completed_attempt() {
        $this->assertEquals(1, scorm_get_last_completed_attempt($this->scorm->id, $this->student->id));
    }

    /**
     * Test scorm print overview.
     */
    public function test_scorm_print_overview() {
        global $CFG;

        $this->resetAfterTest();

        $CFG->enablecompletion = true;

        // Delete the existing scorm.
        $this->assertNull(course_delete_module($this->cm->id));

        // Create and delete a label.
        $label = $this->getDataGenerator()->create_module('label', array('course' => $this->course->id));
        $this->assertNull(course_delete_module($label->cmid));

        // Create a new scorm activity.
        $this->getDataGenerator()->create_module('scorm', array(
            'course' => $this->course->id,
            'completion' => COMPLETION_TRACKING_MANUAL
        ));

        scorm_print_overview(array($this->course->id => $this->course), $details);
        $this->assertInternalType('array', $details);
        $this->assertCount(1, $details);
        $scormdetails = reset($details);
        $this->assertInternalType('array', $scormdetails);
        $this->assertCount(1, $scormdetails);
        $this->assertArrayHasKey('scorm', $scormdetails);
        $this->assertContains('SCORM package:', $scormdetails['scorm']);
    }
}
