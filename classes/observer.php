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

namespace local_chatwootsync;

/**
 * Event observer for user creation.
 *
 * Listens for user_created events and syncs the new user to Chatwoot
 * if they opted in to WhatsApp communications during signup.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /** @var int Maximum age in seconds for session opt-in data to be considered valid. */
    private const OPTIN_MAX_AGE = 300;

    /**
     * Handle the user_created event.
     *
     * Checks for a WhatsApp opt-in flag stored in the session by
     * local_chatwootsync_post_signup_requests(), verifies the email and
     * phone match, and syncs the contact to Chatwoot.
     *
     * @param \core\event\user_created $event The user creation event.
     */
    public static function user_created(\core\event\user_created $event): void {
        global $DB, $SESSION;

        // Only proceed if we came from signup with our session flag set.
        $opt = $SESSION->chatwootsync_signup_optin ?? null;
        if (empty($opt['enabled']) || empty($opt['email']) || empty($opt['phone'])) {
            return;
        }

        // Reject stale session data.
        if (isset($opt['time']) && (time() - $opt['time']) > self::OPTIN_MAX_AGE) {
            unset($SESSION->chatwootsync_signup_optin);
            return;
        }

        // Verify the email matches the newly created user.
        $user = $DB->get_record('user', ['id' => $event->objectid],
            'id, email, firstname, lastname, phone1', MUST_EXIST);
        $useremail = strtolower(trim((string) $user->email));

        if ($useremail !== $opt['email']) {
            return;
        }

        // Verify the phone matches (normalized comparison).
        $userphone = trim((string) $user->phone1);
        if ($userphone === ''
            || chatwoot_client::normalize_phone($userphone) !== chatwoot_client::normalize_phone($opt['phone'])) {
            return;
        }

        // Consume the session data to avoid affecting other user creations.
        unset($SESSION->chatwootsync_signup_optin);

        // Call Chatwoot API. Never block user registration on failure.
        try {
            $client = new chatwoot_client();
            $fullname = trim($user->firstname . ' ' . $user->lastname);
            $client->sync_optin_contact($userphone, $fullname, $useremail);
        } catch (\Throwable $e) {
            debugging('Chatwoot sync failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
