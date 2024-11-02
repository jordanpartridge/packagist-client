<?php

namespace JordanPartridge\Packagist\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JordanPartridge\Packagist\Packagist
 */
class Packagist extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JordanPartridge\Packagist\Packagist::class;
    }
}
