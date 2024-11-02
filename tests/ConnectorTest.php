<?php

use JordanPartridge\Packagist\PackagistConnector;

it('has proper base url', function () {
    $connector = new PackagistConnector();
    expect($connector->resolveBaseUrl())->toBe('https://repo.packagist.org/p2/');
});

it('has proper headers', function () {
    $connector = new PackagistConnector();
    expect($connector->headers()->all())->toBe([
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    ]);
});
