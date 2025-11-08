<?php

namespace WPBullhornStaffing\Domain\Meta;

abstract class AbstractMeta
{
    protected static $instance;

    protected $fieldsMap = [];

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

    abstract public function getMetaType(): string;

    public function getMeta(string $field)
    {
        if(isset(array_flip($this->fieldsMap)[$field])) {
            $field = array_flip($this->fieldsMap)[$field];
        }
        $transientName = 'wpbstaff_options_' . $this->getMetaType() . '_' . $field;

        $data = get_transient( $transientName );

        if(!$data) {
            $data = $this->findMeta($field);

            if($data && !empty($data)) {
                set_transient($transientName, $data, DAY_IN_SECONDS);
            }
        }

        return $data;
    }

    protected function findMeta(string $field)
    {
        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'meta/' . $this->getMetaType(),
            [
                'query' => [
                    'fields' => $field,
                ]
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        if(!isset($response->fields) && empty($response->fields)) {
            return null;
        }

        return $response->fields[0];
    }
}