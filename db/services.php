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
 * Web service for Timezoneclock block
 *
 * @package block_timezoneclock
 * @copyright 2024 Harshil Patel <harshil8595@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'block_timezoneclock_dynamic_form' => [
        'classname' => 'block_timezoneclock\external\formsubmit',
        'methodname' => 'process',
        'description' => 'Process submission of a dynamic (modal) form',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => false,
    ],
];
