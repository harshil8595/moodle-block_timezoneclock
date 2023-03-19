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
        global $CFG;

        require_once($CFG->libdir . '/formslib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $mainblock = new block_timezoneclock\output\main($this);
        $renderer = $this->page->get_renderer('block_timezoneclock');

        $this->content = new stdClass;
        $this->content->text = $renderer->render($mainblock);
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
        $dateobj = new DateTime();
        $dateobj->setTimezone(new DateTimeZone($tz));
        $dateobj->setTimestamp($timestamp);
        [$day, $month, $date, $year, $hour, $minute, $second, $meridiem] = explode(' ', $dateobj->format('D M d o h i s A'));

        return compact('tz', 'day', 'month', 'date', 'year', 'hour', 'minute', 'second', 'meridiem');
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
        }, array_unique(array_merge($timezones, $this->config->timezone ?? [])));
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

    /**
     * Show digits in analog clock or not
     *
     * @return bool
     */
    public function show_digits(): bool {
        $showdigits = get_config('block_timezoneclock', 'showdigits');
        if (isset($this->config->showdigits)) {
            $showdigits = !empty($this->config->showdigits);
        }
        return !empty($showdigits);
    }

}
