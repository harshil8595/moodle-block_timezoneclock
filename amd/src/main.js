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
 * Main js functions for block_timezoneclock
 *
 * @module block_timezoneclock/main
 * @copyright 2022 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import DynamicForm from 'core_form/dynamicform';
import {replaceNodeContents} from 'core/templates';

const dtOptions = {
    year: 'numeric',
    month: 'short',
    weekday: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
    hour12: true
};
const getDateInfo = (timeZone, timestamp = new Date()) => {
    const locale = 'en-us';
    const ctdt = new Date(timestamp.toLocaleString(locale, {timeZone}));
    const [day, month, date, year, dateinfo, meridiem] = ctdt
        .toLocaleString(locale, dtOptions).replace(/,/gi, '').split(' ');
    const [hour, minute, second] = dateinfo.split(':').map(unit => unit.padStart(2, 0));
    return {day, month, date, year, hour, minute, second, meridiem};
};

const updateTime = () => document.querySelectorAll('[data-region="clock"]:not([data-autoupdate="false"])')
    .forEach(clock => {
        const datefractions = getDateInfo(clock.dataset.timezone);
        clock.querySelectorAll('.clock > span[data-fraction]').forEach(sp => {
            const {fraction} = sp.dataset;
            if (sp.closest('.hand')) {
                sp.style.setProperty(`--rotation`, datefractions[fraction]);
                return;
            }
            if (sp.innerText !== datefractions[fraction]) {
                sp.innerText = datefractions[fraction];
            }
        });
});

export const initBlock = () => {
    setInterval(updateTime, 1000);
};

export const registerForm = formUniqId => {
    const form = document.getElementById(formUniqId);
    if (form) {
        const dForm = new DynamicForm(form, form.dataset.formClass);
        dForm.load({
            instanceid: form.closest('[data-instance-id]').getAttribute('data-instance-id')
        });
        dForm.addEventListener(dForm.events.FORM_SUBMITTED, e => {
            e.preventDefault();
            replaceNodeContents(form.nextElementSibling, e.detail.html, e.detail.js);
        });
    }
};
