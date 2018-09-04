<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

interface HydratorInterface
{
    public function supports(array $result): bool;

    public function hydrate(array $result);
}
