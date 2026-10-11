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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/chatwootsync/lib.php');

/**
 * Unit tests for lib.php callbacks.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::local_chatwootsync_extend_signup_form
 * @covers     ::local_chatwootsync_post_signup_requests
 */
final class lib_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test that post_signup_requests stores opt-in data in session when checked.
     */
    public function test_post_signup_requests_stores_session_on_optin(): void {
        global $SESSION;

        unset($SESSION->chatwootsync_signup_optin);

        $data = new \stdClass();
        $data->chatwootsync_wa_optin = 1;
        $data->email = 'Test@Example.Com';
        $data->phone1 = '+34612345678';

        local_chatwootsync_post_signup_requests($data);

        $this->assertTrue(isset($SESSION->chatwootsync_signup_optin));
        $this->assertTrue($SESSION->chatwootsync_signup_optin['enabled']);
        $this->assertEquals('test@example.com', $SESSION->chatwootsync_signup_optin['email']);
        $this->assertEquals('+34612345678', $SESSION->chatwootsync_signup_optin['phone']);
        $this->assertArrayHasKey('time', $SESSION->chatwootsync_signup_optin);
    }

    /**
     * Test that post_signup_requests does nothing when opt-in is not checked.
     */
    public function test_post_signup_requests_does_nothing_without_optin(): void {
        global $SESSION;

        unset($SESSION->chatwootsync_signup_optin);

        $data = new \stdClass();
        $data->chatwootsync_wa_optin = 0;
        $data->email = 'test@example.com';
        $data->phone1 = '+34612345678';

        local_chatwootsync_post_signup_requests($data);

        $this->assertFalse(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that post_signup_requests does nothing when field is absent.
     */
    public function test_post_signup_requests_does_nothing_without_field(): void {
        global $SESSION;

        unset($SESSION->chatwootsync_signup_optin);

        $data = new \stdClass();
        $data->email = 'test@example.com';
        $data->phone1 = '+34612345678';

        local_chatwootsync_post_signup_requests($data);

        $this->assertFalse(isset($SESSION->chatwootsync_signup_optin));
    }

    /**
     * Test that post_signup_requests stores the phone number correctly.
     */
    public function test_post_signup_requests_stores_phone(): void {
        global $SESSION;

        unset($SESSION->chatwootsync_signup_optin);

        $data = new \stdClass();
        $data->chatwootsync_wa_optin = 1;
        $data->email = 'test@example.com';
        $data->phone1 = '+1 (555) 123-4567';

        local_chatwootsync_post_signup_requests($data);

        $this->assertEquals('+1 (555) 123-4567', $SESSION->chatwootsync_signup_optin['phone']);
    }
}
