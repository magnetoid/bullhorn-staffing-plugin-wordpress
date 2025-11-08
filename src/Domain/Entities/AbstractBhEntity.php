<?php


namespace WPBullhornStaffing\Domain\Entities;


use WPBullhornStaffing\Domain\Contracts\CanFetch;

abstract class AbstractBhEntity implements CanFetch
{

    /** @var string[] */
    protected $fields = [];

    /** @var array */
    protected $fieldsMap = [];

    public function __construct()
    {
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    public static function transientName($id)
    {
        return 'wpbstaff_entity_' . substr(strrchr(static::class, "\\"), 1) . '_' . $id;
    }

    /**
     * @see https://bullhorn.github.io/rest-api-docs/#get-search
     * @param array $query
     * @return |null
     */
    public static function search(array $query = [])
    {
        $obj = new static();
        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'search/' . $obj->getEntityType(),
            [
                'query' => array_merge([
                    'query' => 'isDeleted:0',
                    'fields' => implode(',', array_merge(
                        $obj->fields,
                        array_keys($obj->fieldsMap)
                    ))
                ], $query)
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        return $response->data;
    }

    public static function find($id)
    {
        $transientName = static::transientName($id);

        $data = get_transient($transientName);
        if ($data && $data instanceof static) {
            return $data;
        }

        $obj = new static();
        $obj->initialize($id);

        return $obj;
    }

    public function updateFields($values, $force = false): int
    {
        if (!$this->getId()) {
            throw new \Exception('Entity ID not specified');
        }

        // $allowedKeys = array_merge($this->fields, array_values($this->fieldsMap));
        // $values = array_intersect_key($values, array_flip($allowedKeys));
        if (isset($values['id'])) {
            unset($values['id']);
        }
        if (empty($values) && !$force) {
            return 0;
        }

        if ($force) {
            $data = call_user_func('get_object_vars', $this);
            $values = array_merge($data, $values);
        }

        $values = $this->updateValuesBeforeSend($values);

        $response = \WPBullhornStaffing::instance()->request(
            'POST',
            'entity/' . $this->getEntityType() . '/' . $this->getId(),
            [
                'json' => $values
            ],
            [
                'Content-Type' => 'application/json'
            ]
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return 0;
        }

        $this->setData($values);

        return count($values);
    }

    public static function create($data = [])
    {
        $obj = new static();

        $data = $obj->updateValuesBeforeSend($data);

        $response = \WPBullhornStaffing::instance()->request(
            'PUT',
            'entity/' . $obj->getEntityType(),
            [
                'json' => $data
            ],
            [
                'Content-Type' => 'application/json'
            ]
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        if (!$response->changedEntityId) {
            error_log($response);
            return null;
        }

        $obj->initialize($response->changedEntityId);

        return $obj;
    }

    public function delete()
    {
        $response = \WPBullhornStaffing::instance()->request(
            'DELETE',
            'entity/' . $this->getEntityType() . '/' . $this->getId()
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }

        delete_transient(static::transientName($this->getId()));

        return true;
    }

    public function attach(string $entityType, int $id): bool
    {
        $response = \WPBullhornStaffing::instance()->request(
            'PUT',
            'entity/' . $this->getEntityType() . '/' . $this->getId() . '/' . $entityType . '/' . $id
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }
        $this->initialize($this->getId());

        return true;
    }

    public function detach(string $entityType, int $id): bool
    {
        $response = \WPBullhornStaffing::instance()->request(
            'DELETE',
            'entity/' . $this->getEntityType() . '/' . $this->getId() . '/' . $entityType . '/' . $id
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }
        $this->initialize($this->getId());

        return true;
    }

    public static function fromObject($data)
    {
        $obj = new static();
        $obj->setData($data);

        return $obj;
    }

    protected function fetchInfo($id)
    {

        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'entity/' . $this->getEntityType() . '/' . $id,
            [
                'query' => [
                    'fields' => implode(',', array_merge(
                        $this->fields,
                        array_keys($this->fieldsMap)
                    ))
                ]
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        return $response->data;
    }


    protected function initialize($id)
    {
        $data = $this->fetchInfo($id);
        if ($data) {
            $this->setData($data);
        }
    }

    public function refresh()
    {
        $this->initialize($this->getId());
    }

    protected function setData($data)
    {
        foreach ($data as $key => $value) {
            if (isset($this->fieldsMap[$key]) && property_exists($this, $this->fieldsMap[$key])) {
                $this->{$this->fieldsMap[$key]} = $value;
            } elseif (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        set_transient(static::transientName($this->getId()), $this, HOUR_IN_SECONDS);
        return $this;
    }

    abstract public function getEntityType(): string;

    protected function updateValuesBeforeSend(array $values)
    {
        return $values;
    }

}