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
 * @author  Pramith Dayananda <pramithd@catalyst.net.nz>
 * @package mod_confidence
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates API features that the forum supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function confidence_supports($feature) {
    switch($feature) {
        case FEATURE_COMPLETION_HAS_RULES: return true;
        default: return null;
    }
}

/**
 * Library of interface functions and constants for module confidence
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the confidence specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 */
 function confidence_add_instance(stdClass $confidence, mod_confidence_mod_form $mform = null) {
    global $DB;

    $confidence->timecreated = time();

    // You may have to add extra stuff in here.

    $confidence->id = $DB->insert_record('confidence', $confidence);

    return $confidence->id;
}


/**
 * Removes an instance of the confidence from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function confidence_delete_instance($id) {
    global $DB;

    if (!$conf = $DB->get_record('confidence', array('id' => $id))) {
        return false;
    }

    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('confidence', array('id' => $conf->id));

    $transaction->allow_commit();

    return true;
}


/**
 * Updates an instance of the ojt in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $ojt An object from the form in mod_form.php
 * @param mod_ojt_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function confidence_update_instance(stdClass $confidence, mod_confidence_mod_form $mform = null) {
    global $DB;

    $confidence->timemodified = time();
    $confidence->id = $confidence->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('confidence', $confidence);

    return $result;
}


/**
 * Overwrites the content for the course module for confidence
 * Append the form for user input
 *
 * @param cm_info $cm
 */
function confidence_cm_info_view(cm_info $cm) {
    global $DB, $USER, $PAGE, $CFG;
    $PAGE->requires->js('/mod/confidence/confidence.js');

    // Get level from db for the logged in user
    $coninfo = $DB->get_record('confidence_record', array('confidenceid'=> $cm->instance, 'userid' => $USER->id ));
    $defaultval = 0;
    $readonly = '';
    if($coninfo) {
        $readonly = 'readonly';
        $defaultval= $coninfo->level;
    }
    // Confidence level form
    $identifier = $cm->modname.'_'.$cm->instance;
    $output = '<div class="confidence_module">';
    $output .= '<form method="POST" action="'.$CFG->wwwroot.'/mod/confidence/confidence.php">';
    $output .= '<input '.$readonly.'  name="'.$identifier.'" class="confidence-slider"
                    id="'.$identifier.'" data-instance="'.$cm->instance.'" type="range" min="0" max="100" value="'.$defaultval.'" />';
    $output .= '<input id="def'.$cm->instance.'" name="level" type="hidden" value="'.$defaultval.'" />';
    $output .= '<input name="instance" type="hidden" value="'.$cm->instance.'" />';
    $sub_btn = '<input name="confidence_submit_'.$cm->instance.'" id="confidence_submit_'.$cm->instance.'" class="noscript" type="submit" />';

    if(!$readonly) {
        $output .= $sub_btn;
    }
    $output .= '</form>';
    $output .= '<div class="confidence-value confidence_value_'.$cm->instance.'">'.$defaultval.'</div>';
    $output .= '<div class="confidence-msg confidence_message_'.$cm->instance.'"></div>';
    $output .= '</div>';

    $cm->set_content($output);
}


/**
 * Obtains the completion progress.
 *
 * @param object $cm      Course-module
 * @param int    $userid  User ID
 * @return string The current status of completion for the user
 */
function confidence_get_completion_progress($cm, $userid) {
    global $DB;

    // Get completion record
    $confidence = $DB->get_record('confidence', array('id' => $cm->instance), '*', MUST_EXIST);
    $result = array();
    if ($confidence->completiontrack) {
        $result[] = get_string('completiontrack', 'confidence');
    }

    return $result;

}


/**
 * Obtains the automatic completion state for this feedback based on the condition
 * in feedback settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function confidence_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $confidence = $DB->get_record('confidence', array('id' => $cm->instance), '*', MUST_EXIST);

    /*if($confidence->completiontrack == 1) {
        return $type;
    }*/

    return $DB->record_exists_select('confidence_record', 'confidenceid = ? AND userid = ?', array($cm->instance, $userid));
}


/**
 * Obtains the specific requirements for completion.
 *
 * @param object $cm Course-module
 * @return array Requirements for completion
 */
function confidence_get_completion_requirements($cm) {
    global $DB;

    $confidence = $DB->get_record('confidence', array('id' => $cm->instance));
    $result = array();

    if ($confidence->completiontrack) {
        $result[] = get_string('completiontrack', 'confidence');
    }
    return $result;
}


/**
 * Print a detailed representation of what a  user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $feedback
 * @return bool
 */
function confidence_user_complete($course, $user, $mod, $ojt) {
}
