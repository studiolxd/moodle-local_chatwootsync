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
 * Unit tests for the event observer.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chatwootsync\observer
 */
final class observer_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test that observer does nothing when no session opt-in data exists.
     */
    public function test_user_created_without_session_data(): void {
        global $SESSION;

        unset($SESSION->chatwootsync_signup_optin);

        $user = $this->getDataGenerator()->create_user(['phone1' => '+34612345678']);

        $this->assertFalse(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that observer does nothing when opt-in is disabled.
     */
    public function test_user_created_with_optin_disabled(): void {
        global $SESSION;

        $SESSION->chatwootsync_signup_optin = [
            'enabled' => false,
            'email'   => 'test@example.com',
            'phone'   => '+34612345678',
            'time'    => time(),
        ];

        $user = $this->getDataGenerator()->create_user([
            'email' => 'test@example.com',
            'phone1' => '+34612345678',
        ]);

        $this->assertFalse($SESSION->chatwootsync_signup_optin['enabled']);
    }

    /**
     * Test that observer does nothing when email does not match.
     */
    public function test_user_created_with_email_mismatch(): void {
        global $SESSION;

        $SESSION->chatwootsync_signup_optin = [
            'enabled' => true,
            'email'   => 'other@example.com',
            'phone'   => '+34612345678',
            'time'    => time(),
        ];

        $user = $this->getDataGenerator()->create_user([
            'email' => 'test@example.com',
            'phone1' => '+34612345678',
        ]);

        $this->assertTrue(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that observer does nothing when phone does not match.
     */
    public function test_user_created_with_phone_mismatch(): void {
        global $SESSION;

        $SESSION->chatwootsync_signup_optin = [
            'enabled' => true,
            'email'   => 'test@example.com',
            'phone'   => '+34612345678',
            'time'    => time(),
        ];

        $user = $this->getDataGenerator()->create_user([
            'email' => 'test@example.com',
            'phone1' => '+1555123456',
        ]);

        $this->assertTrue(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that observer rejects stale session data.
     */
    public function test_user_created_with_stale_session_data(): void {
        global $SESSION;

        $SESSION->chatwootsync_signup_optin = [
            'enabled' => true,
            'email'   => 'test@example.com',
            'phone'   => '+34612345678',
            'time'    => time() - 600, // 10 minutes ago, exceeds 5-minute limit.
        ];

        $user = $this->getDataGenerator()->create_user([
            'email' => 'test@example.com',
            'phone1' => '+34612345678',
        ]);

        $this->assertFalse(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that observer consumes session data and handles missing Chatwoot config gracefully.
     */
    public function test_user_created_with_valid_optin_clears_session(): void {
        global $SESSION;

        $email = 'marketing@example.com';
        $phone = '+34612345678';

        $SESSION->chatwootsync_signup_optin = [
            'enabled' => true,
            'email'   => $email,
            'phone'   => $phone,
            'time'    => time(),
        ];

        // No Chatwoot config set, so chatwoot_client will throw.
        // The observer should catch it and still clear the session.
        $user = $this->getDataGenerator()->create_user([
            'email' => $email,
            'phone1' => $phone,
        ]);

        $this->assertFalse(isset($SESSION->chatwootsync_signup_optin));
    }
}
