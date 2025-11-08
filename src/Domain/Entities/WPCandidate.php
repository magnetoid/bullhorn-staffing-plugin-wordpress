<?php

namespace WPBullhornStaffing\Domain\Entities;


use WPBullhornStaffing\Domain\Contracts\CandidateInfo;

class WPCandidate
{
    /** @var int */
    protected $bhId;

    /** @var \WP_User */
    protected $user;

    protected $candidateInfoClass;
    protected $info;

    protected $candidateFilesClass;
    protected $files;

    /**
     * BhCandidate constructor.
     * @param int $bhId
     */
    public function __construct(\WP_User $user, $bhId, array $subInfoclasses)
    {
        $this->user = $user;
        $this->bhId = (int)$bhId;
        $this->candidateInfoClass = $subInfoclasses['candidateInfoClass'] ?? \WPBullhornStaffing\Domain\Entities\CandidateInfo::class;
        $this->candidateFilesClass = $subInfoclasses['candidateFilesClass'] ?? \WPBullhornStaffing\Domain\Entities\CandidateFiles::class;
    }

    /**
     * @return int
     */
    public function getBhId(): int
    {
        return $this->bhId;
    }

    /**
     * @return \WP_User
     */
    public function getUser(): \WP_User
    {
        return $this->user;
    }


    public function getInfo($forceFetch = false): AbstractBhEntity
    {

        if (!$this->info || $forceFetch) {
            $this->info = call_user_func($this->candidateInfoClass . '::find', $this->getBhId());
        }
        return $this->info;
    }

    public function getFiles($forceFetch = false): CandidateFiles
    {
        if (!$this->files || $forceFetch) {
            $this->files = call_user_func($this->candidateFilesClass . '::find', $this->getBhId());
        }
        return $this->files;
    }


}