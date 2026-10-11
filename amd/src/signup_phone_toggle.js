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
 * Enable/disable the WhatsApp opt-in checkbox based on phone validity.
 *
 * If intl-tel-input is active on the phone1 input (loaded by theme_lxd),
 * uses iti.isValidNumber() for validation. Otherwise falls back to a
 * basic non-empty check.
 *
 * @module     local_chatwootsync/signup_phone_toggle
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        init: function() {
            var checkbox = document.querySelector('input[name="chatwootsync_wa_optin"]');
            var phoneInput = document.querySelector('input[name="phone1"]');

            if (!checkbox || !phoneInput) {
                return;
            }

            /**
             * Check if the phone number is valid.
             * @returns {boolean}
             */
            var isPhoneValid = function() {
                // Try to get intl-tel-input instance if available.
                var itiInstance = null;
                if (phoneInput.closest('.iti') && window.intlTelInputGlobals) {
                    itiInstance = window.intlTelInputGlobals.getInstance(phoneInput);
                }

                if (itiInstance && typeof itiInstance.isValidNumber === 'function') {
                    return itiInstance.isValidNumber();
                }

                // Fallback: just check non-empty.
                return phoneInput.value.trim().length > 0;
            };

            var update = function() {
                if (isPhoneValid()) {
                    checkbox.disabled = false;
                } else {
                    checkbox.disabled = true;
                    checkbox.checked = false;
                }
            };

            // Set initial state.
            update();

            // Listen for relevant events.
            ['input', 'change', 'blur', 'countrychange'].forEach(function(eventName) {
                phoneInput.addEventListener(eventName, update);
            });
        }
    };
});
