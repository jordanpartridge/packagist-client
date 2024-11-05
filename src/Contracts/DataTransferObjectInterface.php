<?php

namespace JordanPartridge\Packagist\Contracts;

interface DataTransferObjectInterface
{
    public static function fromArray(array $data): self;



}
