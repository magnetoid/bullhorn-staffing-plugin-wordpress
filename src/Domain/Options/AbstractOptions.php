<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Options;

abstract class AbstractOptions
{
    protected static ?self $instance = null;

    private function __construct() {}
    final protected function __clone() {}

    public static function getInstance(): self
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    abstract protected function getOptionsType(): string;

    protected function getPaginatedIdList(array $query = []): array
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

            $list = $response->data ?? [];
            $start += 300;
            $data = array_merge($data, $list);
        } while(count($list) === 300);

        return $data;
    }
}