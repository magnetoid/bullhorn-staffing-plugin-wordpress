<?php


namespace WPBullhornStaffing\Domain\Options;

abstract class AbstractOptions
{
    protected static $instance;

    private function __construct()
    {
    }

    final protected function __clone()
    {
    }

    public static function getInstance(): self
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }


    protected function getPaginatedIdList($query = []): array
    {
        $data = [];
        $start = 0;
        do {
            $response = \WPBullhornStaffing::instance()->request(
                'GET',
                'options/' . $this->getOptionsType(),
                [
                    'query' => array_merge($query, [
                        'count' => 300,
                        'start' => $start
                    ])
                ]
            );
            if (is_wp_error($response)) {
                error_log($response->get_error_message());
                return [];
            }

            $list = $response->data;
            $start += 300;

            $data += $list;
        } while (count($list) > 0);
        return $data;
    }

    abstract public function getOptionsType(): string;

    public function getOptions($forceFetch = false, $cacheTime = DAY_IN_SECONDS): array
    {
        $transientName = 'wpbstaff_options_' . $this->getOptionsType();
        $data = null;

        if(!$forceFetch) {
            $data = get_transient($transientName);
        }

        if(!$data) {
            $data = $this->getPaginatedIdList();
            if($data && !empty($data)) {
                set_transient($transientName, $data, $cacheTime);
            }
        }

        return $data;
    }
}