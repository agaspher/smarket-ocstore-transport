<?php

declare(strict_types=1);

namespace Globus\LoadManager\Loader;

include(ABS_PATH . '/config.php');

use Globus\GlobusConfig as Config;

class JsonLoader
{
    public function getProducts(): array
    {
        $content = file_get_contents(Config::IMP_JSON_DIR . '/cards.json');

        if ($content === False) {
            throw new \Exception("File " . Config::IMP_JSON_DIR . '/cards.json' . " not found.");
        }

        return json_decode($content, true);
    }

    public function getCategories(): array
    {
        $content = file_get_contents(Config::IMP_JSON_DIR . '/classif.json');

        if ($content === False) {
            throw new \Exception("File " . Config::IMP_JSON_DIR . '/classif.json' . " not found.");
        }

        return json_decode($content, true);
    }
}