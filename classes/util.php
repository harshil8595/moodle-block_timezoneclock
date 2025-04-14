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

use lang_string;

/**
 * helprt for defining constant
 *
 * @package   block_timezoneclock
 * @copyright 2025 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {

    /**
     * @var array List of supported date time characters
     */
    const SUPPORTEDCHARS = [
        'date' => ['Y', 'y', 'm', 'n', 'F', 'M', 'd', 'j', 'D', 'l'],
        'time' => ['H', 'h', 'g', 'i', 's', 'A', 'a'],
    ];

    /**
     * @var string Default date format
     */
    const DEFAULTFORMAT = 'D F d Y h:i:s A';

    /** @var string */
    const TYPEDIGITAL = 'digital';

    /** @var string */
    const TYPEANALOG = 'analog';

    /**
     * Get clock types
     *
     * @return array
     */
    public static function get_clocktypes() {
        return [
            static::TYPEDIGITAL => new lang_string('typedigital', 'block_timezoneclock'),
            static::TYPEANALOG => new lang_string('typeanalog', 'block_timezoneclock'),
        ];
    }

}
