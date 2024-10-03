<?php

declare(strict_types=1);

namespace App\Entity;

use App\Config\Config;
use App\Entity\Repository\OptionValueDescriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionValueDescriptionRepository::class)]
#[ORM\Table(name: "oc_option_value_description")]
class OptionValueDescription
{
    #[ORM\Id]
    #[ORM\Column(name: 'language_id', nullable: false)]
    private int $languageId;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: OptionValue::class, inversedBy: 'descriptions')]
    #[ORM\JoinColumn(name: 'option_value_id', referencedColumnName: 'option_value_id')]
    private OptionValue $optionValue;

    #[ORM\ManyToOne(targetEntity: Option::class, inversedBy: 'optionValueDescriptions')]
    #[ORM\JoinColumn(name: 'option_id', referencedColumnName: 'option_id')]
    private Option $option;

    #[ORM\Column(name: 'name', type: 'string', length: 128, nullable: false)]
    private string $name = '';

    public function __construct()
    {
        $this->languageId = Config::$defaultLanguageId;
    }

    public function getOptionValue(): OptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(OptionValue $optionValue): self
    {
        $this->optionValue = $optionValue;

        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function setOptionId(int $optionId): self
    {
        $this->optionId = $optionId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function setOption(Option $option): self
    {
        $this->option = $option;

        return $this;
    }
}
