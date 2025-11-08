<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Options;

class SkillOptions extends AbstractOptions
{
    protected function getOptionsType(): string
    {
        return 'Skill';
    }

    public function getAllSkills(): array
    {
        return $this->getPaginatedIdList();
    }

    public function getSkillById(int $id): ?array
    {
        $skills = $this->getAllSkills();
        foreach ($skills as $skill) {
            if (isset($skill['id']) && (int)$skill['id'] === $id) {
                return $skill;
            }
        }
        return null;
    }
}