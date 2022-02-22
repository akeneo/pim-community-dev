<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model;

interface AffectedByMigrationProjection
{
    public function shouldBeMigrated(): bool;

    public function getFormerDocumentId(): string;

    public function toArray(): array;
}
