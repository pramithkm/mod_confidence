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
 * @author  Pramith <eugene@catalyst.net.nz>
 * @package mod_confidence
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Prints a particular instance of confidence for the current user.
 *
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/confidence/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or

if ($id) {
    $cm         = get_coursemodule_from_id('confidence', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $confidence  = $DB->get_record('confidence', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

// Print the page header.
$PAGE->set_url('/mod/confidence/view.php', array('id' => $confidence->id));
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($cm->name));

$record = $DB->get_record('confidence_record', array('confidenceid' => $confidence->id, 'userid' => $USER->id));
$user = $DB->get_record('user', array('id' => $USER->id));

$errmsg = '';
if (!$user) {
    $errmsg = "User not recognized!!";
} else if (!$record) {
    $errmsg = "No records founder under your name!!";
}
// Output starts here.
echo $OUTPUT->header();
// Replace the following lines with you own code.
echo $OUTPUT->heading(format_string($cm->name));
//$renderer = $PAGE->get_renderer('confidence');

if(!empty($errmsg)) {
    echo html_writer::tag('div', $errmsg, array('class' => 'error'));
} else {
    echo html_writer::tag('div', 'Welcome : '. $user->firstname. ' '. $user->lastname);
    echo html_writer::tag('div', 'The level of confidence (%) :' .$record->level);
    echo html_writer::tag('div', 'Completed on: '. date('Y-m-d H:i', $record->timecreated));
}

// Finish the page.
echo $OUTPUT->footer();
