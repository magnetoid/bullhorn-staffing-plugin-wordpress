<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

class CandidateInfo extends AbstractBhEntity
{
    protected array $fields = [
        'id',
        'firstName',
        'lastName',
        'email',
        'status'
    ];

    protected int $id;
    protected string $firstName = '';
    protected string $lastName = '';
    protected string $email = '';
    protected string $status = '';

    public function getEntityType(): string
    {
        return 'Candidate';
    }

    protected function getId(): int
    {
        return $this->id;
    }
}