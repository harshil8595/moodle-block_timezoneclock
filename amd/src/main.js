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
import {processCollectedJavascript} from 'core/fragment';

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

const getDateInfo = (timeZone, timestamp = new Date(), customdateOptions = {}) => {
    customdateOptions = {...dtOptions, ...customdateOptions, timeZone};
    const t1 = new Intl.DateTimeFormat('en-us', customdateOptions);
    const dateInfo = t1.formatToParts(timestamp).reduce((a, i) => ({...a, [i.type]: i.value}), {});
    return {...dateInfo, day: dateInfo.day.padStart(2, 0)};
};

const updateTime = () => document.querySelectorAll('[data-region="clock"]:not([data-autoupdate="false"])')
    .forEach(clock => {
        const datefractions = getDateInfo(clock.dataset.timezone);
        clock.querySelectorAll('[data-fraction]').forEach(sp => {
            const {fraction, unit} = sp.dataset;
            if (unit?.toString() !== datefractions[fraction].toString()) {
                sp.style.setProperty(`--unit`, datefractions[fraction]);
                sp.setAttribute('data-unit', datefractions[fraction]);
                sp.firstElementChild.innerText = datefractions[fraction].toString();
            }
        });
});

export const initBlock = () => {
    setInterval(updateTime, 1000);
};

export const registerForm = formUniqId => {
    const form = document.getElementById(formUniqId);
    const r = new RegExp(`(day|month|year|hour|minute)`);
    if (form) {
        const dForm = new DynamicForm(form, form.dataset.formClass);
        const getTypeFromElement = sel => sel.name.match(r).pop();
        const generateTimeStamp = () => {
            const timestampInput = dForm.getFormNode().elements.timestamp;
            const timezoneSelection = dForm.getFormNode().elements.timezone;
            const dateTimeNode = dForm.getFormNode().querySelector('[data-fieldtype="date_time_selector"]');
            const fractions = [...dateTimeNode.querySelectorAll('select')]
            .reduce((acc, sel) => ({...acc, [getTypeFromElement(sel)]: sel.value.padStart(2, 0)}), {});
            const {year, month, day, hour, minute} = fractions;

            const date = new Date(`${year}-${month}-${day}T${hour}:${minute}:00.000`);
            const dateinfo = getDateInfo(timezoneSelection.value, date, {timeZoneName: 'longOffset'});
            const gmtOffset = dateinfo.timeZoneName.split('GMT').pop();

            const d = new Date(date + gmtOffset);
            timestampInput.value = Math.round(d.valueOf() / 1000);
        };
        dForm.load({
            instanceid: form.closest('[data-instance-id]').getAttribute('data-instance-id')
        });
        dForm.addEventListener(dForm.events.FORM_SUBMITTED, e => {
            e.preventDefault();
            replaceNodeContents(form.nextElementSibling, e.detail.html, processCollectedJavascript(e.detail.js));
        });
        dForm.addEventListener('change', e => {
            const dateTimeNode = e.target.closest('[data-fieldtype="date_time_selector"]');
            const timezoneSelection = e.target.closest('[name="timezone"]');
            if (dateTimeNode || timezoneSelection) {
                generateTimeStamp();
            }
        });
        dForm.addEventListener('change', e => {
            const timestampInput = e.target.closest('[name="timestamp"]');
            const timezoneSelection = dForm.getFormNode().elements.timezone;
            const dateTimeNode = dForm.getFormNode().querySelector('[data-fieldtype="date_time_selector"]');
            if (timestampInput) {
                const d = new Date(0);
                d.setUTCSeconds(timestampInput.value);
                const info = getDateInfo(timezoneSelection.value, d, {month: 'numeric', hour12: false});
                info.hour = Number(info.hour);
                dateTimeNode.querySelectorAll('select').forEach(sel => {
                    sel.value = info[getTypeFromElement(sel)];
                });
            }
        });
    }
};
