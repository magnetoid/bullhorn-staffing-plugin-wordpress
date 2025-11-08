<?php
declare(strict_types=1);
/**
 * Plugin Name: WP Bullhorn Staffing
 * Plugin URI: https://github.com/magnetoid/bullhorn-staffing-plugin-wordpress
 * Description: Bullhorn Staffing synchronisation plugin for WordPress
 * Version: 0.1.0
 * Author: Think studio
 * Author URI: https://think.studio/
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Tested up to: 6.4
 */

require __DIR__ . '/vendor/autoload.php';

if (!defined('WPBS_PLUGIN_FILE')) {
    define('WPBS_PLUGIN_FILE', __FILE__);
}

final class WPBullhornStaffing
{
    private static ?WPBullhornStaffing $instance = null;
    private bool $restError = false;
    protected ?\jonathanraftery\Bullhorn\Rest\Client $restClient = null;

    private function __construct()
    {
        register_activation_hook(__FILE__, [self::class, 'activate']);
        register_deactivation_hook(__FILE__, [self::class, 'deactivate']);
        $this->initAdmin();
        $this->initFront();
    }

    private function __clone() {}
    private function __wakeup() {}

    public static function instance(): WPBullhornStaffing
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function activate(): void
    {
        // Add DB migrations or plugin requirements as needed
    }

    public static function deactivate(): void
    {
        // Optional: flush plugin-specific caches/transients/options.
    }

    public function isRestError(): bool
    {
        $this->initRestClient();
        return $this->restError;
    }

    public static function pluginPath(string $pathWithoutFirstSlash = ''): string
    {
        return plugin_dir_path(WPBS_PLUGIN_FILE) . $pathWithoutFirstSlash;
    }

    public static function pluginUrl(string $pathWithoutFirstSlash = ''): string
    {
        return plugin_dir_url(WPBS_PLUGIN_FILE) . $pathWithoutFirstSlash;
    }

    /**
     * @param WP_User|int|null $user
     * @return null|\WPBullhornStaffing\Domain\Entities\WPCandidate
     */
    public static function candidate($user = null): ?\WPBullhornStaffing\Domain\Entities\WPCandidate
    {
        try {
            if ($user === null) {
                $user = wp_get_current_user();
            } elseif (is_numeric($user)) {
                $user = get_userdata((int)$user);
            }
            if ($user instanceof WP_User) {
                return (new \WPBullhornStaffing\App\CandidateFinder())->find($user);
            }
        } catch (\Throwable $e) {
            error_log('Candidate Error: ' . $e->getMessage());
        }
        return null;
    }

    protected function initAdmin(): void
    {
        // Settings page could be added here via add_options_page
        add_action('admin_notices', [$this, 'showRestErrorNotice']);
    }

    protected function initFront(): void
    {
        // Setup REST endpoints/shortcodes etc.
    }

    public function showRestErrorNotice()
    {
        if ($this->restError) {
            echo '<div class="notice notice-error"><p>Bullhorn Staffing Plugin REST client failed. Check API credentials and connectivity.</p></div>';
        }
    }

    /**
     * Safely initializes/restores the REST client.
     */
    protected function initRestClient(): void
    {
        if ($this->restClient !== null) {
            return;
        }
        try {
            $clientId = defined('BH_CLIENT_ID') ? BH_CLIENT_ID : get_option('bh_client_id');
            $clientSecret = defined('BH_CLIENT_SECRET') ? BH_CLIENT_SECRET : get_option('bh_client_secret');
            $username = defined('BH_API_USERNAME') ? BH_API_USERNAME : get_option('bh_api_username');
            $password = defined('BH_API_PASSWORD') ? BH_API_PASSWORD : get_option('bh_api_password');
            if (!$clientId || !$clientSecret || !$username || !$password) {
                throw new \Exception('Bullhorn API credentials missing!');
            }
            $this->restClient = new \jonathanraftery\Bullhorn\Rest\Client($clientId, $clientSecret, $username, $password);
        } catch (\Throwable $e) {
            $this->restError = true;
            error_log('REST client: ' . $e->getMessage());
        }
    }

    /**
     * Universal API request wrapper.
     */
    public function request(
        string $method,
        string $url,
        array $options = [],
        array $headers = []
    ) {
        $this->initRestClient();
        if (!$this->restClient) {
            $this->restError = true;
            return new WP_Error(500, 'REST client not initialized');
        }
        try {
            return $this->restClient->request($method, $url, $options, $headers);
        } catch (\Throwable $exception) {
            $this->restError = true;
            return new WP_Error($exception->getCode(), $exception->getMessage());
        }
    }

    public function findInBullhornByEmail(string $userEmail)
    {
        $response = WPBullhornStaffing::instance()->request(
            'GET',
            'search/Candidate',
            [
                'query' => [
                    'query' => 'isDeleted:0 AND email:' . $userEmail,
                    'fields' => 'id,firstName,lastName,email',
                    'count' => 1
                ]
            ]
        );
        if (is_wp_error($response)) {
            return null;
        }
        if (!empty($response->data)) {
            return $response->data[0] ?? null;
        }
        return null;
    }
}

WPBullhornStaffing::instance();