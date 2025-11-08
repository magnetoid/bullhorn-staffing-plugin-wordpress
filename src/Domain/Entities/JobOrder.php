<?php


namespace WPBullhornStaffing\Domain\Entities;


class JobOrder extends AbstractBhEntity
{
    /** @var string[] */
    protected $fields = [
        'id',
        'isPublic',
        'isOpen',
    ];

    protected $fieldsMap = [
        'customText12' => 'title'
    ];

    public $id;
    public $title;
    public $isPublic;
    public $isOpen;

    public function getEntityType(): string
    {
        return 'JobOrder';
    }


}