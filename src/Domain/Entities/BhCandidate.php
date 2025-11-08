<?php
declare(strict_types=1);

namespace WPBullhornStaffing\Domain\Entities;

class BhCandidate
{
    protected int $bhId;
    protected ?\WP_User $user = null;
    protected array $info = [];
    protected $gateway;

    public function __construct(int $bhId)
    {
        $this->bhId = $bhId;
    }

    public function setUser(\WP_User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getInfo(string $key)
    {
        if(empty($this->info)) {
            $this->info = $this->fetchInfo();
        }
        return $this->info[$key] ?? null;
    }

    private function fetchInfo(): array
    {
        // Example gateway usage. Extend/replace for your API
        if (!$this->gateway) {
            // Initialize gateway or throw error
            return [];
        }
        return $this->gateway->candidate()->find($this->bhId) ?? [];
    }
}