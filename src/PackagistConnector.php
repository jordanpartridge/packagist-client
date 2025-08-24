<?php

namespace JordanPartridge\Packagist;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PackagistConnector extends Connector
{
    use AlwaysThrowOnErrors;

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function resolveBaseUrl(): string
    {
        return 'https://repo.packagist.org/';
    }
}
