<?php
/**
 * Plugin Name: WP Bullhorn Staffing
 * Plugin URI:
 * Description: Bullhorn Staffing synchronisation plugin
 * Version: 0.0.8
 * Author: Think studio
 * Author URI: https://think.studio/
 * Developer: Yaroslav Georgitsa <yaroslav.georgitsa@gmail.com>
 */

require __DIR__ . '/vendor/autoload.php';

if (!defined('WPBS_PLUGIN_FILE')) {
    define('WPBS_PLUGIN_FILE', __FILE__);
}


class WPBullhornStaffing
{
    private static $instance = null;

    private $restError = false;

    /** @var \jonathanraftery\Bullhorn\Rest\Client */
    protected $restClient;

    private function __construct()
    {
        register_activation_hook(__FILE__, [&$this, 'activate']);
        register_deactivation_hook(__FILE__, [&$this, 'deactivate']);
        $this->initAdmin();
        $this->initFront();
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public function activate()
    {
    }

    public function deactivate()
    {
    }

    public static function instance(): WPBullhornStaffing
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function isRestError()
    {
        $this->initRestClient();
        return $this->restError;
    }

    public static function pluginPath(string $pathWithoutFirstSlash = '')
    {
        return plugin_dir_path(WPBS_PLUGIN_FILE) . $pathWithoutFirstSlash;
    }

    public static function pluginUrl(string $pathWithoutFirstSlash = '')
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
                $user = get_userdata($user);
            }

            if (!($user instanceof WP_User) || !$user->user_email) {
                return null;
            }

            return (new \WPBullhornStaffing\App\CandidateFinder())->find($user);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function initRestClient()
    {
        if (!defined('BH_CLIENT_ID') || !defined('BH_CLIENT_SECRET')
            || !defined('BH_API_USERNAME') || !defined('BH_API_PASSWORD')
        ) {
            return false;
        }
        if ($this->restClient) {
            return $this->restClient;
        }
        try {
            $client = new \jonathanraftery\Bullhorn\Rest\Client(
                BH_CLIENT_ID,
                BH_CLIENT_SECRET,
                new \jonathanraftery\Bullhorn\WordpressDataStore()
            );
            $client->refreshOrInitiateSession(
                BH_API_USERNAME,
                BH_API_PASSWORD,
                ['ttl' => 240]
            );

            $this->restClient = $client;
        } catch (Exception $e) {
            $this->restError = true;
            error_log('rest client' . $e->getMessage());
        }
    }

    protected function initAdmin()
    {
    }

    protected function initFront()
    {
    }

    public function request(
        $method,
        $url,
        $options = [],
        $headers = []
    )
    {
        $this->initRestClient();

        if (!$this->restClient) {
            return new WP_Error(500, 'REST not Working');
        }
        try {
            return $this->restClient->request($method, $url, $options, $headers);
        } catch (Exception $exception) {
            return new WP_Error($exception->getCode(), $exception->getMessage());
        }
    }


    public function findInBullhornByEmail($userEmail)
    {
        $response = WPBullhornStaffing::instance()->request('GET', 'search/Candidate', ['query' => ['query' => 'isDeleted:0 AND email:' . $userEmail, 'fields' => 'id,firstName,lastName,email', 'count' => 1]]);
        if (is_wp_error($response)) {
            return null;
        }
        if ($response->count && $response->count > 0) {
            return $response->data[0];
        }
        return null;
    }

}

WPBullhornStaffing::instance();


