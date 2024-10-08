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

use block_timezoneclock\form\converter;

/**
 * Main timezoneclock rendering class.
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timezoneclock extends block_base {

    /**
     * Initialize block and set block's title
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_timezoneclock');
    }

    /**
     * Allow the block to have a configuration page.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Apply custom block name if inputed
     *
     * @return void
     */
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }
    }

    /**
     * Allow block to add multiple times
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Generates blocks html
     *
     * @return void
     */
    public function get_content() {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $mainblock = new block_timezoneclock\output\main($this);

        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render($mainblock);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Format timestamp according to timezone
     *
     * @param string $tz
     * @param int|null $timestamp
     * @return array
     */
    public static function dateinfo(string $tz, ?int $timestamp = null): array {
        $timestamp = $timestamp ?? time();
        $tz = core_date::normalise_timezone($tz);
        $dateobj = new DateTime();
        $dateobj->setTimezone(new DateTimeZone($tz));
        $dateobj->setTimestamp($timestamp);
        // phpcs:ignore moodle.NamingConventions.ValidVariableName.VariableNameLowerCase
        [$weekday, $month, $day, $year, $hour, $minute, $second, $dayPeriod] = explode(' ', $dateobj->format('D M d o h i s A'));
        $timezone = $tz;
        if ($tz === converter::get_usertimezone()) {
            $timezone = get_string('timezoneuser', 'block_timezoneclock', $tz);
        }

        return compact('timezone', 'tz', 'weekday', 'month', 'day', 'year', 'hour', 'minute', 'second', 'dayPeriod');
    }

    /**
     * Format timestamp according to timezone in batch
     *
     * @param array $timezones
     * @param int|null $timestamp
     * @return array
     */
    public function timezones(array $timezones = [], ?int $timestamp = null): array {
        return array_map(function ($tz) use ($timestamp) {
            return self::dateinfo($tz, $timestamp);
        }, array_unique($timezones));
    }

    /**
     * Show analog clock or not
     *
     * @return bool
     */
    public function is_analog(): bool {
        $clocktype = get_config('block_timezoneclock', 'clocktype');
        if (!empty($this->config->clocktype)) {
            $clocktype = $this->config->clocktype;
        }
        return $clocktype == block_timezoneclock\output\main::TYPEANALOG;
    }

}
