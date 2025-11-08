<?php


namespace WPBullhornStaffing\Domain\Entities;


use Tightenco\Collect\Support\Collection;
use WPBullhornStaffing\Domain\Contracts\CanFetch;

class CandidateFiles implements CanFetch
{

    /** @var array */
    public $files = [];
    protected $candidateId;


    public function __construct(int $id)
    {
        $this->candidateId = $id;
    }

    public static function transientName($id)
    {
        return 'wpbstaff_files_' . substr(strrchr(static::class, "\\"), 1) . '_' . $id;
    }

    public static function find($id)
    {
        $transientName = static::transientName($id);

        $data = get_transient($transientName);
        if ($data) {
            return $data;
        }

        $obj = new static($id);
        $obj->initialize();

        return $obj;
    }

    public function refresh() {
        $this->initialize();
    }

    public function clearCache() {
        delete_transient(static::transientName($this->candidateId));
    }

    public static function fromObject($data)
    {
        if (!$data['candidateId']) {
            throw new \Exception('$data[\'candidateId\'] not present');
        }
        $obj = new static($data['candidateId']);
        $obj->setData($data);

        return $obj;
    }

    protected function fetchInfo()
    {

        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'entityFiles/Candidate/' . $this->candidateId, []
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return [];
        }

        if (is_array($response->EntityFiles)) {
            return $response->EntityFiles;
        }
        return [];
    }

    public function removeFile($fileId)
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

    public function uploadFile($externalID, $fileContent, $name, $additional)
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
        return $response->fileId;
    }

    public function resumeParseToCandidate($filePath, $name)
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

    protected function initialize()
    {
        $data = $this->fetchInfo();
        if ($data) {
            $this->setData($data);
        }
    }

    protected function setData($data)
    {
        $this->files = $data;
        set_transient(static::transientName($this->candidateId), $this, HOUR_IN_SECONDS);
        return $this;
    }

    public function getCVFiles()
    {
        $collection = Collection::make($this->files);
        $filtered = $collection->where('type', 'CV')
            ->sortByDesc('dateAdded');

        return $filtered->toArray();
    }

    public function getLastCV()
    {
        $collection = Collection::make($this->files);
        $filtered = $collection->where('type', 'CV')
            ->sortByDesc('dateAdded');

        return $filtered->first();
    }

    public static function cvAllowedMimeTypes()
    {
        return [
            'application/pdf', // PDF
            'application/x-pdf', // PDF
            'application/msword', // DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
            'application/vnd.oasis.opendocument.text', // ODT
            'application/rtf', // RTF
        ];
    }

}