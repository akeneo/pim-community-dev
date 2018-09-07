<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface HydratorInterface
{
    public function supports(array $row): bool;

    public function hydrate(array $row);
}
