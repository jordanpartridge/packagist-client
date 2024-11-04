<?php

namespace JordanPartridge\Packagist\Requests\Packages;

use JordanPartridge\Packagist\Data\PackageDetails;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetPackageData extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $vendor,
        protected string $package
    ) {
        $this->validateRepoName($package);
        $this->validateVendorName($vendor);
    }

    public function createDtoFromResponse(Response $response): PackageDetails
    {
        return PackageDetails::fromArray($response->json());
    }

    /**
     * {@inheritDoc}
     */
    public function resolveEndpoint(): string
    {
        return "/p2/{$this->vendor}/$this->package.json";
    }

    private function validateVendorName(string $vendor): void
    {
        if (! preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*/i', $vendor)) {
            throw new \InvalidArgumentException('Invalid vendor name');
        }
    }

    private function validateRepoName(string $package): void
    {
        if (! preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*/i', $package)) {
            throw new \InvalidArgumentException('Invalid package name');
        }
    }
}
