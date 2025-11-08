<?php

namespace WPBullhornStaffing\Domain\Entities;


class BhCandidate
{
    /** @var int */
    protected $bhId;

    /** @var \WP_User */
    protected $user;

    protected $info;

    /**
     * BhCandidate constructor.
     * @param int $bhId
     */
    public function __construct($bhId)
    {
        $this->bhId = (int)$bhId;
    }

    public function setUser(\WP_User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getInfo($key)
    {
        if(!$this->info) {
            $this->fetchInfo();
        }
    }

    private function fetchInfo()
    {
        $this->gateway->candidate()->find($this->bhId);
    }


}