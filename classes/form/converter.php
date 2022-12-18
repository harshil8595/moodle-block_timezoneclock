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

use block_timezoneclock\output\timezones;
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
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $USER;
        $mform = $this->_form;

        $mform->addElement('hidden', 'instanceid');
        $mform->setType('instanceid', PARAM_INT);
        $mform->setConstant('instanceid', $this->get_instanceid());

        $mform->addElement('text', 'timestamp', get_string('timestamp', 'block_timezoneclock'),
            'size="10" maxlength="10" minlength="10"');
        $mform->setType('timestamp', PARAM_INT);
        $mform->addRule('timestamp', get_string('entervalidtimestamp', 'block_timezoneclock'), 'rangelength', [10, 10], 'client');
        $mform->addRule('timestamp', get_string('entervalidtimestamp', 'block_timezoneclock'), 'positiveint', null, 'client');

        $mform->addElement('date_time_selector', 'selectedstamp', get_string('datestamp', 'block_timezoneclock'),
            ['optional' => true]);
        $mform->setType('selectedstamp', PARAM_INT);
        $mform->disabledIf('timestamp', 'selectedstamp[enabled]', 'checked');

        $choices = core_date::get_list_of_timezones($USER->timezone, true);
        $mform->addElement('autocomplete', 'timezones', get_string('timezones', 'block_timezoneclock'), $choices,
            ['multiple' => true]);
        $mform->setType('timezones', PARAM_NOTAGS);

        $this->add_action_buttons(false, get_string('convert', 'block_timezoneclock'));
    }

    /**
     * Get block instanceid
     *
     * @return int
     */
    private function get_instanceid(): int {
        return $this->optional_param('instanceid', 0, PARAM_INT);
    }

    /**
     * Get context for form from block instanceid
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_block::instance($this->get_instanceid());
    }

    /**
     * Checks user loggedin or not
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        require_login();
    }

    /**
     * Process form and generates timezones list
     *
     * @return void
     */
    public function process_dynamic_submission() {
        global $PAGE;
        $formdata = $this->get_data();

        $timestamp = !empty($formdata->timestamp) ? $formdata->timestamp : $formdata->selectedstamp;
        $timezonestab = new timezones([
            'instanceid' => $formdata->instanceid,
            'timezones' => $formdata->timezones ?? [],
            'timestamp' => $timestamp,
        ]);
        $timezonestab->set_blockautoupdate(true);
        $renderer = $PAGE->get_renderer('block_timezoneclock');

        $renderer->header();

        $PAGE->start_collecting_javascript_requirements();
        $html = html_writer::tag('h3', get_string('convertedtimes', 'block_timezoneclock'));
        $html .= html_writer::div($renderer->render_timezones($timezonestab), 'additionaltimezones');
        $js = $PAGE->requires->get_end_code();

        return compact('html', 'js');
    }

    /**
     * function's body is empty as no data initially set
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {

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
     * Validation
     * - Require any one unix timestamp or timeselection
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['timestamp']) && empty($data['selectedstamp'])) {
            $errors['timestamp'] = get_string('timestampnotentered', 'block_timezoneclock');
        }
        return $errors;
    }

}
