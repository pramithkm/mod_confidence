<?php

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('url/popupheight',
        "String1", "String2", 450, PARAM_INT, 7));

}
