<?php

declare(strict_types=1);

namespace App\Import\Reader;

use App\Exception\FileNotFoundException;
use Cerbero\JsonParser\JsonParser;

class JsonReader implements ReaderInterface
{
    private const SUPPORTED_TYPES = ['json'];
    private ?string $source = '';

    public function __construct(?string $source)
    {
        $this->source = $source;
    }

    public function read(string $path = null, int $chunkSize = 2000): iterable
    {
        $source = $path ?? $this->source;

        if (!file_exists($source)) {
            throw  new FileNotFoundException(sprintf('File [%s] not found.', $source));
        }

        $parser = new JsonParser($source);

        $i = 0;
        $chunk = [];
        foreach ($parser->getIterator() as $key => $value) {
            $chunk[$key] = $value;
            $i++;

            if ($i >= $chunkSize) {
                yield $chunk;

                $chunk = [];
                $i = 0;
            }
        }

        yield $chunk;
    }

    public static function matches(string $sourceType): bool
    {
        return in_array($sourceType, self::SUPPORTED_TYPES);
    }
}
