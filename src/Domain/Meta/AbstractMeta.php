<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Meta;

abstract class AbstractMeta
{
    protected static ?self $instance = null;
    protected array $fieldsMap = [];

    private function __construct() {}
    final protected function __clone() {}

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

        $data = get_transient($transientName);

        if(!$data) {
            $data = $this->findMeta($field);

            if($data && !empty($data)) {
                set_transient($transientName, $data, DAY_IN_SECONDS);
            }
        }
        return $data;
    }

    abstract protected function findMeta(string $field);
}