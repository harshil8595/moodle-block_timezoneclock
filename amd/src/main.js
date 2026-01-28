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
import Fragment from 'core/fragment';
import {exception as displayException} from 'core/notification';
import {eventTypes} from 'core_filters/events';

const GMTCONST = 'GMT';
let select2registered = false;
let monthNames;

const getLangCode = () => 'en';

const to24Hour = (hour, period) => {
    let h = parseInt(hour, 10);
    if (period === 'AM' && h === 12) {
        return '00';
    }
    if (period === 'PM' && h < 12) {
        h += 12;
    }
    return String(h).padStart(2, '0');
};

const monthsForLocale = (monthFormat = 'long') => {
    const format = new Intl.DateTimeFormat(getLangCode(), {month: monthFormat}).format;
    return [...Array(12)].map((_, i) => {
        const d = new Date();
        d.setMonth(i);
        return format(d);
    });
};

const getNumericMonth = (monthName) => {
    monthNames = monthNames || monthsForLocale();
    const index = monthNames.indexOf(monthName);
    return index !== -1 ? String(index + 1).padStart(2, '0') : '';
};

const getDateInfo = (timeZone, dateFormat, date = new Date()) => {
    const is12Hour = /[hagA]/.test(dateFormat);
    const needsWeekday = /[Dl]/.test(dateFormat);
    const needsMonthName = /[FM]/.test(dateFormat);

    const formatter = new Intl.DateTimeFormat(getLangCode(), {
        timeZone,
        year: 'numeric',
        month: needsMonthName ? 'long' : '2-digit',
        day: '2-digit',
        weekday: needsWeekday ? 'long' : undefined,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: is12Hour,
        timeZoneName: 'longOffset'
    });

    const parts = formatter.formatToParts(date).reduce((acc, part) => {
        if (part.type !== 'literal') {
            acc[part.type] = part.value;
        }
        return acc;
    }, {});

    const shortWeekday = parts.weekday?.slice(0, 3);
    const shortMonth = parts.month?.slice(0, 3);
    const twoDigitYear = parts.year?.slice(-2);

    const replacements = {
        Y: parts.year,
        y: twoDigitYear,
        m: /^\d+$/.test(parts.month) ? parts.month : getNumericMonth(parts.month),
        F: parts.month || '',
        M: shortMonth || '',
        d: parts.day,
        H: is12Hour ? to24Hour(parts.hour, parts.dayPeriod) : parts.hour,
        h: parts.hour,
        g: String(parseInt(parts.hour, 10)),
        i: parts.minute,
        s: parts.second,
        A: parts.dayPeriod?.toUpperCase() || '',
        a: parts.dayPeriod?.toLowerCase() || '',
        D: shortWeekday || '',
        l: parts.weekday || '',
    };

    replacements.timeZoneName = parts.timeZoneName;

    return replacements;
};

const updateTime = (dateFormat) => {
    document.querySelectorAll('[data-region="clock"]:not([data-autoupdate="false"])')
    .forEach(clock => {
        const datefractions = getDateInfo(clock.dataset.timezone, dateFormat);
        clock.querySelectorAll('[data-fraction]').forEach(sp => {
            const {fraction, unit} = sp.dataset;
            if (fraction in datefractions && unit != datefractions[fraction]) {
                sp.style.setProperty(`--unit`, datefractions[fraction]);
                sp.setAttribute('data-unit', datefractions[fraction]);
                sp.firstElementChild.innerText = datefractions[fraction];
            }
        });
    });
    setTimeout(() => updateTime(dateFormat), 1000);
};

export const makeSelectEnhanced = (parentNode = document) => {
    require(['theme_boost/index',
        `${M.cfg.wwwroot}/blocks/timezoneclock/tom-select/js/tom-select.complete.min.js`], (_, TomSelect) => {
        [].concat(parentNode).forEach(pNode => {
            const $spnodes = pNode.querySelectorAll('[data-selectenhanced="1"]');
            $spnodes.forEach(node => {
                node.classList.remove('custom-select');
                new TomSelect(node, {
                    openOnFocus: true, maxOptions: null,
                    plugins: [],
                });
            });
        });
    });
};

export const initBlock = (dateFormat) => {
    const d = new Date();
    setTimeout(() => updateTime(dateFormat), 1000 - d.getMilliseconds());
    if (!select2registered) {
        select2registered = true;
        document.addEventListener(eventTypes.filterContentUpdated, e => {
            makeSelectEnhanced(e.detail.nodes);
        });
    }

    // Replace the computer timezone
    const replacecomputertznode = document.querySelector('[data-action="replacecomputertimezone"]');
    if (replacecomputertznode) {
        const computrertz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        replacecomputertznode.setAttribute('data-timezone', computrertz);
        updateTime(dateFormat);
        replacecomputertznode.removeAttribute('data-action');
    }
};

export const registerForm = (formUniqId, dateFormat) => {
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
            const dateinfo = getDateInfo(timezoneSelection.value, dateFormat, date);
            const gmtOffset = dateinfo.timeZoneName.split(GMTCONST).pop();

            const d = new Date(date + gmtOffset);
            timestampInput.value = Math.round(d.valueOf() / 1000);
        };
        const clientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const urlParams = new URLSearchParams([
            ...(new URLSearchParams(location.search)).entries(),
            ...Object.entries({...form.dataset, timezone: clientTimezone})
        ]);
        dForm.load(Object.fromEntries(urlParams)).then(() => {
            if (form.nextElementSibling.childElementCount === 0) {
                dForm.submitFormAjax({firstload: 1});
            }
            return;
        }).catch(displayException);
        dForm.addEventListener(dForm.events.FORM_SUBMITTED, e => {
            e.preventDefault();
            replaceNodeContents(form.nextElementSibling, e.detail.html,
                Fragment.processCollectedJavascript(e.detail.js));
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
                const info = getDateInfo(timezoneSelection.value, dateFormat, d);
                info.hour = Number(info.hour);
                dateTimeNode.querySelectorAll('select').forEach(sel => {
                    sel.value = info[getTypeFromElement(sel)];
                });
            }
        });
    }
};
