<?php

declare(strict_types=1);

namespace App\Import\Reader;

use Cerbero\JsonParser\JsonParser;
use JsonSchema\Exception\ResourceNotFoundException;

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
        if (!$path && !$this->source) {
            throw  new ResourceNotFoundException('You have to point a json resource.');
        }

        $parser = new JsonParser($path ?? $this->source);

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
