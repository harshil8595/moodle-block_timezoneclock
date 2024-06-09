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
use core\output\dynamic_tabs\base;
use renderer_base;
use stdClass;

/**
 * Base class for generating tabobject.
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tabbase extends base {

    /**
     * @var block_timezoneclock
     */
    protected $blockinstance;

    /**
     * Make tab available to all users
     *
     * @return bool
     */
    public function is_available(): bool {
        return true;
    }

    /**
     * Get/make block instance by id
     *
     * @return block_timezoneclock
     */
    public function get_block(): block_timezoneclock {
        if (is_null($this->blockinstance)) {
            $this->blockinstance = block_instance_by_id($this->data['instanceid']);
        }
        return $this->blockinstance;
    }

    /**
     * Get class name
     */
    public function get_classname(): string {
        $parts = preg_split('/\\\\/', static::class);
        return array_pop($parts);
    }

    /**
     * HTML "id" attribute that should be used for this tab, by default the last part of class name
     *
     * @return string
     */
    public function get_tab_id(): string {
        $tabid = sprintf('%s-%d', $this->get_classname(), $this->data['instanceid']);
        return $tabid;
    }

    /**
     * Get template name to render
     *
     * @return string
     */
    public function get_template(): string {
        return "block_timezoneclock/{$this->get_classname()}";
    }

    /**
     * Generates data needed for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $context = new stdClass;
        $context->isanalog = $this->get_block()->is_analog();
        return $context;
    }
}
