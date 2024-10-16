<?php

declare(strict_types=1);

namespace App\Entity;

use App\Config\Config;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_option_description")
 */
class OptionDescription
{
    /**
     * @ORM\Id
     * @ORM\Column(name="option_id")
     */
    private int $optionId;

    /**
     * @ORM\Id
     * @ORM\Column(name="language_id", nullable=false)
     */
    private int $languageId;

    /**
     * @ORM\Column(name="name", length=128, nullable=false)
     */
    private string $name = '';

    public function __construct()
    {
        $this->optionId = Config::$defaultOptionId;
        $this->languageId = Config::$defaultLanguageId;
    }
}
