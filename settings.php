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
 * Admin settings for the local_chatwootsync plugin.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_chatwootsync', get_string('pluginname', 'local_chatwootsync'));

    // Connection settings.
    $settings->add(new admin_setting_configtext(
        'local_chatwootsync/chatwoot_baseurl',
        get_string('chatwoot_baseurl', 'local_chatwootsync'),
        get_string('chatwoot_baseurl_desc', 'local_chatwootsync'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_chatwootsync/chatwoot_account_id',
        get_string('chatwoot_account_id', 'local_chatwootsync'),
        get_string('chatwoot_account_id_desc', 'local_chatwootsync'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_chatwootsync/chatwoot_token',
        get_string('chatwoot_token', 'local_chatwootsync'),
        get_string('chatwoot_token_desc', 'local_chatwootsync'),
        ''
    ));

    // Dynamic selects: inboxes, labels and custom attributes from Chatwoot API.
    $baseurl   = get_config('local_chatwootsync', 'chatwoot_baseurl');
    $accountid = get_config('local_chatwootsync', 'chatwoot_account_id');
    $token     = get_config('local_chatwootsync', 'chatwoot_token');

    if ($baseurl && $accountid && $token) {
        try {
            $client = new \local_chatwootsync\chatwoot_client();

            // Fetch inboxes.
            $apiinboxes = $client->get_inboxes();
            $inboxchoices = ['' => get_string('choose', 'local_chatwootsync')];
            foreach ($apiinboxes as $inbox) {
                $id = $inbox['id'] ?? '';
                $name = $inbox['name'] ?? '';
                if ($id !== '' && $name !== '') {
                    $inboxchoices[$id] = $name;
                }
            }

            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/chatwoot_inbox_id',
                get_string('chatwoot_inbox_id', 'local_chatwootsync'),
                get_string('chatwoot_inbox_id_desc', 'local_chatwootsync'),
                '',
                $inboxchoices
            ));

            // Fetch labels.
            $apilabels = $client->get_account_labels();
            $labelchoices = ['' => get_string('choose', 'local_chatwootsync')];
            foreach ($apilabels as $label) {
                $title = $label['title'] ?? '';
                if ($title !== '') {
                    $labelchoices[$title] = $title;
                }
            }

            // Fetch custom attribute definitions for contacts.
            $apiattrs = $client->get_custom_attributes();
            $attrchoices = ['' => get_string('choose', 'local_chatwootsync')];
            foreach ($apiattrs as $attr) {
                $key = $attr['attribute_key'] ?? '';
                $name = $attr['attribute_display_name'] ?? $key;
                if ($key !== '') {
                    $attrchoices[$key] = $name;
                }
            }

            // Label selects.
            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/label_optin',
                get_string('label_optin', 'local_chatwootsync'),
                get_string('label_optin_desc', 'local_chatwootsync'),
                '',
                $labelchoices
            ));

            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/label_optout',
                get_string('label_optout', 'local_chatwootsync'),
                get_string('label_optout_desc', 'local_chatwootsync'),
                '',
                $labelchoices
            ));

            // Attribute selects.
            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/attr_opt_in',
                get_string('attr_opt_in', 'local_chatwootsync'),
                get_string('attr_opt_in_desc', 'local_chatwootsync'),
                '',
                $attrchoices
            ));

            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/attr_opt_in_at',
                get_string('attr_opt_in_at', 'local_chatwootsync'),
                get_string('attr_opt_in_at_desc', 'local_chatwootsync'),
                '',
                $attrchoices
            ));

            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/attr_opt_out',
                get_string('attr_opt_out', 'local_chatwootsync'),
                get_string('attr_opt_out_desc', 'local_chatwootsync'),
                '',
                $attrchoices
            ));

            $settings->add(new admin_setting_configselect(
                'local_chatwootsync/attr_opt_out_at',
                get_string('attr_opt_out_at', 'local_chatwootsync'),
                get_string('attr_opt_out_at_desc', 'local_chatwootsync'),
                '',
                $attrchoices
            ));

        } catch (\Throwable $e) {
            $settings->add(new admin_setting_description(
                'local_chatwootsync/api_error',
                get_string('api_connection_error', 'local_chatwootsync'),
                get_string('api_connection_error_desc', 'local_chatwootsync', $e->getMessage())
            ));
        }
    } else {
        $settings->add(new admin_setting_description(
            'local_chatwootsync/save_credentials_first',
            '',
            get_string('save_credentials_first', 'local_chatwootsync')
        ));
    }

    $ADMIN->add('localplugins', $settings);
}
