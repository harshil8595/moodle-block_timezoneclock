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

namespace block_timezoneclock\form;

use block_timezoneclock;
use block_timezoneclock\output\main;
use context;
use context_block;
use core_date;
use core_form\dynamic_form;
use html_writer;
use moodle_url;

/**
 * Form for converting timestamps
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class converter extends dynamic_form {

    /**
     * User's timezone
     *
     * @var string
     */
    protected static $usertimezone;

    /**
     * block instance
     *
     * @var block_timezoneclock
     */
    protected $blockinstance;

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $OUTPUT, $USER;
        $mform = $this->_form;

        $this->set_display_vertical();
        $mform->updateAttributes(['class' => $mform->getAttribute('class') . ' block_timezoneclockconverterform']);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $this->get_contextid());

        $mform->addElement('header', 'formheader', get_string('timezoneconveter', 'block_timezoneclock'));
        $mform->setExpanded('formheader', NO_MOODLE_COOKIES);

        $usertimezone = self::get_usertimezone();
        $timezonelist = core_date::get_list_of_timezones();
        $timezoneoptions = ['GMT' => core_date::get_localised_timezone('GMT')];
        if (!NO_MOODLE_COOKIES) {
            $timezoneoptions += [$usertimezone => get_string('timezoneuser', 'block_timezoneclock', $usertimezone)];
        }
        $timezoneoptions += $timezonelist;
        $mform->addElement('select', 'timezone', get_string('fromtimezone', 'block_timezoneclock'), $timezoneoptions,
            ['data-selectenhanced' => 1]);
        $mform->setType('timezone', PARAM_TIMEZONE);

        $servertimezonelist = core_date::get_list_of_timezones($USER->timezone, true);
        $mform->addElement('select', 'timezones', get_string('totimezones', 'block_timezoneclock'), $servertimezonelist,
            ['multiple' => true, 'data-selectenhanced' => 1]);
        $mform->setType('timezones', PARAM_NOTAGS);

        $groupels[] = $mform->createElement('date_time_selector', 'selectedstamp', get_string('datestamp', 'block_timezoneclock'),
            ['optional' => true, 'timezone' => $this->optional_param('timezone', $usertimezone, PARAM_NOTAGS)]);
        $mform->setType('selectedstamp', PARAM_INT);

        $groupels[] = $mform->createElement('advcheckbox', 'toggletimestamp', null,
            html_writer::span($OUTPUT->pix_icon('checked', null, 'block_timezoneclock').
            $OUTPUT->pix_icon('unchecked', null, 'block_timezoneclock', ['class' => 'font-weight-light']), null,
            ['title' => get_string('toggletimeinput', 'block_timezoneclock'), 'data-toggle' => 'tooltip']));
        $mform->setType('toggletimestamp', PARAM_INT);

        $groupels[] = $mform->createElement('text', 'timestamp', get_string('timestamp', 'block_timezoneclock'),
            'size="10" maxlength="10" minlength="10" pattern="[0-9]{10}" '.
            'placeholder="' . get_string('timestamp_placeholder', 'block_timezoneclock') . '"');
        $mform->setType('timestamp', PARAM_INT);

        $mform->addGroup($groupels, 'timeinput', get_string('datestamp', 'block_timezoneclock'), null, false);

        $mform->disabledIf('toggletimestamp', 'selectedstamp[enabled]', 'notchecked');
        $mform->disabledIf('timestamp', 'selectedstamp[enabled]', 'notchecked');
        $mform->hideIf('timestamp', 'toggletimestamp', 'notchecked');

        $mform->addElement('submit', 'submitbutton', get_string('convert', 'block_timezoneclock'));
    }

    /**
     * Get block instanceid
     *
     * @return int
     */
    private function get_contextid(): int {
        return $this->optional_param('contextid', 0, PARAM_INT);
    }

    /**
     * Get context for form from block instanceid
     *
     * @return context_block
     */
    protected function get_context_for_dynamic_submission(): context {
        return context::instance_by_id($this->get_contextid());
    }

    /**
     * Checks user loggedin or not
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!NO_MOODLE_COOKIES) {
            require_login();
        }
    }

    /**
     * Process form and generates timezones list
     *
     * @return void
     */
    public function process_dynamic_submission() {
        global $PAGE, $OUTPUT;
        $formdata = $this->get_data();
        $selectedtimezones = (array) $formdata->timezones;
        if (!NO_MOODLE_COOKIES) {
            array_unshift($selectedtimezones, self::get_usertimezone());
        } else if ($this->optional_param('firstload', null, PARAM_BOOL)) {
            if (empty($selectedtimezones)) {
                $selectedtimezones = array_values(core_date::get_list_of_timezones());
            }
        }

        $selectedtimestamp = !empty($formdata->selectedstamp) ? $formdata->selectedstamp : null;
        $main = new main($this->get_block());
        $context = (object) $main->export_for_template($OUTPUT);
        $context->blockautoupdate = !empty($selectedtimestamp);
        $context->additionaltimezones = $this->get_block()->timezones($selectedtimezones, $selectedtimestamp);
        if (!empty($selectedtimestamp)) {
            $context->contexttitle = $OUTPUT->heading(get_string('convertedtimes', 'block_timezoneclock'), 3, 'w-100');
        }

        $OUTPUT->header();

        $PAGE->start_collecting_javascript_requirements();
        $html = $OUTPUT->render_from_template('block_timezoneclock/timezones', $context);
        $js = $PAGE->requires->get_end_code();

        return compact('html', 'js');
    }

    /**
     * function's body is empty as no data initially set
     *
     * @param array additional formdata to pass
     * @return void
     */
    public function set_data_for_dynamic_submission(array $formdata = []): void {
        $formdata['contextid'] = $this->get_contextid();
        $formdata['timezone'] = self::get_usertimezone();
        $formdata['toggletimestamp'] = (int) debugging();
        $formdata['timezones'] = (array) $this->get_block()->config->timezone;
        if (NO_MOODLE_COOKIES) {
            $formdata['timezone'] = $this->optional_param('timezone', 'GMT', PARAM_TIMEZONE);
            $formdata['timezone'] = core_date::normalise_timezone($formdata['timezone']);
            $formdata['timezones'] = !empty($formdata['timezones']) ?
                $formdata['timezones'] : (array) core_date::get_server_timezone();
            $t = $this->optional_param('t', null, PARAM_INT);
            if ($t > 0) {
                $formdata['timestamp'] = $formdata['selectedstamp'] = $t;
                if ($formdata['timezone'] !== 'GMT') {
                    $formdata['timezones'] = (array) $formdata['timezone'];
                    $formdata['timezone'] = 'GMT';
                }
            }
        } else {
            $formdata['timestamp'] = time();
            $formdata['selectedstamp']['enabled'] = true;
        }
        $this->set_data($formdata);
    }

    /**
     * Sets moodle url for form
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url(get_local_referer());
    }

    /**
     * Get block instance
     *
     * @return block_timezoneclock
     */
    public function get_block() {
        if (is_null($this->blockinstance)) {
            $blockinstanceid = $this->get_context_for_dynamic_submission()->instanceid;
            $this->blockinstance = block_instance_by_id($blockinstanceid);
        }
        return $this->blockinstance;
    }

    /**
     * Get user's timezone
     *
     * @return string
     */
    public static function get_usertimezone() {
        if (is_null(self::$usertimezone)) {
            self::$usertimezone = core_date::get_user_timezone();
        }
        return self::$usertimezone;
    }

}
