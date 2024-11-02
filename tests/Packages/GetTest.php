<?php

use JordanPartridge\Packagist\PackagistConnector;
use JordanPartridge\Packagist\Requests\Packages\Get;

it('has proper endpoint', function () {
    $get = new Get('jordanpartridge', 'packagist-client');
    expect($get->resolveEndpoint())->toBe('/jordanpartridge/packagist-client.json');
});

it('can get this package', function () {
    $get = new Get('jordanpartridge', 'packagist-client');
    $connector = new PackagistConnector;
    $response = $connector->send($get);
    expect($response->json())->toBeArray();
});
