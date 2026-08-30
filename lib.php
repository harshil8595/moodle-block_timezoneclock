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
 * Block timezone clock plugin.
 *
 * @package   block_timezoneclock
 * @copyright 2024 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get icon mapping for font-awesome.
 */
function block_timezoneclock_get_fontawesome_icon_map() {
    global $CFG;
    if ($CFG->branch < 402) {
        return [
            'block_timezoneclock:checked' => 'fa-check-square',
            'block_timezoneclock:unchecked' => 'fa-check-square-o',
        ];
    }
    return [
        'block_timezoneclock:checked' => 'fas fa-square-check',
        'block_timezoneclock:unchecked' => 'far fa-square-check',
    ];
}

function block_timezoneclock_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser) {
    global $PAGE, $OUTPUT;

    /** @var \block_timezoneclock $block */
    $block = block_instance('timezoneclock', null, $PAGE);
    if ($block->check_hiddenonprofile()) {
        return;
    }
    $block->set_showingonprofile(true);

    $widget = new block_timezoneclock\output\main($block, $user);
    $template = $OUTPUT->render_from_template(
        'block_timezoneclock/profileclock',
        $widget->export_for_template($OUTPUT)
    );

    $clockcategory = new core_user\output\myprofile\category(
        'timezoneclock',
        get_string('clock', 'block_timezoneclock'),
        null,
        ' block_timezoneclock'
    );
    $tree->add_category($clockcategory);

    $node = new core_user\output\myprofile\node(
        'timezoneclock',
        'userclock',
        null,
        null,
        null,
        $template
    );
    $tree->add_node($node);
}
