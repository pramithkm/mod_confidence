<?php

defined('MOODLE_INTERNAL') || die();
require_once ($CFG->libdir.'/formslib.php');

class mod_confidence_confidence_form extends moodleform {

    /**
     * Defines form elements
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        // Type range not available, further development needed
        $mform->addElement('text', 'range', 'Email Address');
        $mform->setType('slider', PARAM_NOTAGS);
    }
}
