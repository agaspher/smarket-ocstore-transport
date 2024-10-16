<?php

declare(strict_types=1);

namespace App;

use App\Config\Config;
use App\Entity\Log;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;

class Stats
{
    private ?DateTimeImmutable $startTime;
    private ?DateTimeImmutable $endTime;

    private int $usedMemory = 0; // in seconds

    private int $fileSize = 0; // in Kb
    private int $transactionCount = 0;
    private int $rowCountInTransaction = 0;
    private int $rowsCountInFile = 0;
    private int $countUpdated = 0;
    private int $countCreated = 0;

    private array $errors = [];
    private string $importType = 'Undefined';

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

    public function getCountUpdated(): int
    {
        return $this->countUpdated;
    }

    public function setCountUpdated(int $countUpdated): self
    {
        $this->countUpdated = $countUpdated;

        return $this;
    }

    public function getCountCreated(): int
    {
        return $this->countCreated;
    }

    public function setCountCreated(int $countCreated): self
    {
        $this->countCreated = $countCreated;

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

    public function incrementCountUpdated(): int
    {
        $this->countUpdated++;

        return $this->countUpdated;
    }

    public function incrementCountCreated(): int
    {
        $this->countCreated++;

        return $this->countCreated;
    }

    public function getDuration(): int
    {
        if ($this->startTime && $this->endTime) {
            return $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
        }

        return 0;
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;

        if (count($this->errors) > Config::$errorListLength) {
            $this->saveLog();
        }
    }

    public function getImportType(): string
    {
        return $this->importType;
    }

    public function setImportType(string $importType): self
    {
        $this->importType = $importType;

        return $this;
    }

    public function removeOldLog(): void
    {
        // remove all logs older than month
        $qb = $this->em->createQueryBuilder();
        $qb->delete()
            ->from(Log::class, 'l')
            ->where('l.dateAdded <= :targetDate')
            ->setParameter('targetDate', (new DateTimeImmutable('-1 week'))->format('Y-m-d'));

        $qb->getQuery()->getResult();
    }

    public function saveLog(): void
    {
        foreach ($this->getErrors() as $errors) {
            foreach ($errors as $entityId => $message) {
                $newLog = (new Log())
                    ->setEntityId($entityId)
                    ->setImportType($this->getImportType())
                    ->setMsg(str_replace("\n   ", "", $message));

                $this->em->persist($newLog);
            }
        }

        $this->em->flush();

        $this->errors = [];
    }
}
