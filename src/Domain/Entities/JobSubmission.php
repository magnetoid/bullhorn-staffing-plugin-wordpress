<?php


namespace WPBullhornStaffing\Domain\Entities;


class JobSubmission extends AbstractBhEntity
{
    /** @var string[] */
    protected $fields = [
        'id',
        'candidate',
        'jobOrder',
        'status',
        'dateAdded',
        'isDeleted',
        'isHidden',
    ];

    public $id;
    public $jobOrder;
    public $status;
    public $dateAdded;
    public $isDeleted;
    public $isHidden;

    public function getEntityType(): string
    {
        return 'JobSubmission';
    }

    public function getDateTimeAdded()
    {
        $dateTime = false;
        if ($this->dateAdded) {
            $dateTime = \DateTime::createFromFormat('U', round($this->dateAdded / 1000));
        }
        return ($dateTime instanceof \DateTime) ? $dateTime : new \DateTime();
    }

    /**
     * @param array $data
     * @return JobSubmission|null
     * @example
     * $data = [
     *      'candidate' => ['id' => 'SOME_ID'],
     *      'jobOrder' => ['id' => 'SOME_ID'],
     * ];
     */
    public static function create($data = [])
    {
        if (!isset($data['status'])) {
            // to be listed as "Online Response" status should be 'New Lead' and dateWebResponse now
            $data['status'] = 'New Lead';
        }
        if (!isset($data['dateWebResponse'])) {
            $data['dateWebResponse'] = time() * 1000;
        }
        if (!isset($data['source'])) {
            $data['source'] = apply_filters('wpbhs_default_job_submission_source', 'WP Website');
        }
        return parent::create($data);
    }


}