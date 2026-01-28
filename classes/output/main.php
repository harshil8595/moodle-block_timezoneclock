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
use block_timezoneclock\form\converter as FormConverter;
use core_date;
use html_writer;
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
        $context = new stdClass();
        $context->dateformat = $this->block->get_format();
        $context->isanalog = $this->block->is_analog();
        $context->formclass = FormConverter::class;
        $context->userloggedin = isloggedin();
        $context->blockcontextid = $this->block->context->id;
        $context->indicators = range(0, MINSECS - 1);
        $context->formuniqid = html_writer::random_id('form');
        $context->additionaltimezones = $this->block->timezones(
            (array) ($this->block->config->timezone ?? [])
        );
        $context->blockautoupdate = false;
        $context->information['moodle'] = block_timezoneclock::dateinfo(
            core_date::get_user_timezone(),
            $context->dateformat,
            !$context->isanalog
        );
        $context->information['moodle']['timezone'] = get_string('tzinformation:moodlelabel', 'block_timezoneclock');

        $context->information['user'] = array_merge(
            $context->information['moodle'],
            ['timezone' => get_string('tzinformation:userlabel', 'block_timezoneclock')]
        );

        $context->information['user']['attributes'] = [[
            'name' => 'data-action',
            'value' => 'replaceusertimezone',
        ]];

        $context->information['computer'] = array_merge(
            $context->information['moodle'],
            ['timezone' => get_string('tzinformation:computerlabel', 'block_timezoneclock')]
        );

        $context->information['computer']['attributes'] = [[
            'name' => 'data-action',
            'value' => 'replacecomputertimezone',
        ]];

        $context->information['toggler'] = [
            'header' => get_string('tzinformation:title', 'block_timezoneclock'),
            'id' => html_writer::random_id('toggler'),
            'collapseable' => true,
            'collapsed' => false,
            'helpbutton' => null,
        ];

        return $context;
    }
}
