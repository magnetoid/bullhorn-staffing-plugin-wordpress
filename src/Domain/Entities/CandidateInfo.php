<?php


namespace WPBullhornStaffing\Domain\Entities;


class CandidateInfo extends AbstractBhEntity
{

    /** @var string[] */
    protected $fields = [
        'id',
        'email',
        'submissions',
        'isDeleted',
    ];

    /** @var array */
    protected $submittedJobs = [];

    public $id;
    public $email;
    public $submissions;
    public $isDeleted;


    public function getEntityType(): string
    {
        return 'Candidate';
    }

    /**
     * @return array|bool|mixed|object|\WP_Error
     */
    public function getLastSubmissions($clearCache = false)
    {
        $transientName = 'wpbstaff_candidate_submissions_' . $this->getId();
        if (!$clearCache) {
            if ($data = get_transient($transientName)) {
                return $data;
            }
        }

        $data = [];
        foreach ([
                     'webResponses',
                     'submissions',
                 ] as $type) {
            $response = \WPBullhornStaffing::instance()->request(
                'GET',
                'entity/' . $this->getEntityType() . '/' . $this->getId() . '/' . $type,
                [
                    'query' => [
                        'start' => 0,
                        'count' => 100,
                        'meta' => 'full',
                        'orderBy' => '-dateAdded',
                        'fields' => implode(',', [
                            'id',
                            'jobOrder',
                            'status',
                            'dateAdded',
                            'isDeleted',
                            'isHidden',
                        ]),
                    ]
                ]
            );

            if (is_wp_error($response)) {
                error_log($response->get_error_message());
                continue;
            }
            if (!$response->data) {
                // error_log('data is empty from response /' . $type . '; bh_id: ' . $this->getId() . '; email: ' . $this->email);
                continue;
            }

            $data += $response->data;
        }

        usort($data, function ($a, $b) {
            return $b->dateAdded - $a->dateAdded;
        });

        if (!empty($data)) {
            set_transient($transientName, $data, HOUR_IN_SECONDS);
        }

        $this->cacheSubmitedJobs($data);

        return $data;
    }

    protected function cacheSubmitedJobs(array $data)
    {
        $jobs = [];
        /** @var JobSubmission $submission */
        foreach ($data as $submission) {
            $jobs[] = $submission->jobOrder->id;
        }

        $this->submittedJobs = array_unique($jobs);
        $this->setData([]);
    }

    protected function initialize($id)
    {
        parent::initialize($id);
        if (empty($this->submittedJobs)) {
            $this->getLastSubmissions(true);
        }
    }

    public function getSubmittedJobs(): array
    {
        if(isset($this->submittedJobs) && !empty($this->submittedJobs)) {
            return $this->submittedJobs;
        }
        return  [];
    }


}