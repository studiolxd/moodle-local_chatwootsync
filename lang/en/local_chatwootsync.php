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
 * English language pack for Chatwoot sync
 *
 * @package    local_chatwootsync
 * @category   string
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Chatwoot sync';

// Signup form.
$string['signup_optin_label'] = 'I want to receive communications about new courses and new content in my courses via WhatsApp (I can unsubscribe anytime).';

// Settings.
$string['chatwoot_baseurl'] = 'Chatwoot Base URL';
$string['chatwoot_baseurl_desc'] = 'Example: https://chat.studiolxd.com (no trailing slash).';
$string['chatwoot_account_id'] = 'Chatwoot Account ID';
$string['chatwoot_account_id_desc'] = 'Numeric ID of the Chatwoot account (e.g., 1).';
$string['chatwoot_token'] = 'Chatwoot API Token';
$string['chatwoot_token_desc'] = 'API access token for authentication.';
$string['chatwoot_inbox_id'] = 'Chatwoot Inbox ID';
$string['chatwoot_inbox_id_desc'] = 'Inbox ID for associating new contacts (e.g., a WhatsApp channel).';
$string['label_optin'] = 'Opt-in label';
$string['label_optin_desc'] = 'Label to add when user opts in to WhatsApp.';
$string['label_optout'] = 'Opt-out label';
$string['label_optout_desc'] = 'Label to remove when user opts in (and add when opting out).';
$string['attr_opt_in'] = 'Opt-in attribute';
$string['attr_opt_in_desc'] = 'Custom attribute to set to true when user opts in.';
$string['attr_opt_in_at'] = 'Opt-in timestamp attribute';
$string['attr_opt_in_at_desc'] = 'Custom attribute to store the opt-in date/time.';
$string['attr_opt_out'] = 'Opt-out attribute';
$string['attr_opt_out_desc'] = 'Custom attribute to set to false when user opts in.';
$string['attr_opt_out_at'] = 'Opt-out timestamp attribute';
$string['attr_opt_out_at_desc'] = 'Custom attribute to store the opt-out date/time (cleared on opt-in).';
$string['choose'] = '-- Select --';
$string['save_credentials_first'] = 'Please save the Chatwoot URL, Account ID, and Token first, then reload this page to configure labels and attributes.';
$string['api_connection_error'] = 'Chatwoot API connection error';
$string['api_connection_error_desc'] = 'Could not connect to the Chatwoot API: {$a}. Please check your credentials and try again.';
$string['chatwoot_api_error'] = 'An error occurred while communicating with the Chatwoot API.';

// Privacy.
$string['privacy:metadata'] = 'The Chatwoot sync plugin sends user data to an external Chatwoot CRM instance for WhatsApp communications.';
$string['privacy:metadata:phone'] = 'The user\'s phone number is sent to create or identify a contact.';
$string['privacy:metadata:email'] = 'The user\'s email address is sent to Chatwoot.';
$string['privacy:metadata:name'] = 'The user\'s full name is sent to Chatwoot.';
$string['privacy:metadata:ip'] = 'The user\'s IP address may be logged for consent records.';
