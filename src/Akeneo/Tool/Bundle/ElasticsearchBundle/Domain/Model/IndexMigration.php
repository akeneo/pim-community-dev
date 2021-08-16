<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model;

use Webmozart\Assert\Assert;

class IndexMigration
{
    private string $indexAlias;
    private string $indexConfigurationHash;
    private \DateTimeImmutable $startedAt;
    private string $temporaryIndexAlias;
    private string $newIndexName;
    private string $status;

    private function __construct(
        string $indexAlias,
        string $indexConfigurationHash,
        \DateTimeImmutable $startedAt,
        string $temporaryIndexAlias,
        string $newIndexName
    ) {
        Assert::notEq($indexAlias, $temporaryIndexAlias);
        Assert::notEq($indexAlias, $newIndexName);
        Assert::notEq($newIndexName, $temporaryIndexAlias);

        $this->indexAlias = $indexAlias;
        $this->indexConfigurationHash = $indexConfigurationHash;
        $this->startedAt = $startedAt;
        $this->temporaryIndexAlias = $temporaryIndexAlias;
        $this->newIndexName = $newIndexName;
        $this->status = 'started';
    }

    public static function create(
        string $indexAlias,
        string $indexConfigurationHash,
        \DateTimeImmutable $startedAt,
        string $temporaryIndexAlias,
        string $newIndexName
    ): self {
        return new self(
            $indexAlias,
            $indexConfigurationHash,
            $startedAt,
            $temporaryIndexAlias,
            $newIndexName
        );
    }

    public function markAsDone()
    {
        $this->status = 'done';
    }

    public function getIndexAlias(): string
    {
        return $this->indexAlias;
    }

    public function getIndexConfigurationHash(): string
    {
        return $this->indexConfigurationHash;
    }

    public function normalize(): array
    {
        return [
            'started_at' => $this->startedAt->format(\DateTimeInterface::ISO8601),
            'temporary_index_alias' => $this->temporaryIndexAlias,
            'new_index_name' => $this->newIndexName,
            'status' => $this->status,
        ];
    }
}
