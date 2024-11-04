<?php

namespace JordanPartridge\Packagist\Data;

use Illuminate\Support\Collection;

readonly class PackageDetails
{
    public function __construct(
        private string $minified,
        private array $packages,
        private array $security_advisories,
    ) {}

    public function minified(): string
    {
        return $this->minified;
    }

    public function securityAdvisories(): Collection
    {
        return collect($this->security_advisories);
    }

    public function packages(): Collection
    {
        return collect($this->packages);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['minified'],
            $data['packages'],
            $data['security-advisories'],
        );

    }
}
