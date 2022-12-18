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
        global $USER;

        $choices = core_date::get_list_of_timezones($USER->timezone, true);
        $repeatarray[] = $mform->createElement('searchableselector', 'config_timezone', get_string('timezone'), $choices);
        $repeatedoptions['timezone']['type'] = PARAM_INT;

        $norepeats = !empty($this->block->config->timezone) ? count($this->block->config->timezone) : 1;
        $this->repeat_elements($repeatarray, $norepeats, $repeatedoptions, 'tz_repeats', 'tz_add', 1);

    }

}
