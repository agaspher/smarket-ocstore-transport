<?php

declare(strict_types=1);

namespace App\Import\Reader;

interface ReaderInterface
{
    public function read(string $path): iterable;

    public static function matches(string $sourceType): bool;
}
