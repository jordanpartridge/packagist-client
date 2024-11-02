<?php

namespace JordanPartridge\Packagist;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use JordanPartridge\Packagist\Commands\PackagistCommand;

class PackagistServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('packagist-client')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_packagist_client_table')
            ->hasCommand(PackagistCommand::class);
    }
}
