<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

use WPBullhornStaffing\Domain\Collections\Collection;

class CandidateFiles
{
    protected int $candidateId;
    protected array $files = [];

    public function __construct(int $candidateId)
    {
        $this->candidateId = $candidateId;
    }

    public static function fromObject(array $data): self
    {
        if (empty($data['candidateId'])) {
            throw new \Exception("candidateId key is required");
        }
        $obj = new static($data['candidateId']);
        $obj->setData($data['files'] ?? []);
        return $obj;
    }

    protected function fetchInfo(): array
    {
        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'entityFiles/Candidate/' . $this->candidateId,
            []
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return [];
        }

        return $response->EntityFiles ?? [];
    }

    public function removeFile(int $fileId): bool
    {
        $response = \WPBullhornStaffing::instance()->request(
            'DELETE',
            'file/Candidate/' . $this->candidateId . '/' . $fileId
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }
        $this->clearCache();
        return true;
    }

    public function uploadFile(string $externalID, string $fileContent, string $name, array $additional): mixed
    {
        $response = \WPBullhornStaffing::instance()->request(
            'PUT',
            'file/Candidate/' . $this->candidateId,
            [
                'json' => array_merge($additional, [
                    'externalID' => $externalID,
                    'fileContent' => $fileContent,
                    'fileType' => 'SAMPLE',
                    'name' => $name,
                ])
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }
        $this->clearCache();
        return $response->fileId ?? null;
    }

    public function resumeParseToCandidate(string $filePath, string $name): mixed
    {
        $response = \WPBullhornStaffing::instance()->request(
            'POST',
            'resume/parseToCandidate',
            [
                'query' => [
                    'format' => 'text',
                    'populateDescription' => 'html',
                ],
                'multipart' => [
                    [
                        'name'     => 'FileContents',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $name
                    ],
                ],
            ]
        );
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }
        return $response;
    }

    protected function initialize(): void
    {
        $data = $this->fetchInfo();
        if ($data) {
            $this->setData($data);
        }
    }

    public function setData(array $data): self
    {
        $this->files = $data;
        set_transient(static::transientName($this->candidateId), $this, HOUR_IN_SECONDS);
        return $this;
    }

    public function clearCache(): void
    {
        delete_transient(static::transientName($this->candidateId));
    }

    public static function transientName(int $candidateId): string
    {
        return 'wpbstaff_candidate_files_' . $candidateId;
    }

    public function getCVFiles(): array
    {
        $collection = Collection::make($this->files);
        $filtered = $collection->where('type', 'CV')->sortByDesc('dateAdded');
        return $filtered->toArray();
    }

    public function getLastCV(): mixed
    {
        $cvs = $this->getCVFiles();
        return !empty($cvs) ? $cvs[0] : null;
    }
}