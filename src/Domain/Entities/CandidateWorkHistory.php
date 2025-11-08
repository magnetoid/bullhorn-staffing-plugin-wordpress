<?php


namespace WPBullhornStaffing\Domain\Entities;


class CandidateWorkHistory extends AbstractBhEntity
{
    /** @var string[] */
    protected $fields = [
        'id',
        'candidate',
        'terminationReason',
        'companyName',
        'title',
        'startDate',
        'endDate',
    ];

    public $id;
    public $candidate;
    public $terminationReason;
    public $companyName;
    public $title;
    public $startDate;
    public $endDate;

    public function getEntityType(): string
    {
        return 'CandidateWorkHistory';
    }

    public function getStartDateTime() {
        if($this->startDate) {
            return \DateTime::createFromFormat('U', round($this->startDate/1000));
        }
        return new \DateTime();
    }

    public function getEndDateTime() {
        if($this->endDate) {
            return \DateTime::createFromFormat('U', round($this->endDate/1000));
        }
        return new \DateTime();
    }
}