<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SizeDto
{
    /** @Assert\Length(min=3, minMessage="Size for very short articul") */
    private ?string $articul = '';

    /** @Assert\Length(min=1, minMessage="Size with empty description") */
    private string $rus = '';

    public function getArticul(): ?string
    {
        return $this->articul;
    }

    public function setArticul(?string $articul): self
    {
        $this->articul = $articul;

        return $this;
    }

    public function getRus(): string
    {
        return $this->rus;
    }

    public function setRus(string $rus): self
    {
        $this->rus = $rus;

        return $this;
    }

    public function getEntityId(): int
    {
        return (int)$this->articul;
    }

    public static function fromArray(array $dataSet): self
    {
        return (new self())
            ->setArticul($dataSet['articul'] ?? '')
            ->setRus($dataSet['rus'] ?? '');
    }
}
