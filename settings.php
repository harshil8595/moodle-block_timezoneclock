<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
/**
 * Block timezoneclock settings.
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $name = 'block_timezoneclock/clocktype';
    $title = get_string('clocktype', 'block_timezoneclock');
    $description = get_string('clocktype_desc', 'block_timezoneclock');
    $setting = new admin_setting_configselect($name, $title, $description,
        block_timezoneclock\output\main::TYPEDIGITAL, block_timezoneclock\output\main::get_clocktypes());
    $settings->add($setting);

    $name = 'block_timezoneclock/showdigits';
    $title = get_string('showdigits', 'block_timezoneclock');
    $description = get_string('showdigits_desc', 'block_timezoneclock');
    $setting = new admin_setting_configselect($name, $title, $description,
        block_timezoneclock\output\main::TYPEDIGITAL, [
            0 => new lang_string('no'),
            1 => new lang_string('yes'),
        ]);
    $settings->add($setting);
}
