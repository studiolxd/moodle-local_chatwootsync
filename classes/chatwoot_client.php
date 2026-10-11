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
 * HTTP client for the Chatwoot CRM API.
 *
 * Handles authentication and API calls to search, create, and update
 * contacts, manage labels, and set custom attributes via Chatwoot's
 * REST API using token-based authentication.
 *
 * @package    local_chatwootsync
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chatwoot_client {

    /** @var string Chatwoot instance base URL. */
    private string $baseurl;

    /** @var int Chatwoot account ID. */
    private int $accountid;

    /** @var string API access token. */
    private string $token;

    /** @var int Inbox ID for new contacts. */
    private int $inboxid;

    /**
     * Constructor. Reads plugin configuration and validates it.
     *
     * @throws \coding_exception If any required configuration value is missing.
     */
    public function __construct() {
        $this->baseurl   = rtrim((string) get_config('local_chatwootsync', 'chatwoot_baseurl'), '/');
        $this->accountid = (int) get_config('local_chatwootsync', 'chatwoot_account_id');
        $this->token     = (string) get_config('local_chatwootsync', 'chatwoot_token');
        $this->inboxid   = (int) get_config('local_chatwootsync', 'chatwoot_inbox_id');

        if (!$this->baseurl || !$this->accountid || !$this->token) {
            throw new \coding_exception('Chatwoot configuration is incomplete (local_chatwootsync).');
        }
    }

    /**
     * Create a pre-configured curl instance with API token header.
     *
     * @return \curl A curl instance with authentication headers set.
     */
    private function curl(): \curl {
        $curl = new \curl();
        $curl->setHeader('api_access_token: ' . $this->token);
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader('Accept: application/json');
        return $curl;
    }

    /**
     * Build the base API URL for this account.
     *
     * @return string The base URL like https://host/api/v1/accounts/1
     */
    private function api(): string {
        return $this->baseurl . '/api/v1/accounts/' . $this->accountid;
    }

    // ---------------------------------------------------------------
    // Admin / settings methods.
    // ---------------------------------------------------------------

    /**
     * Get all labels for the account.
     *
     * @return array List of label objects with 'title' key.
     * @throws \moodle_exception If the API call fails.
     */
    public function get_account_labels(): array {
        $curl = $this->curl();
        $resp = $curl->get($this->api() . '/labels');
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'GET labels failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];
        return $json['payload'] ?? [];
    }

    /**
     * Get custom attribute definitions for contacts.
     *
     * @return array List of attribute definitions with 'attribute_key' and 'attribute_display_name'.
     * @throws \moodle_exception If the API call fails.
     */
    public function get_custom_attributes(): array {
        $curl = $this->curl();
        $resp = $curl->get($this->api() . '/custom_attribute_definitions');
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'GET custom attributes failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];

        // Filter to contact attributes only (attribute_model 0 = contact).
        return array_values(array_filter($json, function ($attr) {
            return ($attr['attribute_model'] ?? null) === 0
                || ($attr['attribute_model'] ?? '') === 'contact_attribute';
        }));
    }

    /**
     * Get all inboxes for the account.
     *
     * @return array List of inbox objects with 'id' and 'name' keys.
     * @throws \moodle_exception If the API call fails.
     */
    public function get_inboxes(): array {
        $curl = $this->curl();
        $resp = $curl->get($this->api() . '/inboxes');
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'GET inboxes failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];
        return $json['payload'] ?? [];
    }

    // ---------------------------------------------------------------
    // Signup flow methods.
    // ---------------------------------------------------------------

    /**
     * Sync an opted-in user to Chatwoot: search/create contact, set labels and attributes.
     *
     * @param string $phone Phone number in E.164 format.
     * @param string $name  Full name of the user.
     * @param string $email Email address.
     * @throws \moodle_exception If a critical API call fails.
     */
    public function sync_optin_contact(string $phone, string $name, string $email): void {
        $labeloptin     = (string) get_config('local_chatwootsync', 'label_optin');
        $labeloptout    = (string) get_config('local_chatwootsync', 'label_optout');
        $attroptinkey   = (string) get_config('local_chatwootsync', 'attr_opt_in');
        $attroptintatkey = (string) get_config('local_chatwootsync', 'attr_opt_in_at');
        $attroptoutkey  = (string) get_config('local_chatwootsync', 'attr_opt_out');
        $attroptoutatkey = (string) get_config('local_chatwootsync', 'attr_opt_out_at');

        $now = gmdate('c'); // ISO 8601.

        $customattrs = [];
        if ($attroptinkey) {
            $customattrs[$attroptinkey] = true;
        }
        if ($attroptintatkey) {
            $customattrs[$attroptintatkey] = $now;
        }
        if ($attroptoutkey) {
            $customattrs[$attroptoutkey] = false;
        }
        if ($attroptoutatkey) {
            $customattrs[$attroptoutatkey] = null;
        }

        // Step 1: Search for existing contact by phone.
        $contactid = $this->find_contact_by_phone($phone);

        // Step 2: Create or update.
        if (!$contactid) {
            $contactid = $this->create_contact($phone, $name, $email, $customattrs);
        } else {
            $this->update_contact($contactid, $customattrs);
        }

        // Step 3: Manage labels (GET → merge → POST).
        if ($labeloptin || $labeloptout) {
            $currentlabels = $this->get_contact_labels($contactid);

            if ($labeloptin && !in_array($labeloptin, $currentlabels)) {
                $currentlabels[] = $labeloptin;
            }

            if ($labeloptout) {
                $currentlabels = array_values(array_filter($currentlabels, function ($l) use ($labeloptout) {
                    return $l !== $labeloptout;
                }));
            }

            $this->set_contact_labels($contactid, $currentlabels);
        }
    }

    /**
     * Search for an existing Chatwoot contact by phone number.
     *
     * @param string $phone The phone number to search for.
     * @return int|null The contact ID, or null if not found.
     */
    private function find_contact_by_phone(string $phone): ?int {
        $curl = $this->curl();
        $url = $this->api() . '/contacts/search?q=' . rawurlencode($phone);

        $resp = $curl->get($url);
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            debugging('Chatwoot find contact by phone failed: HTTP ' . $code
                . ' for phone ' . $phone, DEBUG_DEVELOPER);
            return null;
        }

        $json = json_decode($resp, true) ?: [];
        $contacts = $json['payload'] ?? [];

        foreach ($contacts as $c) {
            $candidate = $c['phone_number'] ?? '';
            if (self::normalize_phone($candidate) === self::normalize_phone($phone)) {
                $id = (int) ($c['id'] ?? 0);
                return $id > 0 ? $id : null;
            }
        }

        return null;
    }

    /**
     * Create a new contact in Chatwoot.
     *
     * @param string $phone       Phone number.
     * @param string $name        Full name.
     * @param string $email       Email address.
     * @param array  $customattrs Custom attributes to set.
     * @return int The newly created contact ID.
     * @throws \moodle_exception If the API call fails.
     */
    private function create_contact(string $phone, string $name, string $email, array $customattrs): int {
        $curl = $this->curl();

        $payload = [
            'phone_number'     => $phone,
            'name'             => $name,
            'email'            => $email,
            'custom_attributes' => (object) $customattrs,
        ];

        if ($this->inboxid > 0) {
            $payload['inbox_id'] = $this->inboxid;
        }

        $resp = $curl->post($this->api() . '/contacts', json_encode($payload));
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'Create contact failed: HTTP ' . $code . ' :: ' . $resp);
        }

        $json = json_decode($resp, true) ?: [];
        $id = $json['payload']['contact']['id'] ?? null;

        if (!is_numeric($id)) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'Could not extract contact id from Chatwoot response');
        }

        return (int) $id;
    }

    /**
     * Get the current labels for a contact.
     *
     * @param int $contactid The contact ID.
     * @return array List of label strings.
     */
    private function get_contact_labels(int $contactid): array {
        $curl = $this->curl();
        $resp = $curl->get($this->api() . '/contacts/' . $contactid . '/labels');
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            debugging('Chatwoot get contact labels failed: HTTP ' . $code, DEBUG_DEVELOPER);
            return [];
        }

        $json = json_decode($resp, true) ?: [];
        return $json['payload'] ?? [];
    }

    /**
     * Set labels on a contact (overwrites all existing labels).
     *
     * @param int   $contactid The contact ID.
     * @param array $labels    Complete list of labels to set.
     * @throws \moodle_exception If the API call fails.
     */
    private function set_contact_labels(int $contactid, array $labels): void {
        $curl = $this->curl();
        $resp = $curl->post(
            $this->api() . '/contacts/' . $contactid . '/labels',
            json_encode(['labels' => array_values($labels)])
        );
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'Set contact labels failed: HTTP ' . $code . ' :: ' . $resp);
        }
    }

    /**
     * Update custom attributes on a contact.
     *
     * @param int   $contactid   The contact ID.
     * @param array $customattrs Custom attributes to update.
     * @throws \moodle_exception If the API call fails.
     */
    private function update_contact(int $contactid, array $customattrs): void {
        $curl = $this->curl();

        $payload = json_encode(['custom_attributes' => (object) $customattrs]);
        $resp = $curl->put($this->api() . '/contacts/' . $contactid, $payload);
        $code = (int) ($curl->get_info()['http_code'] ?? 0);

        if ($code >= 400) {
            throw new \moodle_exception('chatwoot_api_error', 'local_chatwootsync', '', null,
                'Update contact failed: HTTP ' . $code . ' :: ' . $resp);
        }
    }

    /**
     * Normalize a phone number for comparison by removing spaces, hyphens, and parentheses.
     *
     * @param string $phone The phone number to normalize.
     * @return string The normalized phone number.
     */
    public static function normalize_phone(string $phone): string {
        return preg_replace('/[\s\-\(\)]+/', '', $phone);
    }
}
