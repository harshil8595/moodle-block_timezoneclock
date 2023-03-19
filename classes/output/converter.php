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

namespace block_timezoneclock\output;

use block_timezoneclock\form\converter as FormConverter;
use html_writer;
use lang_string;
use renderer_base;
use stdClass;

/**
 * Generates converter tab html
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class converter extends tabbase {

    /**
     * Define tab title
     *
     * @return string
     */
    public function get_tab_label(): string {
        return new lang_string('timezoneconveter', 'block_timezoneclock');
    }

    /**
     * Generates data needed for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $context = parent::export_for_template($output);
        $context->formclass = FormConverter::class;
        $context->formuniqid = html_writer::random_id('form');
        return $context;
    }
}
