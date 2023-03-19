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

use block_timezoneclock;
use core\output\dynamic_tabs;
use core_date;
use lang_string;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Main timezoneclock class.
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /** @var string */
    const TYPEDIGITAL = 'digital';

    /** @var string */
    const TYPEANALOG = 'analog';

    /**
     * @var block_timezoneclock
     */
    protected $block;

    /**
     * Constructor
     *
     * @param block_timezoneclock $block
     */
    public function __construct(block_timezoneclock $block) {
        $this->block = $block;
    }

    /**
     * Generates data needed for template
     *
     * @param renderer_base $output
     * @return void
     */
    public function export_for_template(renderer_base $output) {
        $usertimezone = core_date::get_user_timezone();
        $tabobject = new dynamic_tabs([
            new timezones(['instanceid' => $this->block->instance->id]),
            new converter(['instanceid' => $this->block->instance->id]),
        ]);

        $context = new stdClass;
        $context->isanalog = $this->block->is_analog();
        $context->showdigits = $this->block->show_digits();
        $context->usertimezone = $this->block->dateinfo($usertimezone);
        $context->tabshtml = $output->render_from_template(
            'core/dynamic_tabs',
            $tabobject->export_for_template($output)
        );
        return $context;
    }

    /**
     * Get clock types
     *
     * @return array
     */
    public static function get_clocktypes() {
        return [
            self::TYPEDIGITAL => new lang_string('typedigital', 'block_timezoneclock'),
            self::TYPEANALOG => new lang_string('typeanalog', 'block_timezoneclock'),
        ];
    }
}
