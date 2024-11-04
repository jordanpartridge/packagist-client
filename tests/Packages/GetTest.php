<?php

use JordanPartridge\Packagist\PackagistConnector;
use JordanPartridge\Packagist\Requests\Packages\GetPackageData;

it('has proper endpoint', function () {
    $get = new GetPackageData('jordanpartridge', 'packagist-client');
    expect($get->resolveEndpoint())->toBe('/p2/jordanpartridge/packagist-client.json');
});

it('can get this package', function () {
    $get = new GetPackageData('jordanpartridge', 'packagist-client');
    $connector = new PackagistConnector;
    $response = $connector->send($get);
    expect($response->json())->toBeArray();
});
