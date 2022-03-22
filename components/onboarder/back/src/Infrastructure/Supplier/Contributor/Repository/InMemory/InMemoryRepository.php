<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Contributor\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;

final class InMemoryRepository implements Contributor\Repository
{
    private array $contributors = [];

    public function save(Contributor\Model\Contributor $contributor): void
    {
        $this->contributors[$contributor->identifier()] = $contributor;
    }

    public function find(Contributor\ValueObject\Identifier $identifier): ?Contributor\Model\Contributor
    {
        return array_key_exists((string) $identifier, $this->contributors)
            ? $this->contributors[(string) $identifier]
            : null
        ;
    }
}
