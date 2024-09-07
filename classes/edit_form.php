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

namespace block_timezoneclock;

use block_edit_form;
use core_date;
use MoodleQuickForm;

/**
 * Form for editing block_timezoneclock instances.
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @property-read \block_timezoneclock $block
 */
class edit_form extends block_edit_form {

    /**
     * Define block specific form elements
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function specific_definition($mform) {
        global $PAGE, $USER;

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_timezoneclock'),
                ['placeholder' => get_string('configtitle_placeholder', 'block_timezoneclock')]);
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('select', 'config_clocktype', get_string('clocktype', 'block_timezoneclock'),
                output\main::get_clocktypes());
        $mform->setType('config_clocktype', PARAM_ALPHA);
        $mform->setDefault('config_clocktype', get_config('block_timezoneclock', 'clocktype'));

        $choices = core_date::get_list_of_timezones($USER->timezone, true);
        $mform->addElement('select', 'config_timezone', get_string('preferred_timezones', 'block_timezoneclock'), $choices,
            ['multiple' => true, 'data-selectenhanced' => 1]);
        $mform->setType('timezone', PARAM_TIMEZONE);

        $PAGE->requires->js_call_amd('block_timezoneclock/main', 'makeSelectEnhanced');
    }

    /**
     * supplies config to save in block instance
     *
     * @return object
     */
    public function get_data() {
        $data = parent::get_data();
        if (is_null($data)) {
            return $data;
        }
        if (!empty($data->config_timezone)) {
            $data->config_timezone = array_values(array_filter($data->config_timezone, 'trim'));
        } else {
            $data->config_timezone = [];
        }
        return $data;
    }

}
