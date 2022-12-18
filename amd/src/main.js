import DynamicForm from 'core_form/dynamicform';
import {replaceNode} from 'core/templates';

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
        clock.querySelectorAll('.clock > span').forEach(sp => {
            if (sp.innerText !== datefractions[sp.dataset.fraction]) {
                sp.innerText = datefractions[sp.dataset.fraction];
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
            replaceNode(form.nextSibling, e.detail.html, e.detail.js);
        });
    }
};