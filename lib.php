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

/**
 * Callback implementations for Chatwoot sync.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Inject an optional WhatsApp opt-in checkbox into the signup form.
 *
 * The checkbox is disabled until the phone1 field has a valid phone number.
 * An AMD module handles the dynamic enable/disable behaviour.
 *
 * @param MoodleQuickForm $mform The signup form instance.
 */
function local_chatwootsync_extend_signup_form($mform) {
    global $PAGE;

    $mform->addElement(
        'advcheckbox',
        'chatwootsync_wa_optin',
        '',
        get_string('signup_optin_label', 'local_chatwootsync')
    );
    $mform->setType('chatwootsync_wa_optin', PARAM_BOOL);
    $mform->setDefault('chatwootsync_wa_optin', 0);

    // Basic Moodle form rule: disable checkbox when phone1 is empty.
    $mform->disabledIf('chatwootsync_wa_optin', 'phone1', 'eq', '');

    // AMD module provides enhanced validation with intl-tel-input when available.
    $PAGE->requires->js_call_amd('local_chatwootsync/signup_phone_toggle', 'init');
}

/**
 * Capture the opt-in value after successful form validation.
 *
 * Called by core_login_post_signup_requests() only after all validation
 * passes, just before user_signup().
 *
 * @param stdClass $data The form data object from get_data().
 */
function local_chatwootsync_post_signup_requests($data) {
    global $SESSION;

    if (!empty($data->chatwootsync_wa_optin)) {
        $SESSION->chatwootsync_signup_optin = [
            'enabled' => true,
            'email'   => strtolower(trim($data->email ?? '')),
            'phone'   => trim($data->phone1 ?? ''),
            'time'    => time(),
        ];
    }
}
