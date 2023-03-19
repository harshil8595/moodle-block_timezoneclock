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

use lang_string;
use renderer_base;
use stdClass;

/**
 * Main timezoneclock rendering class.
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class timezones extends tabbase {

    /**
     * @var bool
     */
    protected $blockautoupdate = false;

    /**
     * Sets whether to update time of timezone in js
     *
     * @param bool $blockautoupdate
     * @return self
     */
    public function set_blockautoupdate(bool $blockautoupdate): self {
        $this->blockautoupdate = $blockautoupdate;
        return $this;
    }

    /**
     * Define tab title
     *
     * @return string
     */
    public function get_tab_label(): string {
        return new lang_string('additionaltimezones', 'block_timezoneclock');
    }

    /**
     * Generates data needed for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $context = parent::export_for_template($output);
        $context->additionaltimezones = $this->get_block()->timezones(
            $this->data['timezones'] ?? [],
            $this->data['timestamp'] ?? null,
        );
        $context->blockautoupdate = $this->blockautoupdate;
        return $context;
    }
}
