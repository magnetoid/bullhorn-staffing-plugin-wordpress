<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

use WPBullhornStaffing\Domain\Contracts\CandidateInfo;

class WPCandidate
{
    protected int $bhId;
    protected \WP_User $user;
    protected string $candidateInfoClass;
    protected ?AbstractBhEntity $info = null;
    protected string $candidateFilesClass;
    protected ?CandidateFiles $files = null;

    public function __construct(\WP_User $user, int $bhId, array $subInfoclasses)
    {
        $this->user = $user;
        $this->bhId = $bhId;
        $this->candidateInfoClass = $subInfoclasses['candidateInfoClass'] ?? CandidateInfo::class;
        $this->candidateFilesClass = $subInfoclasses['candidateFilesClass'] ?? CandidateFiles::class;
    }

    public function getBhId(): int
    {
        return $this->bhId;
    }

    public function getUser(): \WP_User
    {
        return $this->user;
    }

    public function getInfo(bool $forceFetch = false): AbstractBhEntity
    {
        if (!$this->info || $forceFetch) {
            $this->info = call_user_func([$this->candidateInfoClass, 'find'], $this->getBhId());
        }
        return $this->info;
    }

    public function getFiles(bool $forceFetch = false): CandidateFiles
    {
        if (!$this->files || $forceFetch) {
            $this->files = call_user_func([$this->candidateFilesClass, 'find'], $this->getBhId());
        }
        return $this->files;
    }
}