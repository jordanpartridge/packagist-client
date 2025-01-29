<?php

namespace JordanPartridge\Packagist;

use JordanPartridge\Packagist\Requests\Packages\GetPackageData;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class Packagist
{
    public function __construct(
        protected PackagistConnector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function getPackage(string $vendor, string $name): Response
    {
        return $this->connector->send(new GetPackageData($vendor, $name));
    }
}
