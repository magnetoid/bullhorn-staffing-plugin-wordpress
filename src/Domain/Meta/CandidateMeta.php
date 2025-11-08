<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Meta;

class CandidateMeta extends AbstractMeta
{
    protected array $fieldsMap = [
        'candidateStatus' => 'status',
        'candidateType' => 'type',
        'source' => 'source',
        'owner' => 'owner',
    ];

    public function getMetaType(): string
    {
        return 'Candidate';
    }

    protected function findMeta(string $field)
    {
        $response = \WPBullhornStaffing::instance()->request(
            'GET',
            'options/' . $this->getMetaType(),
            ['query' => ['fields' => $field, 'count' => 1]]
        );

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return null;
        }

        return $response->data[0][$field] ?? null;
    }
}