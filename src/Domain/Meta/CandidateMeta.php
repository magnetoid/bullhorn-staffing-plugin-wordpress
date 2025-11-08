<?php


namespace WPBullhornStaffing\Domain\Meta;


class CandidateMeta extends AbstractMeta
{

    protected static $instance;

    public function getMetaType(): string
    {
        return 'Candidate';
    }
}