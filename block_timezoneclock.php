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
use block_timezoneclock\util;

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
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $mainblock = new block_timezoneclock\output\main($this);

        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render($mainblock);
        $this->content->footer = '';

        $cssurl = new moodle_url('/blocks/timezoneclock/choices/assets/styles/choices.min.css');
        $cssurl->param('rev', $CFG->themerev);
        $this->page->requires->css($cssurl);

        return $this->content;
    }

    /**
     * Format timestamp according to timezone
     *
     * @param string $tz
     * @param string $format
     * @param bool $showtime
     * @param int|null $timestamp
     * @return array
     */
    public static function dateinfo(string $tz, string $format, bool $showtime, ?int $timestamp = null): array {
        $timestamp = $timestamp ?? time();
        $tz = core_date::normalise_timezone($tz);
        $dateobj = new DateTime();
        $dateobj->setTimezone(new DateTimeZone($tz));
        $dateobj->setTimestamp($timestamp);
        $timezone = $tz;
        if ($tz === converter::get_usertimezone()) {
            $timezone = get_string('timezoneuser', 'block_timezoneclock', $tz);
        }

        $allcharacters = array_merge(util::SUPPORTEDCHARS['date'], util::SUPPORTEDCHARS['time']);
        $timeunitsmap = [
            util::SUPPORTEDCHARS['time'][2] => 'hour',
            util::SUPPORTEDCHARS['time'][3] => 'minute',
            util::SUPPORTEDCHARS['time'][4] => 'second',
            util::SUPPORTEDCHARS['time'][5] => 'dayPeriod',
        ];

        $timeunits = [];
        $namedfractions = $fractions = [];
        $pattern = '/(' . join('|', $allcharacters) . ')/'; // Supported format characters.

        // Split format string into tokens (format parts and separators).
        preg_match_all('/(' . join('|', $allcharacters) . '|[^' . join('', $allcharacters) . ']+)/', $format, $matches);

        if (!$showtime) {
            $matches[0] = array_unique(
                array_merge(
                    $matches[0],
                    array_keys($timeunitsmap)
                )
            );
        }

        foreach ($matches[0] as $token) {
            if (in_array($token, $allcharacters)) {
                $value = $dateobj->format($token);
                $fractions[] = [
                    'fraction' => $token,
                    'value' => $value,
                    'hide' => !in_array($token, util::SUPPORTEDCHARS['date']) && !$showtime,
                ];
                $namedfractions[$token] = $value;
                if (!$showtime && isset($timeunitsmap[$token])) {
                    $timeunits[$timeunitsmap[$token]] = $value;
                }
            } else if ($showtime) {
                // Treat as separator (e.g. space, :, -, /).
                $fractions[] = [
                    'fraction' => 'seperator',
                    'value' => htmlspecialchars($token),
                    'hide' => !empty(end($fractions)['hide']),
                ];
            }
        }

        return compact('timezone', 'tz', 'namedfractions', 'fractions', 'timeunits');
    }

    /**
     * Format timestamp according to timezone in batch
     *
     * @param array $timezones
     * @param int|null $timestamp
     * @return array
     */
    public function timezones(array $timezones = [], ?int $timestamp = null): array {
        $dateinfos = [];
        $timezones = array_unique($timezones);
        $format = $this->get_format();
        $showtime = !$this->is_analog();
        foreach ($timezones as $tz) {
            $dateinfos[] = self::dateinfo($tz, $format, $showtime, $timestamp);
        }
        return $dateinfos;
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
        return $clocktype == util::TYPEANALOG;
    }

    /**
     * Get date format
     *
     * @return string
     */
    public function get_format(): string {
        $format = $this->config->datetimeformat ?? null;
        if (empty($format)) {
            $format = util::DEFAULTFORMAT;
        }
        return $format;
    }
}
