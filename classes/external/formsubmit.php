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

namespace block_timezoneclock\external;

use context;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_form\external\dynamic_form;
use moodle_exception;
use moodle_url;

/**
 * Webservice for processing form submission
 *
 * @package   block_timezoneclock
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class formsubmit extends dynamic_form {

    /**
     * Parameters description for web service
     *
     * @return external_function_parameters
     */
    public static function process_parameters(): external_function_parameters {
        return static::execute_parameters();
    }

    /**
     * Submit a form from a modal dialogue.
     *
     * @param string $formclass
     * @param string $formdatastr
     * @return array
     * @throws \moodle_exception
     */
    public static function process(string $formclass, string $formdatastr): array {
        global $PAGE, $OUTPUT, $USER;

        $params = self::validate_parameters(self::process_parameters(), [
            'form' => $formclass,
            'formdata' => $formdatastr,
        ]);
        $formclass = $params['form'];
        parse_str($params['formdata'], $formdata);

        self::autoload_block_edit_form($formclass);

        if (!class_exists($formclass) || !is_subclass_of($formclass, \core_form\dynamic_form::class)) {
            // For security reason we don't throw exception "class does not exist" but rather an access exception.
            throw new moodle_exception('nopermissionform', 'core_form');
        }

        if (NO_MOODLE_COOKIES) {
            $pagecontextid = $formclass['contextid'] ?? SYSCONTEXTID;
            $PAGE->reset_theme_and_output();
            $PAGE->set_context(context::instance_by_id($pagecontextid));
            if (WS_SERVER || CLI_SCRIPT) {
                $PAGE->set_url('/');
            } else {
                $PAGE->set_url(new moodle_url(get_local_referer()));
            }
            $USER->ignoresesskey = true;
        }

        /** @var \core_form\dynamic_form $form */
        $form = new $formclass(null, null, 'post', '', [], true, $formdata, !NO_MOODLE_COOKIES);
        $form->set_data_for_dynamic_submission();
        if (!$form->is_cancelled() && $form->is_submitted() && $form->is_validated()) {
            // Form was properly submitted, process and return results of processing. No need to render it again.
            return ['submitted' => true, 'data' => json_encode($form->process_dynamic_submission())];
        }

        // Render actual form.

        if ($form->no_submit_button_pressed()) {
            // If form has not been submitted, we have to recreate the form for being able to properly handle non-submit action
            // like "repeat elements" to include additional JS.
            /** @var \core_form\dynamic_form $form */
            $form = new $formclass(null, null, 'post', '', [], true, $formdata, true);
            $form->set_data_for_dynamic_submission();
        }
        // Hack alert: Forcing bootstrap_renderer to initiate moodle page.
        $OUTPUT->header();

        $PAGE->start_collecting_javascript_requirements();
        $data = $form->render();
        $jsfooter = $PAGE->requires->get_end_code();
        $output = ['submitted' => false, 'html' => $data, 'javascript' => $jsfooter];
        return $output;
    }

    /**
     * Return description for web service
     *
     * @return external_single_structure
     */
    public static function process_returns(): external_single_structure {
        return static::execute_returns();
    }

}
