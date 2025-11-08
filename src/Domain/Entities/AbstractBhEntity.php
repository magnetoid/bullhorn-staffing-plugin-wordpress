<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

abstract class AbstractBhEntity
{
    protected int $id;

    abstract public function getEntityType(): string;

    protected function updateValuesBeforeSend(array $values): array
    {
        // Filter or massage values as needed before sending
        return $values;
    }

    public static function create(array $data = []): ?self
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

        if (empty($response->changedEntityId)) {
            error_log(print_r($response, true));
            return null;
        }

        $obj->initialize((int)$response->changedEntityId);

        return $obj;
    }

    public static function find($id): ?self
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

    public static function search(array $query = []): ?array
    {
        $obj = new static();
        $fields = $obj->fields ?? [];
        $fieldsMap = $obj->fieldsMap ?? [];

        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'search/' . $obj->getEntityType(),
            [
                'query' => array_merge([
                    'query' => 'isDeleted:0',
                    'fields' => implode(',', array_merge($fields, array_keys($fieldsMap)))
                ], $query)
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        return $response->data ?? null;
    }

    public function updateFields(array $values, bool $force = false): int
    {
        if (empty($this->getId())) {
            throw new \Exception('Entity ID not specified');
        }

        if (isset($values['id'])) {
            unset($values['id']);
        }
        if (empty($values) && !$force) {
            return 0;
        }

        if ($force) {
            $data = get_object_vars($this);
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

    abstract protected function getId(): int;

    protected function initialize($id): void
    {
        $this->id = (int)$id;
        // Extend with fetching remote data if needed
    }

    protected function setData($data): void
    {
        // Map $data onto properties
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    public static function transientName(int $id): string
    {
        return 'wpbstaff_entity_' . static::class . '_' . $id;
    }

    public function delete(): bool
    {
        $response = \WPBullhornStaffing::instance()->request(
            'DELETE',
            'entity/' . $this->getEntityType() . '/' . $this->getId()
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }
        return true;
    }
}