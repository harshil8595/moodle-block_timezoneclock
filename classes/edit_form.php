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
use lang_string;
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
        global $USER;

        $mform->addElement('select', 'config_clocktype', get_string('clocktype', 'block_timezoneclock'),
                output\main::get_clocktypes());
        $mform->setType('config_clocktype', PARAM_ALPHA);
        $mform->setDefault('config_clocktype', get_config('block_timezoneclock', 'clocktype'));

        $mform->addElement('select', 'config_showdigits', get_string('showdigits', 'block_timezoneclock'), [
            0 => new lang_string('no'),
            1 => new lang_string('yes'),
        ]);
        $mform->setType('config_showdigits', PARAM_INT);
        $mform->setDefault('config_showdigits', get_config('block_timezoneclock', 'showdigits'));
        $mform->hideIf('config_showdigits', 'config_clocktype', 'neq', output\main::TYPEANALOG);

        $choices = core_date::get_list_of_timezones($USER->timezone, true);
        $timezoneelement = $mform->createElement('autocomplete', 'config_timezone', get_string('timezone'), $choices);
        $timezoneelement->removeAttribute('id');
        $repeatarray[] = $timezoneelement;
        $repeatedoptions['config_timezone']['type'] = PARAM_TIMEZONE;

        $norepeats = !empty($this->block->config->timezone) ? count($this->block->config->timezone) : 1;
        $this->repeat_elements($repeatarray, $norepeats, $repeatedoptions, 'tz_repeats', 'tz_add', 1);

    }

    /**
     * supplies config to save in block instance
     *
     * @return object
     */
    public function get_data() {
        $data = parent::get_data();
        if (!empty($data->config_timezone)) {
            $data->config_timezone = array_values(array_filter($data->config_timezone, 'trim'));
        }
        return $data;
    }

}
