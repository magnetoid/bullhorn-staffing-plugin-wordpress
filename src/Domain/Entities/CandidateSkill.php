<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

class CandidateSkill extends AbstractBhEntity
{
    protected array $fields = ['id', 'name'];
    public int $id;
    public string $name;

    public function getEntityType(): string
    {
        return 'Skill';
    }

    protected function getId(): int
    {
        return $this->id;
    }
}