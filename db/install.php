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
 * This file sets up installation steps
 *
 * @package block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add the timezone clock block to the index page at time of installation
 */
function xmldb_block_timezoneclock_install() {
    $page = new moodle_page();
    $systemcontext = context_system::instance();
    $page->set_context($systemcontext);
    $page->blocks->add_region('content');
    $page->blocks->add_block('timezoneclock', 'content', 0, false, 'blocks-timezoneclock-index', null);
}
