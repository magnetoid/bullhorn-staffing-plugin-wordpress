<?php


namespace WPBullhornStaffing\Domain\Entities;


class CandidateSkill extends AbstractBhEntity
{
    /** @var string[] */
    protected $fields = [
        'id',
        'name',
    ];

    public $id;
    public $name;

    public function getEntityType(): string
    {
        return 'Skill';
    }


}