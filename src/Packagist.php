<?php

namespace JordanPartridge\Packagist;

use JordanPartridge\Packagist\Requests\Packages\Get;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class Packagist
{
    public function __construct(
        protected PackagistConnector $connector,
    )
    {
    }

    /**
     * @param string $vendor
     * @param string $name
     * @throws FatalRequestException
     * @throws RequestException
     * @return Response
     */
    public function getPackage(string $vendor, string $name): Response
    {
       return $this->connector->send(new Get($vendor, $name));
    }
}
