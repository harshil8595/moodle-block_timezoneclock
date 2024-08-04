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
 * Dyanamic from for submitting when user not logged in
 *
 * @module block_timezoneclock/main
 * @copyright 2024 Harshil Patel <harshil8595@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import DynamicForm from 'core_form/dynamicform';
import Ajax from 'core/ajax';
import Fragment from 'core/fragment';

export class BlockTimezoneclockDynamicForm extends DynamicForm {

    /**
     * Ajax service method to call from non login url
     *
     * @var {String}
     */
    static ajaxCallMethod = 'block_timezoneclock_dynamic_form';

    /**
     * Capture user loggedin or not
     */
    static userLogginIn;

    /**
     * Checks user loggedin or not
     *
     * @return {Boolean}
     */
    get isUserLogginIn() {
        if (typeof BlockTimezoneclockDynamicForm.userLogginIn === 'undefined') {
            BlockTimezoneclockDynamicForm.userLogginIn = !!this.container.getAttribute('data-user-loggedin');
        }
        return BlockTimezoneclockDynamicForm.userLogginIn;
    }

    /**
     * Get form body
     *
     * @param {String} formDataString form data in format of a query string
     * @private
     * @return {Promise}
     */
    getBody(formDataString) {
        return Ajax.call([{
            methodname: BlockTimezoneclockDynamicForm.ajaxCallMethod,
            args: {
                formdata: formDataString,
                form: this.formClass,
            }
        }], true, this.isUserLogginIn)[0]
        .then(response => {
            return {...response, js: Fragment.processCollectedJavascript(response.javascript)};
        });
    }

    /**
     * Submit the form via AJAX call to the core_form_dynamic_form WS
     *
     * @param {Object} additionalFormData Additional Form Data to pass
     */
    async submitFormAjax(additionalFormData = {}) {
        // If we found invalid fields, focus on the first one and do not submit via ajax.
        if (!(await this.validateElements())) {
            this.trigger(this.events.CLIENT_VALIDATION_ERROR, null, false);
            return;
        }
        this.disableButtons();

        // Convert all the form elements values to a serialised string.
        const form = this.container.querySelector('form');
        const formData = new URLSearchParams([
            ...(new FormData(form)).entries(),
            ...(new URLSearchParams(additionalFormData)).entries()
        ]);

        // Now we can continue...
        this.getBody(formData.toString()).then((response) => {
            if (!response.submitted) {
                // Form was not submitted, it could be either because validation failed or because no-submit button was pressed.
                this.updateForm(response);
                this.enableButtons();
                this.trigger(this.events.SERVER_VALIDATION_ERROR, null, false);
            } else {
                // Form was submitted properly.
                const data = JSON.parse(response.data);
                this.enableButtons();
                this.notifyResetFormChanges();
                this.onSubmitSuccess(data);
            }
            return null;
        })
        .catch(exception => this.onSubmitError(exception));
    }
}
