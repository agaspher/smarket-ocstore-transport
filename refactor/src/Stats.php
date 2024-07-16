<?php

declare(strict_types=1);

namespace App;

use App\Config\Config;
use App\Entity\Log;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;

class Stats
{
    private ?DateTimeImmutable $startTime = null;
    private ?DateTimeImmutable $endTime = null;

    private int $usedMemory = 0; // in seconds

    private int $fileSize = 0; // in Kb
    private int $transactionCount = 0;
    private int $rowCountInTransaction = 0;
    private int $rowsCountInFile = 0;
    private int $productsCountUpdated = 0;
    private int $productsCountCreated = 0;

    private array $errors = [];

    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->startTime = new DateTimeImmutable('now');
        $this->endTime = new DateTimeImmutable('now');
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?DateTimeImmutable $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?DateTimeImmutable $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getUsedMemory(): int
    {
        return $this->usedMemory;
    }

    public function setUsedMemory(int $usedMemory): self
    {
        $this->usedMemory = $usedMemory;

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getTransactionCount(): int
    {
        return $this->transactionCount;
    }

    public function setTransactionCount(int $transactionCount): self
    {
        $this->transactionCount = $transactionCount;

        return $this;
    }

    public function getRowCountInTransaction(): int
    {
        return $this->rowCountInTransaction;
    }

    public function setRowCountInTransaction(int $rowCountInTransaction): self
    {
        $this->rowCountInTransaction = $rowCountInTransaction;

        return $this;
    }

    public function getRowsCountInFile(): int
    {
        return $this->rowsCountInFile;
    }

    public function setRowsCountInFile(int $rowsCountInFile): self
    {
        $this->rowsCountInFile = $rowsCountInFile;

        return $this;
    }

    public function getProductsCountUpdated(): int
    {
        return $this->productsCountUpdated;
    }

    public function setProductsCountUpdated(int $productsCountUpdated): self
    {
        $this->productsCountUpdated = $productsCountUpdated;

        return $this;
    }

    public function getProductsCountCreated(): int
    {
        return $this->productsCountCreated;
    }

    public function setProductsCountCreated(int $productsCountCreated): self
    {
        $this->productsCountCreated = $productsCountCreated;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function incrementTransactionCount(): int
    {
        $this->transactionCount++;

        return $this->transactionCount;
    }

    public function incrementRowCountInTransaction(): int
    {
        $this->rowCountInTransaction++;

        return $this->rowCountInTransaction;
    }

    public function increaseRowsCountInFile(int $increase = 0): int
    {
        $this->rowsCountInFile += $increase;

        return $this->rowsCountInFile;
    }

    public function incrementProductsCountUpdated(): int
    {
        $this->productsCountUpdated++;

        return $this->productsCountUpdated;
    }

    public function incrementProductsCountCreated(): int
    {
        $this->productsCountCreated++;

        return $this->productsCountCreated;
    }

    public function getDuration(): int
    {
        return $this->endTime?->getTimestamp() - $this->startTime?->getTimestamp();
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;

        if (count($this->errors) > Config::ERROR_LIST_LENGTH) {
            $this->saveLog();
        }
    }

    private function saveLog(): void
    {
        foreach ($this->getErrors() as $errors) {
            foreach ($errors as $entityId => $message) {
                $newLog = (new Log())
                    ->setEntityId($entityId)
//                    ->setImportType($importType)
                    ->setMsg($message);

                $this->em->persist($newLog);
            }
        }

        $this->em->flush();

        $this->errors = [];
    }
}
