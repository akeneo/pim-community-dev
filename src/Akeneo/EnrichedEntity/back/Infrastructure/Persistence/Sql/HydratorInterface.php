<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface HydratorInterface
{
    public function supports(array $result): bool;

    public function hydrate(AbstractPlatform $platform, array $result);
}
