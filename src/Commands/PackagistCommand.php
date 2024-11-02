<?php

namespace JordanPartridge\Packagist\Commands;

use Illuminate\Console\Command;

class PackagistCommand extends Command
{
    public $signature = 'packagist-client';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
